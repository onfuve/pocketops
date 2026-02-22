<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\Attachment;
use App\Models\PaymentOption;
use App\Models\Product;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Tag;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->canModule('invoices', \App\Models\User::ABILITY_VIEW), 403, 'شما به این بخش دسترسی ندارید.');
        $invoices = Invoice::query()
            ->visibleToUser($request->user())
            ->with(['contact', 'payments', 'user', 'assignedTo'])
            ->ofType($request->get('type'))
            ->when($request->filled('contact_id'), fn ($q) => $q->where('contact_id', $request->contact_id))
            ->latest('date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $contact = $request->filled('contact_id') ? Contact::find($request->contact_id) : null;

        return view('invoices.index', compact('invoices', 'contact'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->canModule('invoices', \App\Models\User::ABILITY_CREATE), 403, 'شما مجوز ایجاد فاکتور را ندارید.');
        $contact = null;
        $type = $request->get('type', Invoice::TYPE_SELL);
        if ($request->filled('contact_id')) {
            $contact = Contact::visibleToUser($request->user())->find($request->contact_id);
        }
        $invoice = new Invoice([
            'type' => in_array($type, [Invoice::TYPE_SELL, Invoice::TYPE_BUY], true) ? $type : Invoice::TYPE_SELL,
            'date' => now(),
            'status' => Invoice::STATUS_DRAFT,
        ]);
        $invoice->setRelation('items', collect([(object)['description' => '', 'quantity' => 1, 'unit_price' => 0, 'amount' => 0, 'sort' => 0]]));
        $paymentOptions = PaymentOption::printableInSettings()->orderBy('sort')->get();
        $selectedIds = [];
        $tags = Tag::forCurrentUser()->orderBy('name')->get();

        return view('invoices.create', ['invoice' => $invoice, 'contact' => $contact, 'paymentOptions' => $paymentOptions, 'selectedIds' => $selectedIds, 'tags' => $tags]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canModule('invoices', \App\Models\User::ABILITY_CREATE), 403, 'شما مجوز ایجاد فاکتور را ندارید.');
        $dateErrors = $this->normalizeShamsiDates($request);
        if (!empty($dateErrors)) {
            return back()->withErrors($dateErrors)->withInput();
        }
        $validated = $request->validate($this->rules());
        $validated['status'] = $request->get('status', Invoice::STATUS_DRAFT);
        $validated['discount'] = $this->numericInput($request->get('discount'), 0);
        $dp = $request->get('discount_percent');
        $validated['discount_percent'] = ($dp !== null && $dp !== '') ? min(100, max(0, (float) preg_replace('/[^\d.]/', '', FormatHelper::persianToEnglish((string) $dp)))) : null;
        if (empty(trim($validated['invoice_number'] ?? ''))) {
            $validated['invoice_number'] = FormatHelper::shamsiNumber();
        }
        $ids = $request->validate(['payment_option_ids' => 'nullable|array', 'payment_option_ids.*' => 'integer|exists:payment_options,id'])['payment_option_ids'] ?? [];
        $ids = array_values(array_map('intval', $ids));
        $validated['payment_option_ids'] = $ids;
        $fields = [];
        foreach ($ids as $id) {
            $fields[(string) $id] = [
                'print_card_number' => $request->boolean("payment_option_fields.{$id}.print_card_number"),
                'print_iban' => $request->boolean("payment_option_fields.{$id}.print_iban"),
                'print_account_number' => $request->boolean("payment_option_fields.{$id}.print_account_number"),
            ];
        }
        $validated['payment_option_fields'] = $fields;
        $items = $this->parseItemsFromRequest($request);

        abort_unless(
            Contact::visibleToUser($request->user())->where('id', $validated['contact_id'])->exists(),
            403,
            'شما به این مخاطب دسترسی ندارید.'
        );

        $validated['user_id'] = $request->user()->id;
        $invoice = Invoice::create($validated);
        $this->syncItems($invoice, $items);
        $this->syncTags($invoice, $request->input('tag_ids', []));
        $invoice->recalculateTotals();

        return redirect()->route('invoices.show', $invoice)->with('success', 'فاکتور ذخیره شد.');
    }

    public function show(Invoice $invoice)
    {
        abort_unless(request()->user()->canModule('invoices', \App\Models\User::ABILITY_VIEW), 403, 'شما به این بخش دسترسی ندارید.');
        abort_unless($invoice->isVisibleTo(request()->user()), 403, 'شما به این فاکتور دسترسی ندارید.');

        $invoice->load('contact', 'items', 'user', 'assignedTo', 'tasks.assignedUsers');
        try {
            $invoice->load('tags', 'attachments', 'payments.bankAccount', 'payments.contact');
        } catch (\Illuminate\Database\QueryException $e) {
            // Some tables (tags, attachments, invoice_payments) may not exist in older DBs
            $invoice->setRelation('payments', collect());
            $invoice->setRelation('tags', collect());
            $invoice->setRelation('attachments', collect());
        }
        $paymentOptions = PaymentOption::printableInSettings()->orderBy('sort')->get();
        $selectedIds = $invoice->payment_option_ids ?? [];
        $paymentOptionFields = $invoice->payment_option_fields ?? [];

        return view('invoices.show', compact('invoice', 'paymentOptions', 'selectedIds', 'paymentOptionFields'));
    }

    public function updatePaymentOptions(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->isVisibleTo($request->user()), 403, 'شما به این فاکتور دسترسی ندارید.');

        $ids = $request->validate(['payment_option_ids' => 'nullable|array', 'payment_option_ids.*' => 'integer|exists:payment_options,id'])['payment_option_ids'] ?? [];
        $ids = array_values(array_map('intval', $ids));
        $fields = [];
        foreach ($ids as $id) {
            $fields[(string) $id] = [
                'print_card_number' => $request->boolean("payment_option_fields.{$id}.print_card_number"),
                'print_iban' => $request->boolean("payment_option_fields.{$id}.print_iban"),
                'print_account_number' => $request->boolean("payment_option_fields.{$id}.print_account_number"),
            ];
        }
        $invoice->update(['payment_option_ids' => $ids, 'payment_option_fields' => $fields]);

        return back()->with('success', 'حساب‌ها و کارت‌ها به‌روز شد.');
    }

    public function edit(Invoice $invoice)
    {
        abort_unless(request()->user()->canModule('invoices', \App\Models\User::ABILITY_EDIT), 403, 'شما مجوز ویرایش فاکتور را ندارید.');
        abort_unless($invoice->isVisibleTo(request()->user()), 403, 'شما به این فاکتور دسترسی ندارید.');

        if ($invoice->status === Invoice::STATUS_DRAFT) {
            // Draft: always editable
        } elseif ($invoice->status === Invoice::STATUS_FINAL && $invoice->canEditOrDelete()) {
            // Finalized with no payments: editable
            // TODO: Later restrict to high-privilege users: Gate::authorize('edit-finalized-invoice', $invoice);
        } else {
            return redirect()->route('invoices.show', $invoice)->with('error', 'این فاکتور قابل ویرایش نیست. فقط فاکتور پیش‌نویس یا فاکتور نهایی‌شده بدون هیچ پرداختی قابل ویرایش است.');
        }
        $invoice->load('items', 'tags');
        $contact = $invoice->contact;
        $paymentOptions = PaymentOption::printableInSettings()->orderBy('sort')->get();
        $selectedIds = $invoice->payment_option_ids ?? [];
        $paymentOptionFields = $invoice->payment_option_fields ?? [];
        $tags = Tag::forCurrentUser()->orderBy('name')->get();
        $users = User::where('id', '!=', auth()->id())->orderBy('name')->get();

        return view('invoices.edit', compact('invoice', 'contact', 'paymentOptions', 'selectedIds', 'paymentOptionFields', 'tags', 'users'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        abort_unless($request->user()->canModule('invoices', \App\Models\User::ABILITY_EDIT), 403, 'شما مجوز ویرایش فاکتور را ندارید.');
        abort_unless($invoice->isVisibleTo($request->user()), 403, 'شما به این فاکتور دسترسی ندارید.');

        if ($invoice->status === Invoice::STATUS_DRAFT) {
            // Draft: always editable
        } elseif ($invoice->status === Invoice::STATUS_FINAL && $invoice->canEditOrDelete()) {
            // Finalized with no payments: reverse balance before update, re-apply after
            $invoice->reverseContactBalanceForInvoice();
        } else {
            return redirect()->route('invoices.show', $invoice)->with('error', 'این فاکتور قابل ویرایش نیست.');
        }
        $dateErrors = $this->normalizeShamsiDates($request);
        if (!empty($dateErrors)) {
            return back()->withErrors($dateErrors)->withInput();
        }
        $validated = $request->validate($this->rules());
        $validated['status'] = $request->get('status', Invoice::STATUS_DRAFT);
        $validated['discount'] = $this->numericInput($request->get('discount'), 0);
        $dp = $request->get('discount_percent');
        $validated['discount_percent'] = ($dp !== null && $dp !== '') ? min(100, max(0, (float) preg_replace('/[^\d.]/', '', FormatHelper::persianToEnglish((string) $dp)))) : null;
        $ids = $request->validate(['payment_option_ids' => 'nullable|array', 'payment_option_ids.*' => 'integer|exists:payment_options,id'])['payment_option_ids'] ?? [];
        $ids = array_values(array_map('intval', $ids));
        $validated['payment_option_ids'] = $ids;
        $fields = [];
        foreach ($ids as $id) {
            $fields[(string) $id] = [
                'print_card_number' => $request->boolean("payment_option_fields.{$id}.print_card_number"),
                'print_iban' => $request->boolean("payment_option_fields.{$id}.print_iban"),
                'print_account_number' => $request->boolean("payment_option_fields.{$id}.print_account_number"),
            ];
        }
        $validated['payment_option_fields'] = $fields;
        if ($invoice->user_id === $request->user()->id || $request->user()->isAdmin()) {
            $validated['assigned_to_id'] = $request->filled('assigned_to_id') ? $request->assigned_to_id : null;
        }
        $items = $this->parseItemsFromRequest($request);

        $invoice->update($validated);
        $this->syncItems($invoice, $items);
        $this->syncTags($invoice, $request->input('tag_ids', []));
        $invoice->recalculateTotals();

        if ($invoice->status === Invoice::STATUS_FINAL) {
            $invoice->applyContactBalanceForInvoice();
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'فاکتور به‌روزرسانی شد.');
    }

    /** Redirect GET requests to invoice show (e.g. bookmarks / open in new tab). */
    public function showMarkFinal(Invoice $invoice)
    {
        abort_unless($invoice->isVisibleTo(request()->user()), 403, 'شما به این فاکتور دسترسی ندارید.');
        return redirect()->route('invoices.show', $invoice);
    }

    /** Mark draft invoice as final so payments can be recorded. */
    public function markFinal(Invoice $invoice)
    {
        abort_unless($invoice->isVisibleTo(request()->user()), 403, 'شما به این فاکتور دسترسی ندارید.');

        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            return redirect()->route('invoices.show', $invoice)->with('error', 'فقط فاکتور پیش‌نویس قابل نهایی شدن است.');
        }
        $invoice->update(['status' => Invoice::STATUS_FINAL]);
        $invoice->applyContactBalanceForInvoice();
        return redirect()->route('invoices.show', $invoice)->with('success', 'فاکتور نهایی شد. اکنون می‌توانید پرداخت ثبت کنید.');
    }

    public function destroy(Invoice $invoice)
    {
        abort_unless($invoice->isVisibleTo(request()->user()), 403, 'شما به این فاکتور دسترسی ندارید.');
        abort_unless(request()->user()->canModule('invoices', \App\Models\User::ABILITY_DELETE), 403, 'شما مجوز حذف فاکتور را ندارید.');

        if ($invoice->status === Invoice::STATUS_DRAFT) {
            foreach ($invoice->payments as $payment) {
                $payment->reverseContactBalance();
            }
        } elseif ($invoice->status === Invoice::STATUS_FINAL && $invoice->canEditOrDelete()) {
            $invoice->reverseContactBalanceForInvoice();
        } else {
            return redirect()->route('invoices.index')->with('error', 'این فاکتور قابل حذف نیست. فقط فاکتور پیش‌نویس یا فاکتور نهایی‌شده بدون هیچ پرداختی قابل حذف است.');
        }
        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'فاکتور حذف شد.');
    }

    public function showSetPaid(Invoice $invoice)
    {
        abort_unless($invoice->isVisibleTo(request()->user()), 403, 'شما به این فاکتور دسترسی ندارید.');

        $invoice->load('contact');
        $paymentOptions = PaymentOption::orderBy('sort')->get();
        $remaining = (float) $invoice->total - $invoice->totalPaid();
        $defaultPaidAt = FormatHelper::shamsi(now());
        return view('invoices.set-paid', compact('invoice', 'paymentOptions', 'remaining', 'defaultPaidAt'));
    }

    public function submitSetPaid(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->isVisibleTo($request->user()), 403, 'شما به این فاکتور دسترسی ندارید.');

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'required|string|max:20',
            'payment_option_id' => 'nullable|exists:payment_options,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'notes' => 'nullable|string',
        ]);
        if (empty($data['payment_option_id']) && empty($data['contact_id'])) {
            return back()->withErrors(['payment_option_id' => 'یکی از حساب بانکی یا مخاطب را انتخاب کنید.'])->withInput();
        }
        if (!empty($data['payment_option_id']) && !empty($data['contact_id'])) {
            return back()->withErrors(['payment_option_id' => 'فقط یکی از حساب بانکی یا مخاطب را انتخاب کنید.'])->withInput();
        }
        $paidAt = trim($data['paid_at']);
        $gregorian = FormatHelper::shamsiToGregorian($paidAt);
        if ($gregorian === null) {
            return back()->withErrors(['paid_at' => 'تاریخ شمسی معتبر نیست. فرمت: ۱۴۰۳/۱۱/۱۳'])->withInput();
        }
        $data['paid_at'] = $gregorian;
        $payment = new InvoicePayment([
            'amount' => $data['amount'],
            'paid_at' => $data['paid_at'],
            'payment_option_id' => $data['payment_option_id'] ?? null,
            'contact_id' => $data['contact_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
        $invoice->payments()->save($payment);
        $payment->applyInvoiceContactBalance();
        $payment->applyContactBalance();
        return redirect()->route('invoices.show', $invoice)->with('success', 'پرداخت ثبت شد.');
    }

    public function destroyPayment(Invoice $invoice, InvoicePayment $payment)
    {
        abort_unless($invoice->isVisibleTo(request()->user()), 403, 'شما به این فاکتور دسترسی ندارید.');

        if ($payment->invoice_id !== $invoice->id) {
            abort(404);
        }
        $payment->reverseInvoiceContactBalance();
        $payment->reverseContactBalance();
        $payment->delete();
        return back()->with('success', 'پرداخت حذف شد.');
    }

    public function print(Invoice $invoice)
    {
        abort_unless($invoice->isVisibleTo(request()->user()), 403, 'شما به این فاکتور دسترسی ندارید.');

        $invoice->load('contact', 'items');
        $paymentOptions = $invoice->selectedPaymentOptions();

        return view('invoices.print', compact('invoice', 'paymentOptions'));
    }

    /** No-login printable invoice via signed URL (for sharing with customer). */
    public function publicPrint(Invoice $invoice)
    {
        $invoice->load('contact', 'items');
        $paymentOptions = $invoice->selectedPaymentOptions();

        return view('invoices.print', [
            'invoice' => $invoice,
            'paymentOptions' => $paymentOptions,
            'public' => true,
        ]);
    }

    public function pdf(Invoice $invoice)
    {
        abort_unless($invoice->isVisibleTo(request()->user()), 403, 'شما به این فاکتور دسترسی ندارید.');

        $invoice->load('contact', 'items');
        $paymentOptions = $invoice->selectedPaymentOptions();

        try {
            $prevMemory = ini_get('memory_limit');
            ini_set('memory_limit', '512M');

            $pdf = Pdf::loadView('invoices.print', compact('invoice', 'paymentOptions'))
                ->setPaper('a5')
                ->setOption('isRemoteEnabled', true)
                ->setOption('isFontSubsettingEnabled', true)
                ->setOption('chroot', [public_path(), base_path('storage')]);

            $response = $pdf->download('invoice-' . ($invoice->invoice_number ?: $invoice->id) . '.pdf');

            ini_set('memory_limit', $prevMemory);

            return $response;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PDF generation failed: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'خطا در ایجاد PDF. لطفاً از دکمه «نسخه چاپ» استفاده کرده و در مرورگر چاپ/ذخیره PDF کنید.');
        }
    }

    private function numericInput(mixed $value, int $default): int
    {
        if ($value === null || $value === '') {
            return $default;
        }
        $value = FormatHelper::persianToEnglish((string) $value);
        return (int) preg_replace('/[^0-9-]/', '', $value) ?: $default;
    }

    private function rules(): array
    {
        return [
            'contact_id' => 'required|exists:contacts,id',
            'type' => 'required|in:sell,buy',
            'assigned_to_id' => 'nullable|exists:users,id',
            'invoice_number' => 'nullable|string|max:50',
            'date' => 'required|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    private function parseItemsFromRequest(Request $request): array
    {
        $items = [];
        $raw = $request->get('items', []);
        if (!is_array($raw)) {
            return $items;
        }
        foreach ($raw as $idx => $item) {
            $desc = is_array($item) ? trim($item['description'] ?? '') : '';
            if ($desc === '') {
                continue;
            }
            $qty = (float) str_replace([','], '', FormatHelper::persianToEnglish((string) (is_array($item) ? ($item['quantity'] ?? 1) : 1)));
            $price = $this->numericInput(is_array($item) ? ($item['unit_price'] ?? 0) : 0, 0);
            $rawAmount = is_array($item) ? ($item['amount'] ?? null) : null;
            $amount = $rawAmount !== null && $rawAmount !== '' ? $this->numericInput($rawAmount, 0) : (int) round($qty * $price);
            $productId = null;
            if (!empty($item['product_id']) && Product::query()->visibleToUser($request->user())->where('id', $item['product_id'])->exists()) {
                $productId = (int) $item['product_id'];
            }
            $items[] = [
                'description' => $desc,
                'product_id' => $productId,
                'quantity' => $qty,
                'unit_price' => $price,
                'amount' => $amount,
                'sort' => $idx,
            ];
        }
        return $items;
    }

    private function syncItems(Invoice $invoice, array $items): void
    {
        $invoice->items()->delete();
        foreach ($items as $item) {
            $invoice->items()->create($item);
        }
    }

    private function normalizeShamsiDates(Request $request): array
    {
        $errors = [];
        $date = $request->get('date');
        if (is_string($date) && $date !== '') {
            $gregorian = FormatHelper::shamsiToGregorian($date);
            if ($gregorian !== null) {
                $request->merge(['date' => $gregorian]);
            } else {
                $errors['date'] = 'تاریخ شمسی معتبر نیست. فرمت صحیح: ۱۴۰۳/۱۱/۱۳';
            }
        }
        $dueDate = $request->get('due_date');
        if (is_string($dueDate) && $dueDate !== '') {
            $gregorian = FormatHelper::shamsiToGregorian($dueDate);
            if ($gregorian !== null) {
                $request->merge(['due_date' => $gregorian]);
            } else {
                $errors['due_date'] = 'تاریخ سررسید معتبر نیست. فرمت صحیح: ۱۴۰۳/۱۱/۱۳';
            }
        }
        return $errors;
    }

    private function syncTags(Invoice $invoice, array $tagIds): void
    {
        $validTagIds = Tag::forCurrentUser()
            ->whereIn('id', $tagIds)
            ->pluck('id')
            ->toArray();
        $invoice->tags()->sync($validTagIds);
    }

    public function storeAttachment(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->isVisibleTo($request->user()), 403, 'شما به این فاکتور دسترسی ندارید.');

        $request->validate(['file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf']);
        $file = $request->file('file');
        $dir = 'attachments/invoices/' . $invoice->id;
        $path = $file->store($dir, 'public');
        $invoice->attachments()->create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
        return redirect()->route('invoices.show', $invoice)->with('success', 'فایل پیوست شد.');
    }

    public function destroyAttachment(Invoice $invoice, Attachment $attachment)
    {
        abort_unless($invoice->isVisibleTo(request()->user()), 403, 'شما به این فاکتور دسترسی ندارید.');

        if ($attachment->attachable_type !== Invoice::class || (int) $attachment->attachable_id !== (int) $invoice->id) {
            abort(404);
        }
        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();
        return redirect()->route('invoices.show', $invoice)->with('success', 'پیوست حذف شد.');
    }
}
