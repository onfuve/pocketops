<?php

namespace App\Http\Controllers;

use App\Models\PaymentOption;
use Illuminate\Http\Request;

class PaymentOptionController extends Controller
{
    public function index()
    {
        $options = PaymentOption::orderBy('sort')->get();

        return view('settings.payment-options', compact('options'));
    }

    public function edit(PaymentOption $payment_option)
    {
        return view('settings.payment-options-edit', ['option' => $payment_option]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePaymentOption($request);
        $validated['sort'] = PaymentOption::max('sort') + 1;
        $validated['type'] = $validated['type'] ?? 'card';
        
        // Ensure value is set (required column) - use first available field if value is empty
        if (($validated['value'] ?? '') === '' || $validated['value'] === null) {
            $validated['value'] = $validated['card_number'] ?? $validated['iban'] ?? $validated['account_number'] ?? '';
        }
        // If still empty, set a default (shouldn't happen due to validation, but safety)
        if ($validated['value'] === '' || $validated['value'] === null) {
            $validated['value'] = $validated['label'] ?? 'N/A';
        }

        PaymentOption::create($validated);

        return redirect()->route('settings.payment-options')->with('success', 'اطلاعات پرداخت اضافه شد.');
    }

    public function update(Request $request, PaymentOption $payment_option)
    {
        $validated = $this->validatePaymentOption($request, $payment_option);
        
        // Ensure value is set (required column) - use first available field if value is empty
        if (($validated['value'] ?? '') === '' || $validated['value'] === null) {
            $validated['value'] = $validated['card_number'] ?? $validated['iban'] ?? $validated['account_number'] ?? '';
        }
        // If still empty, keep existing value
        if ($validated['value'] === '' || $validated['value'] === null) {
            $validated['value'] = $payment_option->value;
        }

        $payment_option->update($validated);

        return redirect()->route('settings.payment-options')->with('success', 'به‌روزرسانی شد.');
    }

    public function destroy(PaymentOption $payment_option)
    {
        $payment_option->delete();

        return redirect()->route('settings.payment-options')->with('success', 'حذف شد.');
    }

    private function validatePaymentOption(Request $request, ?PaymentOption $option = null): array
    {
        $rules = [
            'label' => 'required|string|max:100',
            'holder_name' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'value' => 'nullable|string|max:100',
            'type' => 'nullable|in:card,account',
            'card_number' => 'nullable|string|max:50',
            'iban' => 'nullable|string|max:34',
            'account_number' => 'nullable|string|max:50',
            'print_card_number' => 'nullable|boolean',
            'print_iban' => 'nullable|boolean',
            'print_account_number' => 'nullable|boolean',
        ];
        $data = $request->validate($rules);

        $normalize = function ($v) {
            return $v === null || $v === '' ? null : \App\Helpers\FormatHelper::persianToEnglish($v);
        };
        $data['holder_name'] = trim($data['holder_name'] ?? '');
        $data['bank_name'] = trim($data['bank_name'] ?? '');
        $data['value'] = $normalize($data['value'] ?? null);
        $data['card_number'] = $normalize($data['card_number'] ?? null);
        $data['iban'] = $normalize($data['iban'] ?? null);
        $data['account_number'] = $normalize($data['account_number'] ?? null);
        $data['print_card_number'] = $request->boolean('print_card_number');
        $data['print_iban'] = $request->boolean('print_iban');
        $data['print_account_number'] = $request->boolean('print_account_number');

        $hasAny = ($data['value'] !== null && $data['value'] !== '')
            || ($data['card_number'] ?? null)
            || ($data['iban'] ?? null)
            || ($data['account_number'] ?? null);
        if (!$hasAny) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'value' => ['حداقل یکی از شماره کارت، شبا یا شماره حساب را وارد کنید.'],
            ]);
        }

        return $data;
    }
}
