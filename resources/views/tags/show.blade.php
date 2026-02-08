@php use App\Helpers\FormatHelper; use App\Models\Lead; @endphp
@extends('layouts.app')

@section('title', 'برچسب: ' . $tag->name . ' — ' . config('app.name'))

@section('content')
<div style="max-width: 52rem; margin: 0 auto; padding: 0 1rem; box-sizing: border-box;">
    <div style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <a href="{{ route('tags.index') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #78716c; font-size: 0.875rem; text-decoration: none;">@include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4']) لیست برچسب‌ها</a>
            <span style="display: inline-flex; align-items: center; gap: 0.75rem; padding: 0.5rem 1rem; border-radius: 0.75rem; background: {{ $tag->color }}20; border: 2px solid {{ $tag->color }}40;">
                <span style="display: inline-block; width: 1.25rem; height: 1.25rem; border-radius: 0.375rem; background: {{ $tag->color }};"></span>
                <span style="font-size: 1.25rem; font-weight: 700; color: #292524;">{{ $tag->name }}</span>
            </span>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('tags.edit', $tag) }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none;">@include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4']) ویرایش</a>
        </div>
    </div>

    @if ($tag->leads->isEmpty() && $tag->contacts->isEmpty() && $tag->invoices->isEmpty())
        <div style="padding: 2rem; text-align: center; background: #fff; border: 2px solid #e7e5e4; border-radius: 1rem;">
            <p style="margin: 0; color: #78716c;">هیچ مخاطب، سرنخ یا فاکتوری با این برچسب ثبت نشده است.</p>
        </div>
    @else
        {{-- مخاطبین --}}
        @if ($tag->contacts->isNotEmpty())
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; color: #292524; margin: 0 0 0.75rem 0; display: flex; align-items: center; gap: 0.5rem;">
                    @include('components._icons', ['name' => 'users', 'class' => 'w-5 h-5'])
                    مخاطبین ({{ $tag->contacts->count() }})
                </h2>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    @foreach ($tag->contacts as $contact)
                        <li style="margin-bottom: 0.5rem; border-radius: 0.75rem; border: 1px solid #e7e5e4; background: #fff; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                            <a href="{{ route('contacts.show', $contact) }}" style="display: flex; align-items: center; justify-content: space-between; text-decoration: none; color: inherit;">
                                <span style="font-weight: 600; color: #292524;">{{ $contact->name }}</span>
                                <span style="color: #d6d3d1;">@include('components._icons', ['name' => 'arrow-left', 'class' => 'w-5 h-5'])</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- سرنخ‌ها --}}
        @if ($tag->leads->isNotEmpty())
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; color: #292524; margin: 0 0 0.75rem 0; display: flex; align-items: center; gap: 0.5rem;">
                    @include('components._icons', ['name' => 'lightbulb', 'class' => 'w-5 h-5'])
                    سرنخ‌ها ({{ $tag->leads->count() }})
                </h2>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    @foreach ($tag->leads as $lead)
                        <li style="margin-bottom: 0.5rem; border-radius: 0.75rem; border: 1px solid #e7e5e4; background: #fff; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                            <a href="{{ route('leads.show', $lead) }}" style="display: flex; align-items: center; justify-content: space-between; text-decoration: none; color: inherit;">
                                <div>
                                    <span style="font-weight: 600; color: #292524;">{{ $lead->name }}</span>
                                    @if ($lead->company)
                                        <span style="font-size: 0.875rem; color: #78716c;"> · {{ $lead->company }}</span>
                                    @endif
                                    <span class="rounded-full border px-2.5 py-0.5 text-xs font-medium ml-2 {{ Lead::statusColor($lead->status) }}" style="padding: 0.125rem 0.5rem; border-radius: 9999px;">{{ $lead->status_label }}</span>
                                </div>
                                <span style="color: #d6d3d1;">@include('components._icons', ['name' => 'arrow-left', 'class' => 'w-5 h-5'])</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- فاکتورها --}}
        @if ($tag->invoices->isNotEmpty())
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1rem; font-weight: 600; color: #292524; margin: 0 0 0.75rem 0; display: flex; align-items: center; gap: 0.5rem;">
                    @include('components._icons', ['name' => 'document', 'class' => 'w-5 h-5'])
                    فاکتورها ({{ $tag->invoices->count() }})
                </h2>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    @foreach ($tag->invoices as $invoice)
                        <li style="margin-bottom: 0.5rem; border-radius: 0.75rem; border: 1px solid #e7e5e4; background: #fff; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                            <a href="{{ route('invoices.show', $invoice) }}" style="display: flex; align-items: center; justify-content: space-between; text-decoration: none; color: inherit;">
                                <div>
                                    <span style="font-weight: 600; color: #292524;">فاکتور {{ $invoice->type === \App\Models\Invoice::TYPE_SELL ? 'فروش' : 'خرید' }} #{{ $invoice->invoice_number ?: $invoice->id }}</span>
                                    <span style="font-size: 0.875rem; color: #78716c;"> · {{ $invoice->contact->name ?? '' }} · {{ FormatHelper::shamsi($invoice->date) }}</span>
                                    <span style="font-size: 0.875rem; font-weight: 600; color: #292524;"> · {{ FormatHelper::rial($invoice->total) }}</span>
                                </div>
                                <span style="color: #d6d3d1;">@include('components._icons', ['name' => 'arrow-left', 'class' => 'w-5 h-5'])</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif
</div>
@endsection
