<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\BusinessExpense;
use App\Models\ExpenseCategory;
use App\Models\PaymentOption;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    /** @return array{0: string, 1: string, 2: string, 3: string} */
    protected function resolvePeriod(Request $request): array
    {
        $fromIn = trim((string) $request->input('from', ''));
        $toIn = trim((string) $request->input('to', ''));

        if ($fromIn === '' && $toIn === '') {
            $today = FormatHelper::shamsi(now());
            $fromIn = $today;
            $toIn = $today;
        } elseif ($fromIn === '') {
            $fromIn = $toIn;
        } elseif ($toIn === '') {
            $toIn = $fromIn;
        }

        $fromG = FormatHelper::shamsiToGregorian($fromIn);
        $toG = FormatHelper::shamsiToGregorian($toIn);

        if ($fromG === null) {
            $fromG = Carbon::now('Asia/Tehran')->format('Y-m-d');
        }
        if ($toG === null) {
            $toG = $fromG;
        }
        if ($fromG > $toG) {
            [$fromG, $toG] = [$toG, $fromG];
        }

        $fromCarbon = Carbon::parse($fromG, 'Asia/Tehran')->startOfDay();
        $toCarbon = Carbon::parse($toG, 'Asia/Tehran')->endOfDay();

        $fromLabel = FormatHelper::shamsi($fromCarbon);
        $toLabel = FormatHelper::shamsi($toCarbon);

        return [$fromCarbon->format('Y-m-d'), $toCarbon->format('Y-m-d'), $fromLabel, $toLabel];
    }

    /** @param  list<list<string|int|float>>  $rows */
    protected function csvDownload(array $headers, array $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** @return list<int> */
    protected function filterTagIdsFromRequest(Request $request): array
    {
        $selected = array_values(array_unique(array_filter(array_map('intval', (array) $request->input('tag_ids', [])))));
        if ($request->filled('tag_id')) {
            $legacy = $request->integer('tag_id');
            if ($legacy > 0) {
                $selected[] = $legacy;
            }
        }

        return array_values(array_unique(array_filter($selected)));
    }

    public function index(Request $request)
    {
        abort_unless($request->user()->canModule('expenses', User::ABILITY_VIEW), 403, 'شما به این بخش دسترسی ندارید.');

        [$fromYmd, $toYmd, $fromLabel, $toLabel] = $this->resolvePeriod($request);
        $expenseCategoryId = $request->integer('expense_category_id');
        $selectedTagIds = $this->filterTagIdsFromRequest($request);

        $query = BusinessExpense::query()
            ->visibleToUser($request->user())
            ->with(['paymentOption', 'tags', 'expenseCategory'])
            ->whereDate('paid_at', '>=', $fromYmd)
            ->whereDate('paid_at', '<=', $toYmd)
            ->orderByDesc('paid_at')
            ->orderByDesc('id');

        if ($expenseCategoryId > 0 && ExpenseCategory::whereKey($expenseCategoryId)->exists()) {
            $query->where('expense_category_id', $expenseCategoryId);
        }

        if (count($selectedTagIds) > 0) {
            $validTagIds = Tag::forCurrentUser()->whereIn('id', $selectedTagIds)->pluck('id')->all();
            if (count($validTagIds) > 0) {
                $query->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $validTagIds));
            }
        }

        $sumAmount = (int) (clone $query)->sum('amount');
        $sumFees = (int) ((clone $query)->sum('fee_amount') ?? 0);
        $sumOutlay = $sumAmount + $sumFees;
        $expenses = $query->paginate(30)->withQueryString();

        if ($request->query('export') === 'csv') {
            $all = (clone $query)->limit(5000)->get();
            $rows = [];
            foreach ($all as $e) {
                $rows[] = [
                    FormatHelper::shamsi($e->paid_at),
                    $e->amount,
                    (int) ($e->fee_amount ?? 0),
                    $e->totalOutlayRial(),
                    $e->expenseCategory?->name ?? '',
                    $e->paymentOption?->label ?? '',
                    $e->tags->pluck('name')->implode('؛ '),
                    $e->notes ?? '',
                ];
            }

            return $this->csvDownload(
                ['تاریخ', 'مبلغ اصلی', 'کارمزد', 'جمع خروج از حساب', 'دسته', 'کارت/حساب', 'برچسب‌ها', 'یادداشت'],
                $rows,
                'expenses-'.str_replace(['/', '\\'], '-', $fromLabel).'-'.str_replace(['/', '\\'], '-', $toLabel).'.csv'
            );
        }

        $tags = Tag::forCurrentUser()->orderBy('name')->get();
        $expenseCategories = ExpenseCategory::ordered()->get();

        return view('expenses.index', compact(
            'expenses',
            'fromLabel',
            'toLabel',
            'expenseCategoryId',
            'selectedTagIds',
            'tags',
            'expenseCategories',
            'sumAmount',
            'sumFees',
            'sumOutlay'
        ));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->canModule('expenses', User::ABILITY_CREATE), 403, 'شما مجوز ثبت هزینه را ندارید.');

        $expenseCategories = ExpenseCategory::ordered()->get();
        $defaultCatId = ExpenseCategory::where('code', 'other')->value('id') ?? $expenseCategories->first()?->id;

        $expense = new BusinessExpense([
            'paid_at' => now(),
            'expense_category_id' => $defaultCatId,
        ]);
        $tags = Tag::forCurrentUser()->orderBy('name')->get();
        $paymentOptions = PaymentOption::orderBy('sort')->get();
        $defaultPaidAt = FormatHelper::shamsi(now());

        return view('expenses.create', compact('expense', 'tags', 'paymentOptions', 'defaultPaidAt', 'expenseCategories'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canModule('expenses', User::ABILITY_CREATE), 403, 'شما مجوز ثبت هزینه را ندارید.');

        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'fee_amount' => 'nullable|numeric|min:0',
            'paid_at' => 'required|string|max:20',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'payment_option_id' => 'nullable|exists:payment_options,id',
            'notes' => 'nullable|string',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',
        ]);

        $gregorian = FormatHelper::shamsiToGregorian(trim($data['paid_at']));
        if ($gregorian === null) {
            return back()->withErrors(['paid_at' => 'تاریخ شمسی معتبر نیست.'])->withInput();
        }

        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $data['paid_at'] = $gregorian;
        $data['user_id'] = $request->user()->id;
        $fee = $data['fee_amount'] ?? null;
        $data['fee_amount'] = ($fee !== null && (float) $fee > 0) ? (int) round((float) $fee) : null;

        $expense = BusinessExpense::create($data);
        $this->syncExpenseTags($expense, $tagIds);

        return redirect()->route('expenses.index')->with('success', 'هزینه ثبت شد.');
    }

    public function show(Request $request, BusinessExpense $expense)
    {
        abort_unless($request->user()->canModule('expenses', User::ABILITY_VIEW), 403, 'شما به این بخش دسترسی ندارید.');
        abort_unless($expense->isVisibleTo($request->user()), 403, 'شما به این رکورد دسترسی ندارید.');

        return redirect()->route('expenses.edit', $expense);
    }

    public function edit(Request $request, BusinessExpense $expense)
    {
        abort_unless($request->user()->canModule('expenses', User::ABILITY_EDIT), 403, 'شما مجوز ویرایش هزینه را ندارید.');
        abort_unless($expense->isVisibleTo($request->user()), 403, 'شما به این رکورد دسترسی ندارید.');

        $expense->load('tags');
        $expenseCategories = ExpenseCategory::ordered()->get();
        $tags = Tag::forCurrentUser()->orderBy('name')->get();
        $paymentOptions = PaymentOption::orderBy('sort')->get();
        $defaultPaidAt = FormatHelper::shamsi($expense->paid_at);

        return view('expenses.edit', compact('expense', 'tags', 'paymentOptions', 'defaultPaidAt', 'expenseCategories'));
    }

    public function update(Request $request, BusinessExpense $expense)
    {
        abort_unless($request->user()->canModule('expenses', User::ABILITY_EDIT), 403, 'شما مجوز ویرایش هزینه را ندارید.');
        abort_unless($expense->isVisibleTo($request->user()), 403, 'شما به این رکورد دسترسی ندارید.');

        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'fee_amount' => 'nullable|numeric|min:0',
            'paid_at' => 'required|string|max:20',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'payment_option_id' => 'nullable|exists:payment_options,id',
            'notes' => 'nullable|string',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',
        ]);

        $gregorian = FormatHelper::shamsiToGregorian(trim($data['paid_at']));
        if ($gregorian === null) {
            return back()->withErrors(['paid_at' => 'تاریخ شمسی معتبر نیست.'])->withInput();
        }

        $tagIds = $data['tag_ids'] ?? [];
        unset($data['tag_ids']);

        $data['paid_at'] = $gregorian;
        $fee = $data['fee_amount'] ?? null;
        $data['fee_amount'] = ($fee !== null && (float) $fee > 0) ? (int) round((float) $fee) : null;
        $expense->update($data);
        $this->syncExpenseTags($expense, $tagIds);

        return redirect()->route('expenses.index')->with('success', 'هزینه به‌روز شد.');
    }

    public function destroy(Request $request, BusinessExpense $expense)
    {
        abort_unless($request->user()->canModule('expenses', User::ABILITY_DELETE), 403, 'شما مجوز حذف هزینه را ندارید.');
        abort_unless($expense->isVisibleTo($request->user()), 403, 'شما به این رکورد دسترسی ندارید.');

        $expense->tags()->detach();
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'هزینه حذف شد.');
    }

    /** @param  list<int>  $tagIds */
    private function syncExpenseTags(BusinessExpense $expense, array $tagIds): void
    {
        $validTagIds = Tag::forCurrentUser()
            ->whereIn('id', $tagIds)
            ->pluck('id')
            ->toArray();
        $expense->tags()->sync($validTagIds);
    }
}
