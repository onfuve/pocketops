<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\BankAccount;
use App\Models\Contact;
use App\Models\ContactTransaction;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BusinessReportController extends Controller
{
    /** @return array{0: string, 1: string, 2: string, 3: string} gregorian from, gregorian to, shamsi from label, shamsi to label */
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

    protected function isPrint(Request $request): bool
    {
        return $request->query('print') === '1' || $request->boolean('print');
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

    public function index()
    {
        return view('reports.business.index');
    }

    public function bankAccount(Request $request)
    {
        [$fromYmd, $toYmd, $fromLabel, $toLabel] = $this->resolvePeriod($request);
        $bankId = $request->integer('bank_account_id');
        $banks = BankAccount::query()->orderBy('name')->get();

        if ($bankId <= 0) {
            if ($request->query('export') === 'csv') {
                return redirect()
                    ->route('reports.business.bank-account', $request->only(['from', 'to']))
                    ->with('error', 'برای خروجی، ابتدا حساب بانکی را انتخاب کنید.');
            }

            return $this->isPrint($request)
                ? view('layouts.print', [
                    'title' => 'گزارش تراکنش حساب بانکی',
                    'slot' => view('reports.business._bank-account-inner', [
                        'banks' => $banks,
                        'bank' => null,
                        'invoicePayments' => collect(),
                        'contactTransactions' => collect(),
                        'fromLabel' => $fromLabel,
                        'toLabel' => $toLabel,
                        'fromYmd' => $fromYmd,
                        'toYmd' => $toYmd,
                        'sumInvoice' => 0,
                        'sumContact' => 0,
                    ])->render(),
                ])
                : view('reports.business.bank-account', [
                    'banks' => $banks,
                    'bank' => null,
                    'invoicePayments' => collect(),
                    'contactTransactions' => collect(),
                    'fromLabel' => $fromLabel,
                    'toLabel' => $toLabel,
                    'fromYmd' => $fromYmd,
                    'toYmd' => $toYmd,
                    'sumInvoice' => 0,
                    'sumContact' => 0,
                ]);
        }

        $bank = BankAccount::findOrFail($bankId);
        $user = $request->user();

        $invoicePayments = InvoicePayment::query()
            ->with(['invoice.contact', 'paymentOption', 'contact'])
            ->where('bank_account_id', $bankId)
            ->whereHas('invoice', fn ($q) => $q->visibleToUser($user))
            ->whereDate('paid_at', '>=', $fromYmd)
            ->whereDate('paid_at', '<=', $toYmd)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get();

        $contactTransactions = ContactTransaction::query()
            ->with(['contact', 'counterpartyContact', 'paymentOption'])
            ->where('bank_account_id', $bankId)
            ->whereHas('contact', fn ($q) => $q->visibleToUser($user))
            ->whereDate('paid_at', '>=', $fromYmd)
            ->whereDate('paid_at', '<=', $toYmd)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get();

        $sumInvoice = (int) $invoicePayments->sum('amount');
        $sumContact = (int) $contactTransactions->sum('amount');

        if ($request->query('export') === 'csv') {
            $rows = [];
            foreach ($invoicePayments as $p) {
                $inv = $p->invoice;
                $rows[] = [
                    FormatHelper::shamsi($p->paid_at),
                    'پرداخت فاکتور',
                    FormatHelper::englishToPersian((string) (int) $p->amount),
                    $inv ? FormatHelper::englishToPersian((string) ($inv->invoice_number ?? $inv->id)) : '',
                    $inv && $inv->type === Invoice::TYPE_SELL ? 'فروش' : 'خرید',
                    $inv && $inv->contact ? $inv->contact->name : '',
                    $p->notes ?? '',
                ];
            }
            foreach ($contactTransactions as $t) {
                $rows[] = [
                    FormatHelper::shamsi($t->paid_at),
                    $t->type === ContactTransaction::TYPE_RECEIVE ? 'دریافت بدون فاکتور' : 'پرداخت بدون فاکتور',
                    FormatHelper::englishToPersian((string) (int) $t->amount),
                    '',
                    '',
                    $t->contact->name ?? '',
                    $t->notes ?? '',
                ];
            }

            return $this->csvDownload(
                ['تاریخ', 'نوع', 'مبلغ (ریال)', 'شماره فاکتور', 'نوع فاکتور', 'مخاطب', 'یادداشت'],
                $rows,
                'report-bank-'.$bankId.'-'.$fromYmd.'-'.$toYmd.'.csv'
            );
        }

        $viewData = compact(
            'banks',
            'bank',
            'invoicePayments',
            'contactTransactions',
            'fromLabel',
            'toLabel',
            'fromYmd',
            'toYmd',
            'sumInvoice',
            'sumContact'
        );

        return $this->isPrint($request)
            ? view('layouts.print', [
                'title' => 'گزارش تراکنش حساب: '.$bank->name,
                'slot' => view('reports.business._bank-account-inner', $viewData)->render(),
            ])
            : view('reports.business.bank-account', $viewData);
    }

    public function allTransactions(Request $request)
    {
        [$fromYmd, $toYmd, $fromLabel, $toLabel] = $this->resolvePeriod($request);
        $user = $request->user();

        $invoicePayments = InvoicePayment::query()
            ->with(['invoice.contact', 'bankAccount', 'paymentOption', 'contact'])
            ->whereHas('invoice', fn ($q) => $q->visibleToUser($user))
            ->whereDate('paid_at', '>=', $fromYmd)
            ->whereDate('paid_at', '<=', $toYmd)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get();

        $contactTransactions = ContactTransaction::query()
            ->with(['contact', 'counterpartyContact', 'bankAccount', 'paymentOption'])
            ->whereHas('contact', fn ($q) => $q->visibleToUser($user))
            ->whereDate('paid_at', '>=', $fromYmd)
            ->whereDate('paid_at', '<=', $toYmd)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get();

        $merged = [];
        foreach ($invoicePayments as $p) {
            $merged[] = ['paid_at' => $p->paid_at, 'kind' => 'invoice', 'item' => $p];
        }
        foreach ($contactTransactions as $t) {
            $merged[] = ['paid_at' => $t->paid_at, 'kind' => 'contact', 'item' => $t];
        }
        usort($merged, function ($a, $b) {
            $ca = $a['paid_at'] instanceof \DateTimeInterface ? $a['paid_at']->getTimestamp() : 0;
            $cb = $b['paid_at'] instanceof \DateTimeInterface ? $b['paid_at']->getTimestamp() : 0;

            return $cb <=> $ca;
        });

        $sumInvoice = (int) $invoicePayments->sum('amount');
        $sumContactReceive = (int) $contactTransactions->where('type', ContactTransaction::TYPE_RECEIVE)->sum('amount');
        $sumContactPay = (int) $contactTransactions->where('type', ContactTransaction::TYPE_PAY)->sum('amount');

        if ($request->query('export') === 'csv') {
            $rows = [];
            foreach ($merged as $row) {
                if ($row['kind'] === 'invoice') {
                    /** @var InvoicePayment $p */
                    $p = $row['item'];
                    $inv = $p->invoice;
                    $bankLabel = $p->bankAccount?->name ?? ($p->paymentOption?->label ?? '—');
                    $rows[] = [
                        FormatHelper::shamsi($p->paid_at),
                        'پرداخت فاکتور',
                        FormatHelper::englishToPersian((string) (int) $p->amount),
                        $inv ? FormatHelper::englishToPersian((string) ($inv->invoice_number ?? $inv->id)) : '',
                        $inv && $inv->type === Invoice::TYPE_SELL ? 'فروش' : 'خرید',
                        $inv && $inv->contact ? $inv->contact->name : '',
                        $bankLabel,
                        $p->notes ?? '',
                    ];
                } else {
                    /** @var ContactTransaction $t */
                    $t = $row['item'];
                    $bankLabel = $t->bankAccount?->name ?? ($t->paymentOption?->label ?? '—');
                    $rows[] = [
                        FormatHelper::shamsi($t->paid_at),
                        $t->type === ContactTransaction::TYPE_RECEIVE ? 'دریافت' : 'پرداخت',
                        FormatHelper::englishToPersian((string) (int) $t->amount),
                        '—',
                        '—',
                        $t->contact->name ?? '',
                        $bankLabel,
                        $t->notes ?? '',
                    ];
                }
            }

            return $this->csvDownload(
                ['تاریخ', 'نوع', 'مبلغ (ریال)', 'فاکتور', 'فروش/خرید', 'مخاطب', 'حساب / روش', 'یادداشت'],
                $rows,
                'report-all-transactions-'.$fromYmd.'-'.$toYmd.'.csv'
            );
        }

        $viewData = compact(
            'merged',
            'fromLabel',
            'toLabel',
            'fromYmd',
            'toYmd',
            'sumInvoice',
            'sumContactReceive',
            'sumContactPay'
        );

        return $this->isPrint($request)
            ? view('layouts.print', [
                'title' => 'گزارش همه تراکنش‌ها',
                'slot' => view('reports.business._all-transactions-inner', $viewData)->render(),
            ])
            : view('reports.business.all-transactions', $viewData);
    }

    public function invoices(Request $request)
    {
        [$fromYmd, $toYmd, $fromLabel, $toLabel] = $this->resolvePeriod($request);
        $type = $request->input('invoice_type', 'both');
        if (! in_array($type, ['sell', 'buy', 'both'], true)) {
            $type = 'both';
        }

        $user = $request->user();
        $query = Invoice::query()
            ->with(['contact'])
            ->visibleToUser($user)
            ->whereDate('date', '>=', $fromYmd)
            ->whereDate('date', '<=', $toYmd)
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($type === 'sell') {
            $query->where('type', Invoice::TYPE_SELL);
        } elseif ($type === 'buy') {
            $query->where('type', Invoice::TYPE_BUY);
        }

        $invoices = $query->get();
        $sumSell = (int) $invoices->where('type', Invoice::TYPE_SELL)->sum('total');
        $sumBuy = (int) $invoices->where('type', Invoice::TYPE_BUY)->sum('total');

        if ($request->query('export') === 'csv') {
            $rows = [];
            foreach ($invoices as $inv) {
                $rows[] = [
                    FormatHelper::shamsi($inv->date),
                    FormatHelper::englishToPersian((string) ($inv->invoice_number ?? $inv->id)),
                    $inv->type === Invoice::TYPE_SELL ? 'فروش' : 'خرید',
                    $inv->contact?->name ?? '',
                    FormatHelper::englishToPersian((string) (int) $inv->total),
                    $inv->status ?? '',
                ];
            }

            return $this->csvDownload(
                ['تاریخ فاکتور', 'شماره', 'نوع', 'مخاطب', 'جمع (ریال)', 'وضعیت'],
                $rows,
                'report-invoices-'.$fromYmd.'-'.$toYmd.'.csv'
            );
        }

        $viewData = compact('invoices', 'fromLabel', 'toLabel', 'fromYmd', 'toYmd', 'type', 'sumSell', 'sumBuy');

        return $this->isPrint($request)
            ? view('layouts.print', [
                'title' => 'گزارش فاکتورها',
                'slot' => view('reports.business._invoices-inner', $viewData)->render(),
            ])
            : view('reports.business.invoices', $viewData);
    }

    public function balances(Request $request)
    {
        $filter = $request->input('balance_filter', 'nonzero');
        if (! in_array($filter, ['nonzero', 'debit_us', 'credit_us', 'all'], true)) {
            $filter = 'nonzero';
        }

        $query = Contact::query()->orderBy('name');
        if ($filter === 'nonzero') {
            $query->where('balance', '!=', 0);
        } elseif ($filter === 'debit_us') {
            $query->where('balance', '>', 0);
        } elseif ($filter === 'credit_us') {
            $query->where('balance', '<', 0);
        }

        $contacts = $query->get();

        $sumPositive = (int) $contacts->where('balance', '>', 0)->sum('balance');
        $sumNegative = (int) $contacts->where('balance', '<', 0)->sum('balance');

        if ($request->query('export') === 'csv') {
            $rows = [];
            foreach ($contacts as $c) {
                $b = (float) $c->balance;
                $label = $b > 0
                    ? 'ما به مخاطب بدهکار (مانده مثبت)'
                    : ($b < 0 ? 'مخاطب به ما بدهکار (مانده منفی)' : 'تسویه');
                $rows[] = [
                    $c->name,
                    FormatHelper::englishToPersian((string) (int) round($b)),
                    $label,
                ];
            }

            return $this->csvDownload(
                ['مخاطب', 'مانده (ریال)', 'توضیح'],
                $rows,
                'report-balances-'.now()->format('Y-m-d').'.csv'
            );
        }

        $viewData = compact('contacts', 'filter', 'sumPositive', 'sumNegative');

        return $this->isPrint($request)
            ? view('layouts.print', [
                'title' => 'گزارش بدهکار / بستانکار',
                'slot' => view('reports.business._balances-inner', $viewData)->render(),
            ])
            : view('reports.business.balances', $viewData);
    }
}
