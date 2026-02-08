<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function companyIndex()
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'فقط مدیر به تنظیمات شرکت دسترسی دارد.');

        return view('settings.company-index');
    }

    public function companyAddress()
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'فقط مدیر به تنظیمات شرکت دسترسی دارد.');

        $senderAddress = Setting::senderAddress();

        return view('settings.company-address', compact('senderAddress'));
    }

    public function updateCompany(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403, 'فقط مدیر می‌تواند تنظیمات شرکت را تغییر دهد.');

        $request->validate([
            'sender_address' => 'nullable|string|max:1000',
        ]);

        Setting::set('sender_address', $request->input('sender_address') ?: null);

        return redirect()->route('settings.company')->with('success', 'آدرس فرستنده به‌روزرسانی شد.');
    }
}
