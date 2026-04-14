<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\BusinessExpense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceItemBuyAllocation;
use App\Models\InvoiceItemExpenseAllocation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceItemCostController extends Controller
{
    public function show(Request $request, Invoice $invoice)
    {
        abort_unless($request->user()->canModule('invoices', User::ABILITY_VIEW), 403, 'شما به این بخش دسترسی ندارید.');
        abort_unless($invoice->isVisibleTo($request->user()), 403, 'شما به این فاکتور دسترسی ندارید.');
        abort_unless($invoice->type === Invoice::TYPE_SELL, 404);

        $invoice->load([
            'items.sellBuyCostLinks.buyItem.invoice.contact',
            'items.sellExpenseCostLinks.businessExpense.expenseCategory',
            'contact',
        ]);

        $hasBuyReceipts = Invoice::query()
            ->visibleToUser($request->user())
            ->where('type', Invoice::TYPE_BUY)
            ->exists();

        $canLinkExpenses = $request->user()->canModule('expenses', User::ABILITY_VIEW);

        return view('invoices.item-costs', compact('invoice', 'hasBuyReceipts', 'canLinkExpenses'));
    }

    /** JSON: search buy receipts by number, vendor name, or line description (for cost-link UI). */
    public function searchBuyReceipts(Request $request, Invoice $invoice): JsonResponse
    {
        abort_unless($request->user()->canModule('invoices', User::ABILITY_VIEW), 403);
        abort_unless($invoice->isVisibleTo($request->user()), 403);
        abort_unless($invoice->type === Invoice::TYPE_SELL, 404);

        $raw = trim((string) $request->query('q', ''));
        $term = FormatHelper::persianToEnglish($raw);
        if (mb_strlen($term) < 1) {
            return response()->json(['receipts' => []]);
        }
        $like = '%'.addcslashes($term, '%_\\').'%';

        $receipts = Invoice::query()
            ->visibleToUser($request->user())
            ->where('type', Invoice::TYPE_BUY)
            ->where(function ($q) use ($like) {
                $q->where('invoice_number', 'like', $like)
                    ->orWhereHas('contact', fn ($c) => $c->where('name', 'like', $like))
                    ->orWhereHas('items', fn ($i) => $i->where('description', 'like', $like));
            })
            ->withCount('items')
            ->with('contact:id,name')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(25)
            ->get(['id', 'invoice_number', 'date', 'contact_id', 'total']);

        $out = $receipts->map(function (Invoice $inv) {
            return [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'date_label' => FormatHelper::shamsi($inv->date),
                'contact_name' => $inv->contact?->name ?? '—',
                'total' => (int) $inv->total,
                'items_count' => $inv->items_count,
            ];
        });

        return response()->json(['receipts' => $out]);
    }

    /** JSON: line items for one buy receipt (remaining qty for allocations). */
    public function buyReceiptLines(Request $request, Invoice $invoice, Invoice $buyInvoice): JsonResponse
    {
        abort_unless($request->user()->canModule('invoices', User::ABILITY_VIEW), 403);
        abort_unless($invoice->isVisibleTo($request->user()), 403);
        abort_unless($invoice->type === Invoice::TYPE_SELL, 404);
        abort_unless($buyInvoice->type === Invoice::TYPE_BUY, 404);
        abort_unless($buyInvoice->isVisibleTo($request->user()), 403);

        $buyInvoice->load(['items.buySellCostLinks', 'contact']);

        $items = $buyInvoice->items->map(function (InvoiceItem $item) {
            $used = (float) $item->buySellCostLinks->sum('quantity');
            $qty = (float) $item->quantity;
            $remaining = max(0, $qty - $used);

            return [
                'id' => $item->id,
                'description' => $item->description,
                'quantity' => $qty,
                'remaining' => $remaining,
                'unit_cost_rial' => $item->purchaseUnitCostRial(),
                'product_id' => $item->product_id,
            ];
        })->values();

        return response()->json([
            'invoice' => [
                'id' => $buyInvoice->id,
                'invoice_number' => $buyInvoice->invoice_number,
                'date_label' => FormatHelper::shamsi($buyInvoice->date),
                'contact_name' => $buyInvoice->contact?->name ?? '—',
                'total' => (int) $buyInvoice->total,
            ],
            'items' => $items,
        ]);
    }

    /** JSON: search operational expenses for cost attribution (remaining allocatable rial). */
    public function searchExpenses(Request $request, Invoice $invoice): JsonResponse
    {
        abort_unless($request->user()->canModule('expenses', User::ABILITY_VIEW), 403);
        abort_unless($request->user()->canModule('invoices', User::ABILITY_VIEW), 403);
        abort_unless($invoice->isVisibleTo($request->user()), 403);
        abort_unless($invoice->type === Invoice::TYPE_SELL, 404);

        $raw = trim((string) $request->query('q', ''));
        $term = FormatHelper::persianToEnglish($raw);
        if (mb_strlen($term) < 1) {
            return response()->json(['expenses' => []]);
        }
        $like = '%'.addcslashes($term, '%_\\').'%';

        $expenses = BusinessExpense::query()
            ->visibleToUser($request->user())
            ->with('expenseCategory')
            ->withSum('itemExpenseAllocations', 'amount_rial')
            ->where(function ($q) use ($like, $term) {
                $q->where('notes', 'like', $like)
                    ->orWhereHas('expenseCategory', fn ($c) => $c->where('name', 'like', $like));
                if (ctype_digit($term)) {
                    $q->orWhereKey((int) $term);
                }
            })
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        $out = $expenses->map(function (BusinessExpense $e) {
            $allocated = (int) ($e->item_expense_allocations_sum_amount_rial ?? 0);
            $outlay = $e->totalOutlayRial();
            $remaining = max(0, $outlay - $allocated);

            return [
                'id' => $e->id,
                'paid_at_label' => FormatHelper::shamsi($e->paid_at),
                'category' => $e->expenseCategory?->name ?? '—',
                'total_outlay' => $outlay,
                'allocated' => $allocated,
                'remaining' => $remaining,
                'notes' => $e->notes ? \Illuminate\Support\Str::limit((string) $e->notes, 80) : '',
            ];
        });

        return response()->json(['expenses' => $out]);
    }

    public function storeExpense(Request $request, Invoice $invoice)
    {
        abort_unless($request->user()->canModule('expenses', User::ABILITY_VIEW), 403);
        abort_unless($request->user()->canModule('invoices', User::ABILITY_EDIT), 403, 'شما مجوز ویرایش فاکتور را ندارید.');
        abort_unless($invoice->isVisibleTo($request->user()), 403);
        abort_unless($invoice->type === Invoice::TYPE_SELL, 404);

        $amountNorm = FormatHelper::persianToEnglish(trim((string) $request->input('amount_rial', '')));
        $request->merge(['amount_rial' => $amountNorm]);

        $validated = $request->validate([
            'sell_invoice_item_id' => 'required|integer|exists:invoice_items,id',
            'business_expense_id' => 'required|integer|exists:business_expenses,id',
            'amount_rial' => 'required|numeric|min:1',
        ]);

        $sellItem = $invoice->items()->whereKey($validated['sell_invoice_item_id'])->firstOrFail();

        $expense = BusinessExpense::query()
            ->visibleToUser($request->user())
            ->whereKey($validated['business_expense_id'])
            ->firstOrFail();

        $addRial = (int) round((float) $validated['amount_rial']);
        if ($addRial < 1) {
            return back()->withErrors(['amount_rial' => 'مبلغ معتبر وارد کنید.'])->withInput();
        }

        $allocation = InvoiceItemExpenseAllocation::firstOrNew([
            'sell_invoice_item_id' => $sellItem->id,
            'business_expense_id' => $expense->id,
        ]);

        $oldPair = $allocation->exists ? (int) $allocation->amount_rial : 0;
        $newPair = $oldPair + $addRial;

        $usedElsewhere = (int) InvoiceItemExpenseAllocation::query()
            ->where('business_expense_id', $expense->id)
            ->when($allocation->exists, fn ($q) => $q->where('id', '!=', $allocation->id))
            ->sum('amount_rial');

        if ($usedElsewhere + $newPair > $expense->totalOutlayRial()) {
            return back()->withErrors(['amount_rial' => 'جمع مبلغ وصل‌شده از این هزینه از مبلغ کل آن (با کارمزد) بیشتر می‌شود.'])->withInput();
        }

        $allocation->amount_rial = $newPair;
        $allocation->save();

        return redirect()->route('invoices.item-costs.show', $invoice)->with('success', 'هزینه به ردیف فروش وصل شد.');
    }

    public function destroyExpense(Request $request, Invoice $invoice, InvoiceItemExpenseAllocation $expense_allocation)
    {
        abort_unless($request->user()->canModule('expenses', User::ABILITY_VIEW), 403);
        abort_unless($request->user()->canModule('invoices', User::ABILITY_EDIT), 403);
        abort_unless($invoice->isVisibleTo($request->user()), 403);
        abort_unless($invoice->type === Invoice::TYPE_SELL, 404);

        abort_unless(
            $expense_allocation->sellItem && (int) $expense_allocation->sellItem->invoice_id === (int) $invoice->id,
            404
        );

        $expense_allocation->delete();

        return redirect()->route('invoices.item-costs.show', $invoice)->with('success', 'مبلغ هزینه حذف شد.');
    }

    public function store(Request $request, Invoice $invoice)
    {
        abort_unless($request->user()->canModule('invoices', User::ABILITY_EDIT), 403, 'شما مجوز ویرایش فاکتور را ندارید.');
        abort_unless($invoice->isVisibleTo($request->user()), 403, 'شما به این فاکتور دسترسی ندارید.');
        abort_unless($invoice->type === Invoice::TYPE_SELL, 404);

        $qtyNormalized = FormatHelper::persianToEnglish(trim((string) $request->input('quantity', '')));
        $request->merge(['quantity' => $qtyNormalized]);

        $validated = $request->validate([
            'sell_invoice_item_id' => 'required|integer|exists:invoice_items,id',
            'buy_invoice_item_id' => 'required|integer|exists:invoice_items,id',
            'quantity' => 'required|numeric|min:0.0001',
        ]);

        $sellItem = $invoice->items()->whereKey($validated['sell_invoice_item_id'])->firstOrFail();

        $buyItem = InvoiceItem::query()
            ->with('invoice')
            ->whereKey($validated['buy_invoice_item_id'])
            ->firstOrFail();

        abort_unless($buyItem->invoice->type === Invoice::TYPE_BUY, 403);
        abort_unless($buyItem->invoice->isVisibleTo($request->user()), 403);

        if ($sellItem->product_id && $buyItem->product_id && (int) $sellItem->product_id !== (int) $buyItem->product_id) {
            return back()->withErrors(['buy_invoice_item_id' => 'کالای انتخاب‌شده در رسید خرید با کالای ردیف فروش یکسان نیست.'])->withInput();
        }

        $addQty = (float) $validated['quantity'];

        $allocation = InvoiceItemBuyAllocation::firstOrNew([
            'sell_invoice_item_id' => $sellItem->id,
            'buy_invoice_item_id' => $buyItem->id,
        ]);

        $oldPair = $allocation->exists ? (float) $allocation->quantity : 0.0;
        $newPair = $oldPair + $addQty;

        $sellQty = (float) $sellItem->quantity;
        $buyQty = (float) $buyItem->quantity;

        $sellSumOthers = (float) $sellItem->sellBuyCostLinks()
            ->where('buy_invoice_item_id', '!=', $buyItem->id)
            ->sum('quantity');
        $sellTotal = $sellSumOthers + $newPair;

        $buySumOthers = (float) $buyItem->buySellCostLinks()
            ->where('sell_invoice_item_id', '!=', $sellItem->id)
            ->sum('quantity');
        $buyTotal = $buySumOthers + $newPair;

        if ($sellTotal > $sellQty + 1e-4) {
            return back()->withErrors(['quantity' => 'جمع تعدادهای وصل‌شده به این ردیف فروش از تعداد ردیف بیشتر می‌شود.'])->withInput();
        }
        if ($buyTotal > $buyQty + 1e-4) {
            return back()->withErrors(['quantity' => 'جمع تعدادهای وصل‌شده از این ردیف خرید از تعداد موجود در رسید بیشتر می‌شود.'])->withInput();
        }

        $allocation->quantity = $newPair;
        $allocation->save();

        return redirect()->route('invoices.item-costs.show', $invoice)->with('success', 'ارتباط ردیف خرید با فروش ثبت شد.');
    }

    public function destroy(Request $request, Invoice $invoice, int $allocation)
    {
        abort_unless($request->user()->canModule('invoices', User::ABILITY_EDIT), 403, 'شما مجوز ویرایش فاکتور را ندارید.');
        abort_unless($invoice->isVisibleTo($request->user()), 403, 'شما به این فاکتور دسترسی ندارید.');
        abort_unless($invoice->type === Invoice::TYPE_SELL, 404);

        $model = InvoiceItemBuyAllocation::query()
            ->whereKey($allocation)
            ->whereHas('sellItem', fn ($q) => $q->where('invoice_id', $invoice->id))
            ->firstOrFail();

        $model->delete();

        return redirect()->route('invoices.item-costs.show', $invoice)->with('success', 'ارتباط حذف شد.');
    }
}
