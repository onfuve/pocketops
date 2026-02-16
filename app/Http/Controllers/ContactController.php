<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\PaymentOption;
use App\Models\Contact;
use App\Models\ContactPhone;
use App\Models\ContactTransaction;
use App\Models\Setting;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->canModule('contacts', \App\Models\User::ABILITY_VIEW), 403, 'شما به این بخش دسترسی ندارید.');
        $q = $request->get('q');
        $balanceFilter = $request->get('balance'); // positive, negative, zero
        $sort = $request->get('sort', 'recent'); // name, balance, recent
        $perPage = $request->get('per_page', 20);
        $allowedPerPage = [20, 50, 100, 200, 500];
        if (!in_array((int) $perPage, $allowedPerPage, true)) {
            $perPage = 20;
        }

        $query = Contact::query()
            ->visibleToUser($request->user())
            ->with(['contactPhones', 'linkedContact'])
            ->search($q);

        if ($balanceFilter === 'positive') {
            $query->where('balance', '>', 0);
        } elseif ($balanceFilter === 'negative') {
            $query->where('balance', '<', 0);
        } elseif ($balanceFilter === 'zero') {
            $query->where('balance', '=', 0);
        }

        if ($sort === 'name') {
            $query->orderBy('name');
        } elseif ($sort === 'balance') {
            $query->orderBy('balance', 'desc'); // بستانکار اول، بعد تسویه، بعد بدهکار
        } else {
            $query->latest();
        }

        $contacts = $query->paginate((int) $perPage)->withQueryString();

        return view('contacts.index', compact('contacts', 'q', 'balanceFilter', 'sort', 'perPage'));
    }

    public function bulkDelete(Request $request)
    {
        abort_unless($request->user()->canModule('contacts', \App\Models\User::ABILITY_DELETE), 403, 'شما مجوز حذف مخاطب را ندارید.');

        $ids = $request->input('ids', []);
        if (!is_array($ids)) {
            $ids = [];
        }
        $ids = array_filter(array_map('intval', $ids));

        $contacts = Contact::visibleToUser($request->user())
            ->whereIn('id', $ids)
            ->get();

        $deleted = 0;
        foreach ($contacts as $contact) {
            $contact->delete();
            $deleted++;
        }

        return redirect()->route('contacts.index', array_filter($request->only(['q', 'balance', 'sort', 'per_page', 'page'])))
            ->with('success', $deleted > 0 ? "{$deleted} مخاطب حذف شد." : 'مخاطبی حذف نشد.');
    }

    public function create()
    {
        abort_unless(request()->user()->canModule('contacts', \App\Models\User::ABILITY_CREATE), 403, 'شما مجوز ایجاد مخاطب را ندارید.');
        $contact = new Contact;
        $contact->setRelation('contactPhones', collect());
        $tags = Tag::forCurrentUser()->orderBy('name')->get();
        return view('contacts.create', ['contact' => $contact, 'tags' => $tags]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canModule('contacts', \App\Models\User::ABILITY_CREATE), 403, 'شما مجوز ایجاد مخاطب را ندارید.');
        $validated = $request->validate($this->rules());
        $validated['is_hamkar'] = $request->boolean('is_hamkar');
        $validated['linked_contact_id'] = $request->filled('linked_contact_id') ? $request->linked_contact_id : null;
        $validated['user_id'] = $request->user()->id;
        $phones = $this->parsePhonesFromRequest($request);

        $contact = Contact::create($validated);
        $this->syncPhones($contact, $phones);
        $this->syncTags($contact, $request->input('tag_ids', []));

        return redirect()->route('contacts.index')->with('success', 'مخاطب با موفقیت ذخیره شد.');
    }

    public function searchApi(Request $request)
    {
        $q = $request->get('q', '');
        $exclude = $request->get('exclude'); // contact id to exclude (e.g. self when editing)
        $withBalance = $request->boolean('with_balance');
        $query = Contact::query()
            ->visibleToUser($request->user())
            ->where('name', 'like', '%' . $q . '%')
            ->when($exclude, fn ($q) => $q->where('id', '!=', $exclude))
            ->orderBy('name')
            ->limit(20);
        $contacts = $withBalance
            ? $query->get(['id', 'name', 'balance'])
            : $query->get(['id', 'name']);
        return response()->json($contacts);
    }

    /** For lead form autocomplete: return name + first phone. */
    public function showApi(Contact $contact)
    {
        abort_unless($contact->isVisibleTo(request()->user()), 403, 'شما به این مخاطب دسترسی ندارید.');

        $contact->load('contactPhones');
        $firstPhone = $contact->contactPhones->first();

        return response()->json([
            'id' => $contact->id,
            'name' => $contact->name,
            'first_phone' => $firstPhone ? $firstPhone->phone : null,
        ]);
    }

    public function show(Contact $contact)
    {
        abort_unless(request()->user()->canModule('contacts', \App\Models\User::ABILITY_VIEW), 403, 'شما به این بخش دسترسی ندارید.');
        abort_unless($contact->isVisibleTo(request()->user()), 403, 'شما به این مخاطب دسترسی ندارید.');

        $contact->load('contactPhones', 'linkedContact', 'tags', 'tasks.assignedUsers');
        return view('contacts.show', compact('contact'));
    }

    public function addressLabel(Request $request, Contact $contact)
    {
        abort_unless($contact->isVisibleTo($request->user()), 403, 'شما به این مخاطب دسترسی ندارید.');

        $includeSender = filter_var($request->query('include_sender', false), FILTER_VALIDATE_BOOLEAN);
        $senderAddress = $includeSender ? Setting::senderAddress() : null;

        return view('contacts.address-label', compact('contact', 'includeSender', 'senderAddress'));
    }

    public function showReceivePay(Contact $contact)
    {
        abort_unless($contact->isVisibleTo(request()->user()), 403, 'شما به این مخاطب دسترسی ندارید.');

        $paymentOptions = PaymentOption::orderBy('sort')->get();
        $defaultPaidAt = FormatHelper::shamsi(now());
        return view('contacts.receive-pay', compact('contact', 'paymentOptions', 'defaultPaidAt'));
    }

    public function submitReceivePay(Request $request, Contact $contact)
    {
        abort_unless($contact->isVisibleTo($request->user()), 403, 'شما به این مخاطب دسترسی ندارید.');

        $data = $request->validate([
            'type' => 'required|in:receive,pay',
            'amount' => 'required|numeric|min:0.01',
            'paid_at' => 'required|string|max:20',
            'payment_option_id' => 'nullable|exists:payment_options,id',
            'counterparty_contact_id' => 'nullable|exists:contacts,id',
            'notes' => 'nullable|string',
        ]);
        if (empty($data['payment_option_id']) && empty($data['counterparty_contact_id'])) {
            return back()->withErrors(['payment_option_id' => 'یکی از حساب بانکی یا مخاطب طرف معامله را انتخاب کنید.'])->withInput();
        }
        if (!empty($data['payment_option_id']) && !empty($data['counterparty_contact_id'])) {
            return back()->withErrors(['payment_option_id' => 'فقط یکی از حساب بانکی یا مخاطب را انتخاب کنید.'])->withInput();
        }
        $paidAt = trim($data['paid_at']);
        $gregorian = FormatHelper::shamsiToGregorian($paidAt);
        if ($gregorian === null) {
            return back()->withErrors(['paid_at' => 'تاریخ شمسی معتبر نیست. فرمت: ۱۴۰۳/۱۱/۱۳'])->withInput();
        }
        $data['paid_at'] = $gregorian;
        $data['contact_id'] = $contact->id;
        $transaction = ContactTransaction::create($data);
        $transaction->applyBalances();
        return redirect()->route('contacts.show', $contact)->with('success', $data['type'] === 'receive' ? 'دریافت ثبت شد.' : 'پرداخت ثبت شد.');
    }

    public function edit(Contact $contact)
    {
        abort_unless(request()->user()->canModule('contacts', \App\Models\User::ABILITY_EDIT), 403, 'شما مجوز ویرایش مخاطب را ندارید.');
        abort_unless($contact->isVisibleTo(request()->user()), 403, 'شما به این مخاطب دسترسی ندارید.');

        $contact->load('contactPhones', 'linkedContact', 'tags');
        $tags = Tag::forCurrentUser()->orderBy('name')->get();
        return view('contacts.edit', compact('contact', 'tags'));
    }

    public function update(Request $request, Contact $contact)
    {
        abort_unless($request->user()->canModule('contacts', \App\Models\User::ABILITY_EDIT), 403, 'شما مجوز ویرایش مخاطب را ندارید.');
        abort_unless($contact->isVisibleTo($request->user()), 403, 'شما به این مخاطب دسترسی ندارید.');

        $validated = $request->validate($this->rules());
        $validated['is_hamkar'] = $request->boolean('is_hamkar');
        $validated['linked_contact_id'] = $request->filled('linked_contact_id') ? $request->linked_contact_id : null;
        $phones = $this->parsePhonesFromRequest($request);

        $contact->update($validated);
        $this->syncPhones($contact, $phones);
        $this->syncTags($contact, $request->input('tag_ids', []));

        return redirect()->route('contacts.index')->with('success', 'مخاطب به‌روزرسانی شد.');
    }

    public function destroy(Contact $contact)
    {
        abort_unless($contact->isVisibleTo(request()->user()), 403, 'شما به این مخاطب دسترسی ندارید.');
        abort_unless(request()->user()->canModule('contacts', \App\Models\User::ABILITY_DELETE), 403, 'شما مجوز حذف مخاطب را ندارید.');

        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'مخاطب حذف شد.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $contacts = Contact::query()
            ->visibleToUser($request->user())
            ->search($request->get('q'))
            ->orderBy('name')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="contacts-' . now()->format('Y-m-d-His') . '.csv"',
        ];

        return response()->stream(function () use ($contacts) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($out, [
                'نام', 'شهر', 'آدرس', 'تلفن‌ها', 'وب‌سایت', 'اینستاگرام', 'تلگرام', 'واتساپ',
                'معرف (شخص/شرکت)', 'همکار', 'مخاطب مرتبط (شرکت/فروشگاه)', 'یادداشت', 'تاریخ ایجاد',
            ]);
            foreach ($contacts as $c) {
                $c->load('contactPhones', 'linkedContact');
                $phonesStr = $c->contactPhones->map(fn ($p) => ($p->label ? $p->label . ': ' : '') . $p->phone)->implode(' | ');
                fputcsv($out, [
                    $c->name,
                    $c->city ?? '',
                    $c->address ?? '',
                    $phonesStr,
                    $c->website ?? '',
                    $c->instagram ?? '',
                    $c->telegram ?? '',
                    $c->whatsapp ?? '',
                    $c->referrer_name ?? '',
                    $c->is_hamkar ? 'بله' : 'خیر',
                    $c->linkedContact?->name ?? '',
                    $c->notes ?? '',
                    $c->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }

    public function importForm()
    {
        return view('contacts.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();
        $rows = [];
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return redirect()->route('contacts.import')->with('error', 'فایل قابل خواندن نیست.');
        }
        $first = fgets($handle);
        if ($first !== false && preg_match('/^\xEF\xBB\xBF/', $first)) {
            $first = preg_replace('/^\xEF\xBB\xBF/', '', $first);
        }
        $header = str_getcsv($first ?? '');
        if ($first !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = array_pad($row, count($header), '');
            }
        }
        fclose($handle);
        if (!empty($header[0])) {
            $header[0] = trim($header[0]);
        }

        if (empty($header) || stripos($header[0], 'نام') === false) {
            return redirect()->route('contacts.import')->with('error', 'ستون اول فایل باید «نام» باشد. نمونه‌ی خروجی CSV را ببینید.');
        }

        // Existing contacts for duplicate check (visible to user)
        $existingContactIds = Contact::visibleToUser($request->user())->pluck('id')->toArray();
        $existingNames = Contact::visibleToUser($request->user())->pluck('name')->map(fn ($n) => $this->normalizeImportText($n))->flip()->toArray();
        $existingPhones = ContactPhone::whereIn('contact_id', $existingContactIds)->pluck('phone')->map(fn ($p) => $this->normalizePhoneForCompare($p))->flip()->toArray();

        $imported = 0;
        $errors = [];
        $seenNamesInFile = [];
        $seenPhonesInFile = [];
        $dupNames = [];
        $dupPhones = [];

        foreach ($rows as $i => $row) {
            $row = array_pad($row, count($header), '');
            $data = array_combine($header, $row);
            $name = $this->normalizeImportText($data['نام'] ?? '');
            if ($name === '') {
                continue;
            }
            try {
                // Duplicate check: name
                if (isset($seenNamesInFile[$name])) {
                    $dupNames[] = "«{$name}» در ردیف‌های " . $seenNamesInFile[$name] . " و " . ($i + 2);
                } elseif (isset($existingNames[$name])) {
                    $dupNames[] = "«{$name}» در فایل (ردیف " . ($i + 2) . ") و قبلاً در دیتابیس";
                }
                $seenNamesInFile[$name] = $i + 2;

                // Parse phones and check duplicates; skip contact if duplicate or all-empty
                $phonesStr = $this->normalizeImportText($data['تلفن‌ها'] ?? '') ?: $this->normalizeImportText($data['تلفن'] ?? '');
                $phonesToAdd = [];
                $hasDuplicatePhone = false;
                if ($phonesStr !== '') {
                    foreach (array_filter(array_map(fn ($s) => $this->normalizeImportText($s), explode('|', $phonesStr))) as $idx => $one) {
                        $label = null;
                        if (preg_match('/^(.+):\s*(.+)$/u', $one, $m)) {
                            $label = $this->normalizeImportText($m[1]) ?: null;
                            $one = $this->normalizeImportText($m[2]);
                        }
                        $normPhone = $this->normalizePhoneForCompare($one);
                        if ($normPhone === '') {
                            continue; // skip empty phone, don't add
                        }
                        if (isset($seenPhonesInFile[$normPhone])) {
                            $dupPhones[] = "«{$name}» ردیف " . ($i + 2) . ": شماره {$one} تکراری (ردیف " . $seenPhonesInFile[$normPhone] . ")";
                            $hasDuplicatePhone = true;
                            break;
                        }
                        if (isset($existingPhones[$normPhone])) {
                            $dupPhones[] = "«{$name}» ردیف " . ($i + 2) . ": شماره {$one} قبلاً در دیتابیس";
                            $hasDuplicatePhone = true;
                            break;
                        }
                        $seenPhonesInFile[$normPhone] = $i + 2;
                        $phonesToAdd[] = ['phone' => $one, 'label' => $label, 'sort' => $idx];
                    }
                }

                if ($hasDuplicatePhone) {
                    continue; // don't insert, just report
                }

                $contact = Contact::create([
                    'name' => $name,
                    'user_id' => $request->user()->id,
                    'city' => $this->normalizeImportText($data['شهر'] ?? '') ?: null,
                    'address' => $this->normalizeImportText($data['آدرس'] ?? '') ?: null,
                    'website' => $this->normalizeImportText($data['وب‌سایت'] ?? '') ?: null,
                    'instagram' => $this->normalizeImportText($data['اینستاگرام'] ?? '') ?: null,
                    'telegram' => $this->normalizeImportText($data['تلگرام'] ?? '') ?: null,
                    'whatsapp' => $this->normalizeImportText($data['واتساپ'] ?? '') ?: null,
                    'referrer_name' => $this->normalizeImportText($data['معرف (شخص/شرکت)'] ?? '') ?: null,
                    'is_hamkar' => (bool) preg_match('/بله|1|yes|true/i', $this->normalizeImportText($data['همکار'] ?? '')),
                    'notes' => $this->normalizeImportText($data['یادداشت'] ?? '') ?: null,
                ]);
                foreach ($phonesToAdd as $p) {
                    $contact->contactPhones()->create($p);
                }
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = "ردیف " . ($i + 2) . ": " . $e->getMessage();
            }
        }

        $msg = "{$imported} مخاطب وارد شد.";
        if (!empty($errors)) {
            $msg .= ' خطاها: ' . implode('؛ ', array_slice($errors, 0, 5));
        }
        if (!empty($dupNames)) {
            $msg .= ' — تکرار نام: ' . implode('؛ ', array_slice(array_unique($dupNames), 0, 5));
        }
        if (!empty($dupPhones)) {
            $msg .= ' — تکرار شماره: ' . implode('؛ ', array_slice(array_unique($dupPhones), 0, 5));
        }

        return redirect()->route('contacts.index')->with(
            !empty($errors) ? 'error' : 'success',
            $msg
        );
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:100',
            'website' => 'nullable|string|url|max:500',
            'instagram' => 'nullable|string|max:255',
            'telegram' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'referrer_name' => 'nullable|string|max:255',
            'is_hamkar' => 'boolean',
            'linked_contact_id' => 'nullable|exists:contacts,id',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    private function parsePhonesFromRequest(Request $request): array
    {
        $phones = [];
        $raw = $request->get('phones', []);
        if (is_array($raw)) {
            foreach ($raw as $idx => $item) {
                $num = is_array($item) ? trim($item['phone'] ?? '') : trim((string) $item);
                if ($num !== '') {
                    $phones[] = [
                        'phone' => $num,
                        'label' => is_array($item) ? trim($item['label'] ?? '') ?: null : null,
                        'sort' => $idx,
                    ];
                }
            }
        }
        return $phones;
    }

    private function syncPhones(Contact $contact, array $phones): void
    {
        $contact->contactPhones()->delete();
        foreach ($phones as $p) {
            $contact->contactPhones()->create($p);
        }
    }

    private function syncTags(Contact $contact, array $tagIds): void
    {
        $validTagIds = Tag::forCurrentUser()
            ->whereIn('id', $tagIds)
            ->pluck('id')
            ->toArray();
        $contact->tags()->sync($validTagIds);
    }

    /** Normalize text: trim, collapse multiple spaces, Arabic ك/ي/ى → Persian ک/ی */
    private function normalizeImportText(string $s): string
    {
        $s = trim($s);
        $s = preg_replace('/\s+/u', ' ', $s);
        $s = str_replace(["\xE2\x80\x8C", '‌'], ' ', $s); // ZWNJ → space
        $s = preg_replace('/\s+/u', ' ', trim($s));
        // Arabic ك → Persian ک
        $s = str_replace('ك', 'ک', $s);
        // Arabic ي and ى → Persian ی
        $s = str_replace(['ي', 'ى'], 'ی', $s);
        return trim($s);
    }

    /** Normalize phone for duplicate comparison: digits only */
    private function normalizePhoneForCompare(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone);
    }
}
