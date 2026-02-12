@php
use App\Helpers\FormatHelper;
use App\Helpers\FontHelper;
$title = $priceList->title_text ?: $priceList->name;
$primary = $priceList->primary_color ?: '#059669';
$fontCss = FontHelper::cssFor($priceList->font_family ?? 'vazirmatn');
// Social links
$insta = $priceList->social_instagram ? ltrim(trim($priceList->social_instagram), '@') : null;
$telegram = $priceList->social_telegram ? ltrim(trim($priceList->social_telegram), '@') : null;
$whatsapp = null;
if ($priceList->social_whatsapp) {
    $p = preg_replace('/\D/', '', $priceList->social_whatsapp);
    if (str_starts_with($p, '98')) {
        $whatsapp = $p;
    } elseif (str_starts_with($p, '9') && strlen($p) >= 9) {
        $whatsapp = '98' . $p;
    } elseif (str_starts_with($p, '0') && strlen($p) >= 10) {
        $whatsapp = '98' . substr($p, 1);
    } else {
        $whatsapp = $p ? '98' . $p : null;
    }
}
@endphp
@extends('layouts.app-public')

@section('title', $title . ' — ' . config('app.name'))

@push('styles')
<style>
/* Layout: mobile-first, wide-screen friendly */
.pl-public { --pl-primary: {{ $primary }}; --pl-primary-dark: {{ Illuminate\Support\Str::of($primary)->replace('#','')->toString() }}; font-family: {{ $fontCss }}; direction: rtl; }
.pl-public .pl-container { max-width: 64rem; margin: 0 auto; padding: 0 1rem; box-sizing: border-box; width: 100%; }
@media (min-width: 768px) { .pl-public .pl-container { padding: 0 1.5rem; } }
@media (min-width: 1024px) { .pl-public .pl-container { max-width: 72rem; padding: 0 2rem; } }
@media (max-width: 640px) {
  .pl-public .pl-container { padding: 0 0.75rem; }
  .pl-public .pl-header { padding: 1.5rem 0 1rem; }
}

