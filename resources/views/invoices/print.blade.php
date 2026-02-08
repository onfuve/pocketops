@php
    use App\Helpers\FormatHelper;
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاکتور {{ $invoice->invoice_number ?: $invoice->id }}</title>
    <link href="{{ asset('vendor/fonts/vazirmatn/vazirmatn.css') }}" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        /* DejaVu Sans is built into DomPDF and supports Persian - used as fallback if Vazirmatn fails */
        body { font-family: 'Vazirmatn', 'DejaVu Sans', sans-serif; font-size: 14px; line-height: 1.6; color: #1c1917; background: #fff; margin: 0; padding: 24px; }
        .no-print { margin-bottom: 16px; }
        @media print { .no-print { display: none !important; } body { padding: 16px; } }
        .invoice { max-width: 720px; margin: 0 auto; }
        .top-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 3px solid #1c1917; }
        .top-row .right { text-align: right; }
        .top-row .left { text-align: left; }
        .customer-name { font-size: 18px; font-weight: 700; color: #1c1917; margin: 0; }
        .inv-meta { font-size: 13px; color: #44403c; margin-top: 4px; }
        .inv-meta strong { color: #1c1917; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; border: 2px solid #1c1917; }
        th, td { padding: 12px 14px; text-align: right; border: 1px solid #44403c; }
        th { background: #292524; color: #fff; font-size: 12px; font-weight: 700; }
        td { font-size: 13px; color: #1c1917; border-color: #78716c; }
        tr:nth-child(even) td { background: #fafaf9; }
        .totals { border: 2px solid #1c1917; padding: 16px 20px; margin-top: 8px; background: #f5f5f4; }
        .totals dl { max-width: 340px; margin: 0 auto 0 0; }
        .totals dt, .totals dd { display: inline-block; margin: 0; padding: 6px 0; font-size: 14px; }
        .totals dt { width: 58%; color: #44403c; font-weight: 600; }
        .totals dd { width: 40%; font-weight: 700; text-align: left; color: #1c1917; }
        .totals .total-row { border-top: 2px solid #44403c; margin-top: 10px; padding-top: 14px; font-size: 17px; font-weight: 700; }
        .payment-section { margin-top: 20px; padding: 10px 14px; border: 1px solid #d6d3d1; background: #fafaf9; border-radius: 6px; }
        .payment-section h3 { margin: 0 0 8px; font-size: 11px; font-weight: 600; color: #78716c; border-bottom: 1px solid #e7e5e4; padding-bottom: 6px; letter-spacing: 0.02em; }
        .payment-list { list-style: none; padding: 0; margin: 0; }
        .payment-list li { padding: 4px 0; border-bottom: 1px solid #e7e5e4; display: flex; justify-content: space-between; gap: 12px; font-size: 10px; color: #78716c; }
        .payment-list li:last-child { border-bottom: 0; }
        .payment-list .label { font-weight: 600; color: #78716c; }
        .payment-list .value { font-family: 'Vazirmatn', monospace; direction: ltr; text-align: left; letter-spacing: 0.02em; color: #57534e; font-size: 10px; }
        .notes { margin-top: 20px; padding: 14px; border: 1px solid #78716c; font-size: 12px; color: #44403c; }
        .signature-section { margin-top: 28px; padding-top: 20px; border-top: 2px solid #78716c; display: flex; justify-content: flex-end; }
        .signature-field { display: flex; align-items: center; gap: 16px; }
        .signature-field .label { font-size: 13px; font-weight: 600; color: #44403c; white-space: nowrap; }
        .signature-field .box { width: 200px; height: 72px; border: 1px solid #78716c; border-radius: 4px; background: #fafaf9; }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" onclick="window.print()" style="padding: 10px 20px; background: #292524; color: #fff; border: 0; border-radius: 8px; font-family: Vazirmatn; cursor: pointer;">چاپ</button>
        <a href="{{ route('invoices.show', $invoice) }}" style="margin-right: 8px; padding: 10px 20px; background: #e7e5e4; color: #292524; border-radius: 8px; text-decoration: none; font-family: Vazirmatn;">بستن</a>
    </div>

    @php $isBuy = $invoice->type === 'buy'; @endphp
    <div class="invoice">
        <h2 class="invoice-type-title" style="margin: 0 0 20px; font-size: 20px; font-weight: 700; text-align: center; {{ $isBuy ? 'color: #0369a1;' : 'color: #1c1917;' }}">
            {{ $isBuy ? 'رسید خرید' : 'فاکتور فروش' }}
        </h2>
        <div class="top-row">
            <div class="right">
                <p class="customer-name" style="font-size: 12px; color: #78716c; margin-bottom: 2px;">{{ $isBuy ? 'فروشنده' : 'مشتری' }}</p>
                <p class="customer-name">{{ $invoice->contact->name }}</p>
            </div>
            <div class="left">
                <div class="inv-meta">{{ $isBuy ? 'شماره رسید' : 'شماره فاکتور' }}: <strong>{{ $invoice->invoice_number ?: $invoice->id }}</strong></div>
                <div class="inv-meta">تاریخ: <strong>{{ FormatHelper::shamsi($invoice->date) }}</strong></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ردیف</th>
                    <th>شرح</th>
                    <th>تعداد</th>
                    <th>قیمت واحد (ریال)</th>
                    <th>مبلغ (ریال)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $i => $item)
                    <tr>
                        <td>{{ FormatHelper::englishToPersian((string)($i + 1)) }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ FormatHelper::numberFormat($item->quantity) }}</td>
                        <td>{{ FormatHelper::numberFormat($item->unit_price) }}</td>
                        <td>{{ FormatHelper::numberFormat($item->amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <dl>
                <div>
                    <dt>جمع کل (ریال)</dt>
                    <dd>{{ FormatHelper::rial($invoice->subtotal) }}</dd>
                </div>
                @if ($invoice->discount > 0)
                    <div>
                        <dt>تخفیف (ریال)</dt>
                        <dd>−{{ FormatHelper::numberFormat($invoice->discount) }} ریال</dd>
                    </div>
                @endif
                <div class="total-row">
                    <dt>مبلغ قابل پرداخت (ریال)</dt>
                    <dd>{{ FormatHelper::rial($invoice->total) }}</dd>
                </div>
            </dl>
        </div>

        @if ($invoice->type === 'sell')
            @php $paymentLines = $invoice->paymentLinesForPrint(); @endphp
            @if (count($paymentLines) > 0)
                <div class="payment-section">
                    <h3>اطلاعات واریز</h3>
                    <ul class="payment-list">
                        @foreach ($paymentLines as $line)
                            <li>
                                <span class="label">{{ $line['label'] }}</span>
                                <span class="value">{{ FormatHelper::englishToPersian($line['value']) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif

        @if ($invoice->notes)
            <div class="notes">{{ $invoice->notes }}</div>
        @endif

        @if ($invoice->type === 'sell')
            <div class="signature-section">
                <div class="signature-field">
                    <span class="label">نام و امضا:</span>
                    <span class="box"></span>
                </div>
            </div>
        @endif
    </div>

</body>
</html>
