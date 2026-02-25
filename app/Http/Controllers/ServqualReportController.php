<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Lead;
use App\Services\ServqualService;
use Illuminate\Http\Request;

class ServqualReportController extends Controller
{
    public function __construct(private ServqualService $servqualService) {}

    /**
     * Overall SERVQUAL report with company-wide stats and business indicators.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $days = (int) config('servqual.days_for_scoring', 90);

        $stats = $this->servqualService->companyWideStats($days);

        // Business indicators (same visibility as dashboard)
        $contactsCount = Contact::visibleToUser($user)->count();
        $leadsCount = Lead::visibleToUser($user)->count();
        $invoicesSellLast30 = Invoice::visibleToUser($user)
            ->where('type', Invoice::TYPE_SELL)
            ->where('date', '>=', now()->subDays(30))
            ->count();
        $invoicesSellTotal = Invoice::visibleToUser($user)
            ->where('type', Invoice::TYPE_SELL)
            ->count();

        return view('reports.servqual', compact(
            'stats',
            'contactsCount',
            'leadsCount',
            'invoicesSellLast30',
            'invoicesSellTotal',
            'days'
        ));
    }
}