/* Animations */
@keyframes pl-fade-up { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
@keyframes pl-fade-in { from { opacity: 0; } to { opacity: 1; } }
.pl-anim { animation: pl-fade-up 0.5s ease-out forwards; opacity: 0; }
.pl-anim-delay-1 { animation-delay: 0.1s; }
.pl-anim-delay-2 { animation-delay: 0.2s; }
.pl-anim-delay-3 { animation-delay: 0.3s; }

.pl-public .pl-header { text-align: center; padding: 2rem 0 1.5rem; }
.pl-public .pl-title { font-size: 1.75rem; font-weight: 800; margin: 0; color: var(--ds-text); line-height: 1.3; letter-spacing: -0.02em; }
@media (min-width: 768px) { .pl-public .pl-title { font-size: 2.25rem; } }

/* Search bar */
.pl-public .pl-search-wrap { margin: 1.5rem 0 2rem; position: sticky; top: 0.75rem; z-index: 10; }
.pl-public .pl-search { width: 100%; max-width: 100%; padding: 0.875rem 1.25rem; border-radius: 9999px; border: 2px solid var(--ds-border); background: #fff; font-size: 0.9375rem; box-shadow: 0 2px 8px rgba(0,0,0,0.06); transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box; }
.pl-public .pl-search:focus { outline: none; border-color: var(--pl-primary); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.pl-public .pl-search::placeholder { color: var(--ds-text-subtle); }
@media (max-width: 640px) {
  .pl-public .pl-search-wrap { margin: 1rem 0 1.5rem; padding: 0; }
  .pl-public .pl-search { padding: 0.75rem 1rem; font-size: 0.875rem; }
}

/* Section navigation */
.pl-public .pl-nav-wrap { margin: 1.5rem 0; padding: 0.75rem 1rem; background: #fff; border-radius: 0.75rem; border: 1px solid var(--ds-border); position: sticky; top: 0.75rem; z-index: 9; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.pl-public .pl-nav-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ds-text-subtle); margin: 0 0 0.5rem 0; }
.pl-public .pl-nav-links { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.pl-public .pl-nav-link { padding: 0.375rem 0.875rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 600; text-decoration: none; color: var(--ds-text); background: var(--ds-bg-muted); transition: all 0.2s; }
.pl-public .pl-nav-link:hover { background: var(--pl-primary); color: #fff; }
@media (max-width: 640px) {
  .pl-public .pl-nav-wrap { margin: 1rem 0; padding: 0.625rem 0.875rem; }
  .pl-public .pl-nav-links { gap: 0.375rem; }
  .pl-public .pl-nav-link { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
}

/* Sections */
.pl-public .pl-section { margin-bottom: 2.5rem; scroll-margin-top: 1rem; }
.pl-public .pl-section-title { font-size: 1rem; font-weight: 700; margin: 0 0 0.75rem 0; color: var(--ds-text); padding-bottom: 0.5rem; border-bottom: 2px solid var(--pl-primary); display: inline-block; position: relative; }
.pl-public .pl-section-title::after { content: ''; position: absolute; bottom: -2px; right: 0; width: 3rem; height: 2px; background: var(--pl-primary); }
@media (min-width: 768px) { .pl-public .pl-section-title { font-size: 1.125rem; } }

/* Items: simple list */
.pl-public .pl-item { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 1rem 1.25rem; background: #fff; border-radius: 0.875rem; border: 1px solid var(--ds-border); margin-bottom: 0.625rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.25s ease; }
.pl-public .pl-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); border-color: var(--pl-primary); }
.pl-public .pl-item-name { font-weight: 600; font-size: 0.9375rem; line-height: 1.4; }
.pl-public .pl-item-desc { font-size: 0.8125rem; color: var(--ds-text-subtle); margin-top: 0.35rem; line-height: 1.5; }
.pl-public .pl-item-price { font-weight: 700; font-size: 1rem; color: var(--pl-primary); white-space: nowrap; }

/* Items: with_photos */
.pl-public .pl-item-with-photo { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; padding: 1rem 1.25rem; background: #fff; border-radius: 0.875rem; border: 1px solid var(--ds-border); margin-bottom: 0.625rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.25s ease; }
.pl-public .pl-item-with-photo:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); border-color: var(--pl-primary); }
.pl-public .pl-item-photo { width: 4rem; height: 4rem; border-radius: 0.625rem; object-fit: cover; background: var(--ds-bg-muted); flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.08); transition: transform 0.2s; }
.pl-public .pl-item-with-photo:hover .pl-item-photo { transform: scale(1.05); }
@media (min-width: 768px) { .pl-public .pl-item-photo { width: 4.5rem; height: 4.5rem; } }

/* Grid */
.pl-public .pl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(10rem, 1fr)); gap: 1rem; }
@media (min-width: 640px) { .pl-public .pl-grid { grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr)); } }
@media (min-width: 1024px) { .pl-public .pl-grid { grid-template-columns: repeat(auto-fill, minmax(14rem, 1fr)); } }
.pl-public .pl-card { background: #fff; border-radius: 1rem; overflow: hidden; border: 1px solid var(--ds-border); box-shadow: 0 2px 6px rgba(0,0,0,0.06); transition: all 0.3s ease; }
.pl-public .pl-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); border-color: var(--pl-primary); }
.pl-public .pl-card-photo { width: 100%; aspect-ratio: 1; object-fit: cover; background: var(--ds-bg-muted); transition: transform 0.3s; }
.pl-public .pl-card:hover .pl-card-photo { transform: scale(1.05); }
.pl-public .pl-card-body { padding: 1.25rem; }
.pl-public .pl-card-name { font-weight: 600; font-size: 0.9375rem; margin: 0 0 0.35rem 0; line-height: 1.4; }
.pl-public .pl-card-desc { font-size: 0.8125rem; color: var(--ds-text-subtle); margin: 0 0 0.625rem 0; line-height: 1.5; }
.pl-public .pl-card-price { font-weight: 700; font-size: 1rem; color: var(--pl-primary); }

