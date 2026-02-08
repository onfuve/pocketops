<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index()
    {
        $bankAccounts = BankAccount::query()->orderBy('name')->get();
        return view('bank-accounts.index', compact('bankAccounts'));
    }

    public function create()
    {
        return view('bank-accounts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'shaba' => 'nullable|string|size:26',
            'notes' => 'nullable|string',
        ]);
        BankAccount::create($data);
        return redirect()->route('bank-accounts.index')->with('success', 'حساب بانکی ایجاد شد.');
    }

    public function edit(BankAccount $bankAccount)
    {
        return view('bank-accounts.edit', compact('bankAccount'));
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:100',
            'shaba' => 'nullable|string|size:26',
            'notes' => 'nullable|string',
        ]);
        $bankAccount->update($data);
        return redirect()->route('bank-accounts.index')->with('success', 'حساب بانکی به‌روزرسانی شد.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        if ($bankAccount->invoicePayments()->exists()) {
            return back()->with('error', 'به‌دلیل وجود تراکنش، امکان حذف این حساب نیست.');
        }
        $bankAccount->delete();
        return redirect()->route('bank-accounts.index')->with('success', 'حساب بانکی حذف شد.');
    }
}
