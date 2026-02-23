<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    /** مهر / امضای فاکتور — upload transparent image for invoice print */
    public function companyStamp()
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'فقط مدیر به تنظیمات شرکت دسترسی دارد.');

        $stampPath = Setting::get('company_stamp_path');
        $stampUrl = $stampPath ? Storage::disk('public')->url($stampPath) : null;

        return view('settings.company-stamp', compact('stampUrl', 'stampPath'));
    }

    public function updateCompanyStamp(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403, 'فقط مدیر می‌تواند تنظیمات شرکت را تغییر دهد.');

        $request->validate([
            'stamp' => 'required|image|mimes:png,jpeg,jpg,gif,webp|max:2048',
        ]);

        $disk = Storage::disk('public');
        $oldPath = Setting::get('company_stamp_path');
        if ($oldPath && $disk->exists($oldPath)) {
            $disk->delete($oldPath);
        }

        $path = $request->file('stamp')->store('company', 'public');
        Setting::set('company_stamp_path', $path);

        return redirect()->route('settings.company.stamp')->with('success', 'مهر / امضا ذخیره شد و در چاپ فاکتور نمایش داده می‌شود.');
    }

    public function removeCompanyStamp(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403, 'فقط مدیر می‌تواند تنظیمات شرکت را تغییر دهد.');

        $path = Setting::get('company_stamp_path');
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        Setting::set('company_stamp_path', null);

        return redirect()->route('settings.company.stamp')->with('success', 'مهر / امضا حذف شد.');
    }
}
