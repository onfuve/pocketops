<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\InvoicePayment;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /** Date-based: all payments (transactions) ordered by date */
    public function byDate(Request $request)
    {
        $user = $request->user();
        $query = InvoicePayment::with(['invoice.contact', 'bankAccount', 'contact'])
            ->whereHas('invoice', fn ($q) => $q->visibleToUser($user))
            ->orderByDesc('paid_at')->orderByDesc('id');
        if ($request->filled('from')) {
            $query->whereDate('paid_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('paid_at', '<=', $request->to);
        }
        $transactions = $query->paginate(30)->withQueryString();
        return view('transactions.by-date', compact('transactions'));
    }

    /** Contact-based: list contacts with balance and their transactions */
    public function byContact(Request $request)
    {
        $query = Contact::query()
            ->visibleToUser($request->user())
            ->withCount(['invoices', 'invoicePaymentsAsCounterparty'])
            ->orderBy('name');
        if ($request->filled('has_balance')) {
            if ($request->has_balance === 'yes') {
                $query->where('balance', '!=', 0);
            } elseif ($request->has_balance === 'zero') {
                $query->where('balance', 0);
            }
        }
        $contacts = $query->get();
        return view('transactions.by-contact', compact('contacts'));
    }

    /** Single contact's transaction history */
    public function contactTransactions(Contact $contact)
    {
        abort_unless($contact->isVisibleTo(request()->user()), 403, 'شما به این مخاطب دسترسی ندارید.');

        $contact->load(['invoices.payments', 'invoicePaymentsAsCounterparty.invoice']);
        $paymentsAsCounterparty = $contact->invoicePaymentsAsCounterparty()
            ->with('invoice.contact')
            ->orderByDesc('paid_at')
            ->get();
        $invoices = $contact->invoices()->with('payments')->orderByDesc('date')->get();
        return view('transactions.contact-detail', compact('contact', 'paymentsAsCounterparty', 'invoices'));
    }
}
