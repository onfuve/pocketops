<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function companyIndex()
    {
        return view('settings.company-index');
    }

    public function companyAddress()
    {
        $senderAddress = Setting::senderAddress();

        return view('settings.company-address', compact('senderAddress'));
    }

    public function updateCompany(Request $request)
    {
        $request->validate([
            'sender_address' => 'nullable|string|max:1000',
        ]);

        Setting::set('sender_address', $request->input('sender_address') ?: null);

        return redirect()->route('settings.company')->with('success', 'آدرس فرستنده به‌روزرسانی شد.');
    }
}
