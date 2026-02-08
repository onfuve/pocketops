<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>برچسب آدرس — {{ $contact->name }}</title>
    <link href="{{ asset('vendor/fonts/vazirmatn/vazirmatn.css') }}" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Vazirmatn', sans-serif; font-size: 14px; line-height: 1.6; color: #1c1917; background: #f5f5f4; margin: 0; padding: 24px; }
        .no-print { margin-bottom: 20px; padding: 16px; background: #fff; border-radius: 0.75rem; border: 2px solid #e7e5e4; display: flex; flex-wrap: wrap; align-items: center; gap: 16px; }
        @media print { .no-print { display: none !important; } body { padding: 0; background: #fff; } }
        .label-sheet { max-width: 380px; margin: 0 auto; padding: 24px; background: #fff; border: 2px dashed #d6d3d1; border-radius: 0.75rem; min-height: 180px; }
        @media print { .label-sheet { border: none; box-shadow: none; max-width: 100%; padding: 16px; } }
        .label-row { margin-bottom: 16px; }
        .label-row:last-child { margin-bottom: 0; }
        .label-label { font-size: 11px; font-weight: 600; color: #78716c; margin-bottom: 4px; letter-spacing: 0.02em; }
        .label-value { font-size: 16px; font-weight: 600; color: #1c1917; white-space: pre-wrap; }
        .label-value.sender { font-size: 14px; font-weight: 500; color: #57534e; }
        .label-divider { border-top: 1px solid #e7e5e4; margin: 12px 0; }
        .btn-print { padding: 10px 20px; background: #059669; color: #fff; border: none; border-radius: 0.5rem; font-family: Vazirmatn; font-size: 0.9375rem; font-weight: 600; cursor: pointer; }
        .btn-print:hover { background: #047857; }
        .btn-back { padding: 10px 20px; background: #e7e5e4; color: #292524; border-radius: 0.5rem; text-decoration: none; font-family: Vazirmatn; font-size: 0.9375rem; }
        .btn-back:hover { background: #d6d3d1; }
        .checkbox-wrap { display: flex; align-items: center; gap: 8px; }
        .checkbox-wrap input { width: 18px; height: 18px; }
        .checkbox-wrap label { cursor: pointer; font-size: 0.9375rem; }
        .empty-address { color: #a8a29e; font-style: italic; }
    </style>
</head>
<body>
    <div class="no-print">
        <form action="{{ route('contacts.address-label', $contact) }}" method="get" style="display: contents;">
            <label class="checkbox-wrap">
                <input type="checkbox" name="include_sender" value="1" {{ $includeSender ? 'checked' : '' }} onchange="this.form.submit()">
                <span>آدرس فرستنده را هم نمایش بده</span>
            </label>
            <button type="button" onclick="window.print()" class="btn-print">چاپ برچسب</button>
        </form>
        <a href="{{ route('contacts.show', $contact) }}" class="btn-back">بازگشت به مخاطب</a>
    </div>

    <div class="label-sheet">
        {{-- آدرس گیرنده (مخاطب) --}}
        <div class="label-row">
            <div class="label-label">آدرس گیرنده</div>
            <div class="label-value">
                @if ($contact->address || $contact->city || $contact->name)
                    {{ $contact->name }}
                    @if ($contact->address)
                        {{ "\n" . $contact->address }}
                    @endif
                    @if ($contact->city)
                        {{ "\n" . $contact->city }}
                    @endif
                @else
                    <span class="empty-address">آدرس ثبت نشده</span>
                @endif
            </div>
        </div>

        @if ($includeSender && $senderAddress)
            <div class="label-divider"></div>
            <div class="label-row">
                <div class="label-label">آدرس فرستنده</div>
                <div class="label-value sender">{{ $senderAddress }}</div>
            </div>
        @endif
    </div>
</body>
</html>
