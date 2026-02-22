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
        /* A5: 148mm × 210mm — optimized for print */
        @page { size: A5; margin: 8mm; }
        @media print {
            html, body { width: 148mm; min-height: 210mm; margin: 0; padding: 0; background: #fff; }
            .no-print { display: none !important; }
            .invoice { width: 100%; max-width: none; }
        }
        body { font-family: 'Vazirmatn', 'DejaVu Sans', sans-serif; font-size: 11px; line-height: 1.45; color: #1c1917; background: #fff; margin: 0; padding: 12px; max-width: 148mm; min-height: 210mm; }
        .no-print { margin-bottom: 12px; }
        .invoice { max-width: 132mm; margin: 0 auto; }
        .top-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 2px solid #1c1917; }
        .top-row .right { text-align: right; }
        .top-row .left { text-align: left; }
        .customer-name { font-size: 13px; font-weight: 700; color: #1c1917; margin: 0; }
        .inv-meta { font-size: 10px; color: #44403c; margin-top: 2px; }
        .inv-meta strong { color: #1c1917; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; border: 1px solid #1c1917; }
        th, td { padding: 5px 6px; text-align: right; border: 1px solid #78716c; }
        th { background: #292524; color: #fff; font-size: 9px; font-weight: 700; }
        td { font-size: 10px; color: #1c1917; }
        tr:nth-child(even) td { background: #fafaf9; }
        .totals { border: 1px solid #1c1917; padding: 8px 10px; margin-top: 4px; background: #f5f5f4; }
        .totals dl { max-width: 100%; margin: 0; }
        .totals dt, .totals dd { display: inline-block; margin: 0; padding: 2px 0; font-size: 10px; }
        .totals dt { width: 58%; color: #44403c; font-weight: 600; }
        .totals dd { width: 40%; font-weight: 700; text-align: left; color: #1c1917; }
        .totals .total-row { border-top: 1px solid #44403c; margin-top: 6px; padding-top: 8px; font-size: 12px; font-weight: 700; }
        .payment-section { margin-top: 10px; padding: 6px 8px; border: 1px solid #d6d3d1; background: #fafaf9; border-radius: 4px; }
        .payment-section h3 { margin: 0 0 4px; font-size: 9px; font-weight: 600; color: #78716c; border-bottom: 1px solid #e7e5e4; padding-bottom: 4px; }
        .payment-list { list-style: none; padding: 0; margin: 0; }
        .payment-list li { padding: 2px 0; border-bottom: 1px solid #e7e5e4; display: flex; justify-content: space-between; gap: 8px; font-size: 8px; color: #78716c; }
        .payment-list li:last-child { border-bottom: 0; }
        .payment-list .label { font-weight: 600; color: #78716c; }
        .payment-list .value { font-family: 'Vazirmatn', monospace; direction: ltr; text-align: left; letter-spacing: 0.02em; color: #57534e; font-size: 8px; }
        .notes { margin-top: 10px; padding: 8px; border: 1px solid #78716c; font-size: 9px; color: #44403c; }
        .signature-section { margin-top: 12px; padding-top: 10px; border-top: 1px solid #78716c; display: flex; justify-content: flex-end; }
        .signature-field { display: flex; align-items: center; gap: 10px; }
        .signature-field .label { font-size: 10px; font-weight: 600; color: #44403c; white-space: nowrap; }
        .signature-field .box { width: 80px; height: 36px; border: 1px solid #78716c; border-radius: 3px; background: #fafaf9; }
    </style>
</head>
<body>
    @if (empty($public))
    <div class="no-print">
        <button type="button" onclick="window.print()" style="padding: 10px 20px; background: #292524; color: #fff; border: 0; border-radius: 8px; font-family: Vazirmatn; cursor: pointer;">چاپ</button>
        <a href="{{ route('invoices.show', $invoice) }}" style="margin-right: 8px; padding: 10px 20px; background: #e7e5e4; color: #292524; border-radius: 8px; text-decoration: none; font-family: Vazirmatn;">بستن</a>
    </div>
    @endif

    @php $isBuy = $invoice->type === 'buy'; @endphp
    <div class="invoice">
        <h2 class="invoice-type-title" style="margin: 0 0 8px; font-size: 14px; font-weight: 700; text-align: center; {{ $isBuy ? 'color: #0369a1;' : 'color: #1c1917;' }}">
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
                @if ($invoice->effectiveDiscount() > 0)
                    <div>
                        <dt>تخفیف</dt>
                        <dd>−{{ FormatHelper::numberFormat($invoice->effectiveDiscount()) }} ریال @if($invoice->discount_percent)({{ FormatHelper::numberFormat($invoice->discount_percent) }}٪)@endif</dd>
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