/* Item badges */
.pl-public .pl-badge { display: inline-flex; align-items: center; padding: 0.25rem 0.55rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 800; letter-spacing: 0.02em; text-transform: uppercase; box-shadow: 0 1px 4px rgba(0,0,0,0.2); }
.pl-public .pl-badge--new { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; }
.pl-public .pl-badge--hot { background: linear-gradient(135deg, #06b6d4, #0891b2); color: #fff; }
.pl-public .pl-badge--special_offer { background: linear-gradient(135deg, #10b981, #059669); color: #fff; }
.pl-public .pl-badge--sale { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; }

/* CTA */
.pl-public .pl-cta-wrap { text-align: center; margin: 2rem 0; }
.pl-public .pl-cta { display: inline-flex; align-items: center; justify-content: center; padding: 0.875rem 2rem; min-height: 48px; border-radius: 9999px; font-size: 1rem; font-weight: 700; text-decoration: none; background: var(--pl-primary); color: #fff; border: none; box-shadow: 0 4px 14px rgba(5,150,105,0.4); transition: transform 0.2s, box-shadow 0.2s; }
.pl-public .pl-cta:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(5,150,105,0.45); color: #fff; }

/* Notes */
.pl-public .pl-notes { padding: 1.25rem; background: var(--ds-bg-muted); border-radius: 0.75rem; border-right: 4px solid var(--pl-primary); font-size: 0.875rem; color: var(--ds-text-muted); line-height: 1.6; margin-bottom: 1.5rem; }

/* Footer: social, address, contact */
.pl-public .pl-footer { margin-top: 2.5rem; padding: 2.5rem 1rem 3rem; border-top: 2px solid var(--ds-border); background: #fafaf9; }
@media (min-width: 640px) { .pl-public .pl-footer { padding: 3rem 1.5rem 4rem; } }
@media (min-width: 1024px) { .pl-public .pl-footer { padding: 3.5rem 2rem 4.5rem; } }
.pl-public .pl-footer-inner { max-width: 64rem; margin-left: auto; margin-right: auto; }
.pl-public .pl-footer-grid { display: grid; gap: 1.25rem; }
@media (min-width: 640px) { .pl-public .pl-footer-grid { grid-template-columns: repeat(2, 1fr); gap: 1.5rem; } }
@media (min-width: 768px) { .pl-public .pl-footer-grid { grid-template-columns: repeat(3, 1fr); gap: 1.75rem; } }
.pl-public .pl-footer-block { padding: 1.5rem 1.25rem; background: #fff; border-radius: 0.75rem; border: 1px solid var(--ds-border); text-align: right; }
@media (min-width: 1024px) { .pl-public .pl-footer-block { padding: 1.75rem 1.5rem; } }
.pl-public .pl-footer-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ds-text-subtle); margin: 0 0 0.5rem 0; }
.pl-public .pl-footer-body { font-size: 0.9375rem; color: var(--ds-text); }
.pl-public .pl-social-row { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem; }
.pl-public .pl-social-btn { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 0.5rem; color: #fff; text-decoration: none; transition: transform 0.2s, opacity 0.2s; }
.pl-public .pl-social-btn:hover { transform: scale(1.08); opacity: 0.95; color: #fff; }
.pl-public .pl-social-btn--ig { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
.pl-public .pl-social-btn--tg { background: #0088cc; }
.pl-public .pl-social-btn--wa { background: #25d366; }
.pl-public .pl-contact-link { display: inline-flex; align-items: center; gap: 0.5rem; color: var(--pl-primary); text-decoration: none; font-weight: 600; margin-top: 0.25rem; }
.pl-public .pl-contact-link:hover { text-decoration: underline; color: var(--pl-primary); }

/* Share buttons (share this list) */
.pl-public .pl-share-wrap { margin: 1.5rem 0; padding: 1rem; background: #fff; border-radius: 0.75rem; border: 1px solid var(--ds-border); }
.pl-public .pl-share-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ds-text-subtle); margin: 0 0 0.5rem 0; }
.pl-public .pl-share-row { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
.pl-public .pl-share-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; padding: 0.5rem 1rem; min-height: 44px; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; color: #fff; border: none; cursor: pointer; font-family: inherit; transition: transform 0.2s, opacity 0.2s; }
.pl-public .pl-share-btn:hover { transform: scale(1.03); opacity: 0.95; color: #fff; }
.pl-public .pl-share-btn--copy { background: var(--ds-text-muted); }
.pl-public .pl-share-btn--wa { background: #25d366; }
.pl-public .pl-share-btn--tg { background: #0088cc; }

/* Back to top button */
.pl-public .pl-back-top { position: fixed; bottom: 2rem; left: 2rem; width: 48px; height: 48px; border-radius: 50%; background: var(--pl-primary); color: #fff; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); cursor: pointer; opacity: 0; visibility: hidden; transition: all 0.3s; z-index: 100; display: flex; align-items: center; justify-content: center; }
.pl-public .pl-back-top.visible { opacity: 1; visibility: visible; }
.pl-public .pl-back-top:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(0,0,0,0.2); }

/* Print styles */
@media print {
    .pl-public .pl-search-wrap, .pl-public .pl-nav-wrap, .pl-public .pl-share-wrap, .pl-public .pl-back-top, .pl-public .pl-cta-wrap { display: none !important; }
    .pl-public .pl-section { page-break-inside: avoid; }
    .pl-public .pl-item, .pl-public .pl-card { page-break-inside: avoid; }
}

/* Hidden class for search filter */
.pl-public .pl-hidden { display: none !important; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var copyBtn = document.getElementById('pl-copy-link');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            var url = this.getAttribute('data-url') || '';
            if (url && navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function() {
                    var t = copyBtn.textContent;
                    copyBtn.textContent = 'کپی شد!';
                    setTimeout(function() { copyBtn.textContent = t; }, 1500);
                });
            }
        });
    }

    // Search functionality
    var searchInput = document.getElementById('pl-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var query = this.value.toLowerCase().trim();
            var items = document.querySelectorAll('.pl-item, .pl-item-with-photo, .pl-card');
            var sections = document.querySelectorAll('.pl-section');
            var hasVisible = false;

            items.forEach(function(item) {
                var name = item.getAttribute('data-item-name') || '';
                var desc = item.getAttribute('data-item-desc') || '';
                if (query === '' || name.includes(query) || desc.includes(query)) {
                    item.classList.remove('pl-hidden');
                    hasVisible = true;
                } else {
                    item.classList.add('pl-hidden');
                }
            });

            sections.forEach(function(section) {
                var sectionItems = section.querySelectorAll('.pl-item:not(.pl-hidden), .pl-item-with-photo:not(.pl-hidden), .pl-card:not(.pl-hidden)');
                if (sectionItems.length === 0 && query !== '') {
                    section.style.display = 'none';
                } else {
                    section.style.display = '';
                }
            });
        });
    }

    // Back to top button
    var backTopBtn = document.getElementById('pl-back-top');
    if (backTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backTopBtn.classList.add('visible');
            } else {
                backTopBtn.classList.remove('visible');
            }
        });
        backTopBtn.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Smooth scroll for section navigation
    var navLinks = document.querySelectorAll('.pl-nav-link');
    navLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var targetId = this.getAttribute('href');
            var target = document.querySelector(targetId);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
</script>
@endpush

@section('content')
<div class="pl-public">
    <div class="pl-container">
        <header class="pl-header pl-anim">
            <h1 class="pl-title">{{ $title }}</h1>
        </header>

        @if (($priceList->show_quick_access ?? true) && count($priceList->sections) > 1)
            <div class="pl-nav-wrap pl-anim pl-anim-delay-1">
                <div class="pl-nav-title">دسترسی سریع</div>
                <div class="pl-nav-links">
                    @foreach ($priceList->sections as $section)
                        <a href="#section-{{ $section->id }}" class="pl-nav-link">{{ $section->name }}</a>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($priceList->show_search ?? true)
            <div class="pl-search-wrap pl-anim pl-anim-delay-1">
                <input type="text" id="pl-search" class="pl-search" placeholder="جستجو در لیست قیمت...">
            </div>
        @endif

        @if ($priceList->show_cta && $priceList->cta_url)
            <div class="pl-cta-wrap">
                <a href="{{ $priceList->cta_url }}" class="pl-cta" target="_blank" rel="noopener">{{ $priceList->cta_text ?: 'ثبت سفارش' }}</a>
            </div>
        @endif

        @if ($priceList->show_notes && $priceList->notes_text)
            <div class="pl-notes">{{ nl2br(e($priceList->notes_text)) }}</div>
        @endif

        @if ($priceList->show_share_buttons && $priceList->public_url)
            @php $shareUrl = $priceList->public_url; $shareText = e($title); @endphp
            <div class="pl-share-wrap">
                <div class="pl-share-title">اشتراک‌گذاری این لیست</div>
                <div class="pl-share-row">
                    <button type="button" id="pl-copy-link" class="pl-share-btn pl-share-btn--copy" data-url="{{ $shareUrl }}" aria-label="کپی لینک">کپی لینک</button>
                    <a href="https://wa.me/?text={{ urlencode($shareText . ' ' . $shareUrl) }}" class="pl-share-btn pl-share-btn--wa" target="_blank" rel="noopener" aria-label="اشتراک در واتساپ">واتساپ</a>
                    <a href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareText) }}" class="pl-share-btn pl-share-btn--tg" target="_blank" rel="noopener" aria-label="اشتراک در تلگرام">تلگرام</a>
                </div>
            </div>
        @endif

        @forelse ($priceList->sections as $section)
            <section class="pl-section" id="section-{{ $section->id }}" data-section-name="{{ strtolower($section->name) }}">
                <h2 class="pl-section-title">{{ $section->name }}</h2>

                @if ($priceList->template === 'grid')
                    <div class="pl-grid">
                        @foreach ($section->items as $item)
                            <div class="pl-card" data-item-name="{{ strtolower($item->display_name) }}" data-item-desc="{{ strtolower($item->display_description ?? '') }}">
                                @if ($priceList->show_photos && $item->product?->photo_url)
                                    <img src="{{ $item->product->photo_url }}" alt="" class="pl-card-photo" loading="lazy">
                                @endif
                                <div class="pl-card-body">
                                    <div class="pl-card-name" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                        {{ $item->display_name }}
                                        @if ($item->badge_label)
                                            <span class="pl-badge pl-badge--{{ $item->badge }}">{{ $item->badge_label }}</span>
                                        @endif
                                    </div>
                                    @if ($item->display_description)
                                        <div class="pl-card-desc">{{ Str::limit($item->display_description, 60) }}</div>
                                    @endif
                                    @if ($priceList->show_prices && $item->effective_price !== null)
                                        <div class="pl-card-price">{{ FormatHelper::priceForList($item->effective_price, $priceList->price_format ?? 'rial') }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif ($priceList->template === 'with_photos')
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        @foreach ($section->items as $item)
                            <div class="pl-item-with-photo" data-item-name="{{ strtolower($item->display_name) }}" data-item-desc="{{ strtolower($item->display_description ?? '') }}">
                                @if ($priceList->show_photos && $item->product?->photo_url)
                                    <img src="{{ $item->product->photo_url }}" alt="" class="pl-item-photo" loading="lazy">
                                @endif
                                <div style="flex: 1; min-width: 0;">
                                    <div class="pl-item-name" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                        {{ $item->display_name }}
                                        @if ($item->badge_label)
                                            <span class="pl-badge pl-badge--{{ $item->badge }}">{{ $item->badge_label }}</span>
                                        @endif
                                    </div>
                                    @if ($item->display_description)
                                        <div class="pl-item-desc">{{ Str::limit($item->display_description, 80) }}</div>
                                    @endif
                                </div>
                                @if ($priceList->show_prices && $item->effective_price !== null)
                                    <div class="pl-item-price">{{ FormatHelper::priceForList($item->effective_price, $priceList->price_format ?? 'rial') }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="display: flex; flex-direction: column; gap: 0;">
                        @foreach ($section->items as $item)
                            <div class="pl-item" data-item-name="{{ strtolower($item->display_name) }}" data-item-desc="{{ strtolower($item->display_description ?? '') }}">
                                <div>
                                    <div class="pl-item-name" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                        {{ $item->display_name }}
                                        @if ($item->badge_label)
                                            <span class="pl-badge pl-badge--{{ $item->badge }}">{{ $item->badge_label }}</span>
                                        @endif
                                    </div>
                                    @if ($item->display_description)
                                        <div class="pl-item-desc">{{ Str::limit($item->display_description, 80) }}</div>
                                    @endif
                                </div>
                                @if ($priceList->show_prices && $item->effective_price !== null)
                                    <div class="pl-item-price">{{ FormatHelper::priceForList($item->effective_price, $priceList->price_format ?? 'rial') }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        @empty
            <p style="color: var(--ds-text-subtle); margin: 0; padding: 2rem 0;">فعلاً آیتمی در این لیست نیست.</p>
        @endforelse

        @if ($priceList->show_cta && $priceList->cta_url && ($priceList->show_social || $priceList->show_address || $priceList->show_contact))
            <div class="pl-cta-wrap">
                <a href="{{ $priceList->cta_url }}" class="pl-cta" target="_blank" rel="noopener">{{ $priceList->cta_text ?: 'ثبت سفارش' }}</a>
            </div>
        @endif

        @php $hasFooter = $priceList->show_social && ($insta || $telegram || $whatsapp) || ($priceList->show_address && $priceList->address_text) || ($priceList->show_contact && ($priceList->contact_phone || $priceList->contact_email)); @endphp
        @if ($hasFooter)
            <footer class="pl-footer">
                <div class="pl-footer-inner">
                <div class="pl-footer-grid">
                    @if ($priceList->show_social && ($insta || $telegram || $whatsapp))
                        <div class="pl-footer-block">
                            <div class="pl-footer-title">شبکه‌های اجتماعی</div>
                            <div class="pl-social-row">
                                @if ($insta)
                                    <a href="https://instagram.com/{{ $insta }}" class="pl-social-btn pl-social-btn--ig" target="_blank" rel="noopener" aria-label="اینستاگرام">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                    </a>
                                @endif
                                @if ($telegram)
                                    <a href="https://t.me/{{ $telegram }}" class="pl-social-btn pl-social-btn--tg" target="_blank" rel="noopener" aria-label="تلگرام">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                    </a>
                                @endif
                                @if ($whatsapp)
                                    <a href="https://wa.me/{{ $whatsapp }}" class="pl-social-btn pl-social-btn--wa" target="_blank" rel="noopener" aria-label="واتساپ">
                                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if ($priceList->show_address && $priceList->address_text)
                        <div class="pl-footer-block">
                            <div class="pl-footer-title">آدرس</div>
                            <div class="pl-footer-body">{{ nl2br(e($priceList->address_text)) }}</div>
                        </div>
                    @endif
                    @if ($priceList->show_contact && ($priceList->contact_phone || $priceList->contact_email))
                        <div class="pl-footer-block">
                            <div class="pl-footer-title">تماس با ما</div>
                            <div class="pl-footer-body">
                                @if ($priceList->contact_phone)
                                    <a href="tel:{{ preg_replace('/\D/', '', $priceList->contact_phone) }}" class="pl-contact-link">{{ $priceList->contact_phone }}</a>
                                @endif
                                @if ($priceList->contact_phone && $priceList->contact_email)<br>@endif
                                @if ($priceList->contact_email)
                                    <a href="mailto:{{ $priceList->contact_email }}" class="pl-contact-link">{{ $priceList->contact_email }}</a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                </div>
            </footer>
        @endif

        <button type="button" class="pl-back-top" id="pl-back-top" aria-label="بازگشت به بالا">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
        </button>
    </div>
</div>
@endsection
