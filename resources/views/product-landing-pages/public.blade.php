@php
use App\Helpers\FormatHelper;
use App\Helpers\FontHelper;
$product = $page->product;
$headline = $page->display_headline;
$subheadline = $page->subheadline;
$price = $page->price !== null ? (int) $page->price : ($product?->default_unit_price ? (int) $product->default_unit_price : null);
$priceFormat = $page->price_format ?? 'rial';
$ctaHref = $page->cta_href;
$ctaText = $page->cta_button_text ?: 'سفارش دهید';
$primary = $page->primary_color ?: '#7c3aed';
$template = $page->template ?: 'hero';
$insta = $page->social_instagram ? ltrim(trim($page->social_instagram), '@') : null;
$telegram = $page->social_telegram ? ltrim(trim($page->social_telegram), '@') : null;
$whatsapp = null;
if ($page->social_whatsapp) {
    $p = preg_replace('/\D/', '', $page->social_whatsapp);
    $whatsapp = str_starts_with($p, '98') ? $p : (str_starts_with($p, '0') && strlen($p) >= 10 ? '98' . substr($p, 1) : ($p ? '98' . $p : null));
}
$hasFooter = ($page->show_social && ($insta || $telegram || $whatsapp)) || ($page->show_address && $page->address_text) || ($page->show_contact && ($page->contact_phone || $page->contact_email));
$mainPhotoUrl = $page->main_photo_url;
$displayPhotoUrls = $page->display_photo_urls;
$fontCss = FontHelper::cssFor($page->font_family ?? 'vazirmatn');
@endphp
@extends('layouts.app-public')

@section('title', $headline . ' — ' . config('app.name'))

@push('styles')
<style>
.plp { --plp-primary: {{ $primary }}; font-family: {{ $fontCss }}; direction: rtl; }
.plp * { box-sizing: border-box; }
/* Animations */
@keyframes plp-fade-up { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes plp-fade-in { from { opacity: 0; } to { opacity: 1; } }
@keyframes plp-scale-in { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
.plp-anim { animation: plp-fade-up 0.6s ease-out forwards; }
.plp-anim-delay-1 { animation-delay: 0.1s; opacity: 0; }
.plp-anim-delay-2 { animation-delay: 0.2s; opacity: 0; }
.plp-anim-delay-3 { animation-delay: 0.3s; opacity: 0; }
.plp-anim-delay-4 { animation-delay: 0.4s; opacity: 0; }

/* CTA button */
.plp-cta { display: inline-flex; align-items: center; justify-content: center; padding: 1rem 2.5rem; min-height: 56px; border-radius: 9999px; font-size: 1.125rem; font-weight: 800; text-decoration: none; color: #fff; background: var(--plp-primary); box-shadow: 0 4px 20px rgba(0,0,0,0.15); transition: transform 0.2s, box-shadow 0.2s; }
.plp-cta:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.2); color: #fff; }

/* Hero template */
.plp-hero { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1.25rem 3rem; background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 50%, #f5f5f4 100%); }
.plp-hero-inner { max-width: 42rem; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; }
@media (min-width: 1024px) {
    .plp-hero { padding: 3rem 2rem 4rem; }
    .plp-hero-inner { max-width: 64rem; margin-left: auto; margin-right: auto; flex-direction: row; gap: 3rem; text-align: right; align-items: center; justify-content: center; }
    .plp-hero-visual { flex: 1 1 0; min-width: 0; display: flex; justify-content: flex-start; align-items: center; }
    .plp-hero-content { flex: 1 1 0; min-width: 0; display: flex; flex-direction: column; align-items: flex-end; }
}
.plp-hero-img { width: 100%; max-width: 20rem; aspect-ratio: 1; object-fit: cover; border-radius: 1.25rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.2); margin: 0 0 2rem; display: block; animation: plp-scale-in 0.7s ease-out; }
@media (min-width: 1024px) { .plp-hero-img { margin: 0; max-width: 22rem; } }
.plp-hero-title { font-size: 1.75rem; font-weight: 900; color: var(--ds-text); margin: 0 0 0.5rem; line-height: 1.3; letter-spacing: -0.02em; max-width: 100%; }
@media (min-width: 768px) { .plp-hero-title { font-size: 2.5rem; } }
.plp-hero-sub { font-size: 1.0625rem; color: var(--ds-text-subtle); margin: 0 0 1.25rem; line-height: 1.5; }
.plp-hero-price { font-size: 1.5rem; font-weight: 800; color: var(--plp-primary); margin: 0 0 1.5rem; }
.plp-hero-cta { margin-top: 0.25rem; }

/* Minimal template */
.plp-minimal { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2.5rem 1.25rem 3rem; background: #fafaf9; }
.plp-minimal-inner { max-width: 34rem; width: 100%; text-align: center; display: flex; flex-direction: column; align-items: center; }
@media (min-width: 1024px) {
    .plp-minimal { padding: 3rem 2rem 4rem; }
    .plp-minimal-inner { max-width: 56rem; margin-left: auto; margin-right: auto; flex-direction: row; gap: 2.5rem; text-align: right; align-items: center; justify-content: center; }
    .plp-minimal-visual { flex: 1 1 0; min-width: 0; display: flex; justify-content: flex-start; align-items: center; }
    .plp-minimal-content { flex: 1 1 0; min-width: 0; display: flex; flex-direction: column; align-items: flex-end; }
}
.plp-minimal-img { width: 100%; max-width: 14rem; aspect-ratio: 1; object-fit: cover; border-radius: 1rem; margin: 0 0 1.75rem; display: block; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
@media (min-width: 1024px) { .plp-minimal-img { margin: 0; max-width: 16rem; } }
.plp-minimal-title { font-size: 1.625rem; font-weight: 800; color: var(--ds-text); margin: 0 0 0.5rem; line-height: 1.3; }
.plp-minimal-sub { font-size: 1rem; color: var(--ds-text-subtle); margin: 0 0 1rem; line-height: 1.5; }
.plp-minimal-price { font-size: 1.25rem; font-weight: 700; color: var(--plp-primary); margin: 0 0 1.25rem; }
.plp-minimal-desc { font-size: 0.9375rem; color: var(--ds-text-muted); line-height: 1.7; margin: 0 0 1.5rem; max-width: 32rem; }
.plp-minimal-cta { }

/* Card template */
.plp-card { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1.25rem 3rem; background: linear-gradient(180deg, #e0e7ff 0%, #f5f5f4 50%); }
.plp-card-box { max-width: 26rem; width: 100%; background: #fff; border-radius: 1.25rem; overflow: hidden; box-shadow: 0 20px 40px -15px rgba(0,0,0,0.2); animation: plp-scale-in 0.6s ease-out; }
@media (min-width: 1024px) {
    .plp-card-box { max-width: 40rem; display: grid; grid-template-columns: 1fr 1fr; }
    .plp-card-box > .plp-card-body { padding: 2.5rem; }
}
.plp-card-img { width: 100%; aspect-ratio: 1; object-fit: cover; display: block; }
.plp-card-body { padding: 2rem 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; }
.plp-card-title { font-size: 1.375rem; font-weight: 800; color: var(--ds-text); margin: 0 0 0.5rem; line-height: 1.3; }
.plp-card-sub { font-size: 0.9375rem; color: var(--ds-text-subtle); margin: 0 0 0.75rem; line-height: 1.5; }
.plp-card-price { font-size: 1.3125rem; font-weight: 800; color: var(--plp-primary); margin: 0 0 1.25rem; }
.plp-card-desc { font-size: 0.875rem; color: var(--ds-text-muted); line-height: 1.65; margin: 0 0 1.25rem; max-width: 22rem; }
.plp-card-cta { }

/* Split template */
.plp-split { min-height: 100vh; display: grid; grid-template-columns: 1fr; }
@media (min-width: 768px) { .plp-split { grid-template-columns: 1fr 1fr; align-items: stretch; } }
@media (min-width: 1024px) { .plp-split { max-width: 80rem; margin-left: auto; margin-right: auto; } }
.plp-split-img { min-height: 35vh; background: linear-gradient(135deg, #ede9fe, #f5f3ff); display: flex; align-items: center; justify-content: center; padding: 2rem 1.5rem; }
@media (min-width: 768px) { .plp-split-img { min-height: 100vh; padding: 3rem; } }
.plp-split-img img { max-width: 100%; max-height: 60vh; object-fit: contain; border-radius: 0.75rem; box-shadow: 0 16px 32px -12px rgba(0,0,0,0.15); }
@media (min-width: 768px) { .plp-split-img img { max-height: 75vh; } }
.plp-split-content { display: flex; flex-direction: column; justify-content: center; padding: 2.5rem 1.5rem; background: #fff; align-items: flex-start; }
@media (min-width: 768px) { .plp-split-content { padding: 3rem 2.5rem; } }
@media (min-width: 1024px) { .plp-split-content { padding: 4rem 3rem; } }
.plp-split-title { font-size: 1.75rem; font-weight: 900; color: var(--ds-text); margin: 0 0 0.5rem; line-height: 1.3; text-align: right; }
@media (min-width: 768px) { .plp-split-title { font-size: 2rem; } }
.plp-split-sub { font-size: 1.0625rem; color: var(--ds-text-subtle); margin: 0 0 1rem; line-height: 1.5; text-align: right; }
.plp-split-desc { font-size: 0.9375rem; color: var(--ds-text-muted); line-height: 1.7; margin: 0 0 1.25rem; text-align: right; max-width: 28rem; }
.plp-split-price { font-size: 1.4375rem; font-weight: 800; color: var(--plp-primary); margin: 0 0 1.5rem; }
.plp-split-cta { }

/* Notes, share, footer */
.plp-notes { padding: 1.5rem 1.25rem; background: #fff; margin: 2.5rem 1.25rem; border-radius: 0.75rem; border-right: 4px solid var(--plp-primary); font-size: 0.875rem; color: var(--ds-text-muted); line-height: 1.65; max-width: 48rem; margin-left: auto; margin-right: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
@media (min-width: 640px) { .plp-notes { padding: 1.75rem 1.5rem; margin-left: auto; margin-right: auto; margin-top: 3rem; margin-bottom: 3rem; } }
.plp-share-wrap { padding: 2rem 1.25rem; text-align: center; }
.plp-share-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ds-text-subtle); margin: 0 0 0.75rem; }
.plp-share-row { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 0.625rem; }
.plp-share-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0.625rem 1.25rem; min-height: 44px; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; color: #fff; border: none; cursor: pointer; font-family: inherit; }
.plp-share-btn--copy { background: var(--ds-text-muted); }
.plp-share-btn--wa { background: #25d366; }
.plp-share-btn--tg { background: #0088cc; }
.plp-footer { margin-top: 2.5rem; padding: 2.5rem 1.25rem 3rem; border-top: 2px solid var(--ds-border); background: #fafaf9; }
@media (min-width: 640px) { .plp-footer { padding: 3rem 1.5rem 4rem; } }
@media (min-width: 1024px) { .plp-footer { padding: 3.5rem 2rem 4.5rem; } }
.plp-footer-inner { max-width: 64rem; margin-left: auto; margin-right: auto; }
.plp-footer-grid { display: grid; gap: 1.25rem; }
@media (min-width: 640px) { .plp-footer-grid { grid-template-columns: repeat(2, 1fr); gap: 1.5rem; } }
@media (min-width: 768px) { .plp-footer-grid { grid-template-columns: repeat(3, 1fr); gap: 1.75rem; } }
@media (min-width: 1024px) { .plp-footer-grid { max-width: 64rem; margin: 0 auto; } }
.plp-footer-block { padding: 1.5rem 1.25rem; background: #fff; border-radius: 0.75rem; border: 1px solid var(--ds-border); text-align: right; }
@media (min-width: 1024px) { .plp-footer-block { padding: 1.75rem 1.5rem; } }
.plp-footer-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ds-text-subtle); margin: 0 0 0.5rem; }
.plp-footer-body { font-size: 0.9375rem; color: var(--ds-text); line-height: 1.6; }
.plp-social-row { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem; }
.plp-social-btn { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 0.5rem; color: #fff; text-decoration: none; }
.plp-social-btn--ig { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743); }
.plp-social-btn--tg { background: #0088cc; }
.plp-social-btn--wa { background: #25d366; }
.plp-contact-link { color: var(--plp-primary); text-decoration: none; font-weight: 600; }

/* Photo gallery (carousel) */
.plp-gallery { display: flex; gap: 0; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; -ms-overflow-style: none; }
.plp-gallery::-webkit-scrollbar { display: none; }
.plp-gallery-img { scroll-snap-align: center; flex-shrink: 0; }
.plp-gallery--hero { max-width: 20rem; width: 100%; margin: 0 0 2rem; border-radius: 1.25rem; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.2); }
.plp-gallery--hero .plp-gallery-img { width: 100%; min-width: 100%; aspect-ratio: 1; object-fit: cover; }
.plp-gallery--card { width: 100%; aspect-ratio: 1; }
.plp-gallery--card .plp-gallery-img { width: 100%; height: 100%; aspect-ratio: 1; object-fit: cover; }
.plp-gallery--split { width: 100%; max-height: 60vh; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 16px 32px -12px rgba(0,0,0,0.15); }
@media (min-width: 768px) { .plp-gallery--split { max-height: 75vh; } }
.plp-gallery--split .plp-gallery-img { width: 100%; height: 100%; max-height: 60vh; object-fit: contain; }
@media (min-width: 768px) { .plp-gallery--split .plp-gallery-img { max-height: 75vh; } }
.plp-gallery-dots { display: flex; justify-content: center; gap: 0.5rem; margin-top: 0.75rem; }
.plp-gallery-dot { width: 0.5rem; height: 0.5rem; border-radius: 50%; background: var(--ds-text-muted); opacity: 0.5; transition: opacity 0.2s; }
.plp-gallery-dot.active { opacity: 1; background: var(--plp-primary); }
</style>
@endpush

@section('content')
<div class="plp">
    @if ($template === 'minimal')
        <div class="plp-minimal">
            <div class="plp-minimal-inner plp-anim">
                <div class="plp-minimal-content">
                    <h1 class="plp-minimal-title plp-anim plp-anim-delay-2">{{ $headline }}</h1>
                    @if ($subheadline)
                        <p class="plp-minimal-sub plp-anim plp-anim-delay-2">{{ $subheadline }}</p>
                    @endif
                    @if ($page->show_price && $price !== null)
                        <div class="plp-minimal-price plp-anim plp-anim-delay-3">{{ FormatHelper::priceForList($price, $priceFormat) }}</div>
                    @endif
                    @if ($product?->description)
                        <div class="plp-minimal-desc plp-anim plp-anim-delay-3">{{ $product->description }}</div>
                    @endif
                    @if ($ctaHref)
                        <a href="{{ $ctaHref }}" class="plp-cta plp-minimal-cta plp-anim plp-anim-delay-4" target="_blank" rel="noopener">{{ $ctaText }}</a>
                    @endif
                </div>
                @if ($mainPhotoUrl)
                    <div class="plp-minimal-visual">
                        <img src="{{ $mainPhotoUrl }}" alt="" class="plp-minimal-img plp-anim plp-anim-delay-1" loading="eager">
                    </div>
                @endif
            </div>
        </div>
    @elseif ($template === 'card')
        <div class="plp-card">
            <div class="plp-card-box plp-anim">
                @if ($mainPhotoUrl)
                    @if (count($displayPhotoUrls) > 1)
                        <div class="plp-gallery plp-gallery--card">
                            @foreach ($displayPhotoUrls as $url)
                                <img src="{{ $url }}" alt="" class="plp-gallery-img plp-card-img" loading="eager">
                            @endforeach
                        </div>
                    @else
                        <img src="{{ $mainPhotoUrl }}" alt="" class="plp-card-img" loading="eager">
                    @endif
                @else
                    <div class="plp-card-img" style="background: linear-gradient(135deg, #ede9fe, #f5f3ff);"></div>
                @endif
                <div class="plp-card-body">
                    <h1 class="plp-card-title">{{ $headline }}</h1>
                    @if ($subheadline)
                        <p class="plp-card-sub">{{ $subheadline }}</p>
                    @endif
                    @if ($page->show_price && $price !== null)
                        <div class="plp-card-price">{{ FormatHelper::priceForList($price, $priceFormat) }}</div>
                    @endif
                    @if ($product?->description)
                        <div class="plp-card-desc">{{ Str::limit($product->description, 200) }}</div>
                    @endif
                    @if ($ctaHref)
                        <a href="{{ $ctaHref }}" class="plp-cta plp-card-cta" target="_blank" rel="noopener">{{ $ctaText }}</a>
                    @endif
                </div>
            </div>
        </div>
    @elseif ($template === 'split')
        <div class="plp-split">
            <div class="plp-split-img plp-anim">
                @if ($mainPhotoUrl)
                    @if (count($displayPhotoUrls) > 1)
                        <div class="plp-gallery plp-gallery--split">
                            @foreach ($displayPhotoUrls as $url)
                                <img src="{{ $url }}" alt="" class="plp-gallery-img" loading="eager">
                            @endforeach
                        </div>
                    @else
                        <img src="{{ $mainPhotoUrl }}" alt="" loading="eager">
                    @endif
                @endif
            </div>
            <div class="plp-split-content">
                <h1 class="plp-split-title plp-anim plp-anim-delay-1">{{ $headline }}</h1>
                @if ($subheadline)
                    <p class="plp-split-sub plp-anim plp-anim-delay-2">{{ $subheadline }}</p>
                @endif
                @if ($product?->description)
                    <div class="plp-split-desc plp-anim plp-anim-delay-2">{{ $product->description }}</div>
                @endif
                @if ($page->show_price && $price !== null)
                    <div class="plp-split-price plp-anim plp-anim-delay-3">{{ FormatHelper::priceForList($price, $priceFormat) }}</div>
                @endif
                @if ($ctaHref)
                    <a href="{{ $ctaHref }}" class="plp-cta plp-split-cta plp-anim plp-anim-delay-4" target="_blank" rel="noopener">{{ $ctaText }}</a>
                @endif
            </div>
        </div>
    @else
        {{-- Hero (default) --}}
        <div class="plp-hero">
            <div class="plp-hero-inner">
                <div class="plp-hero-content">
                    <h1 class="plp-hero-title plp-anim plp-anim-delay-2">{{ $headline }}</h1>
                    @if ($subheadline)
                        <p class="plp-hero-sub plp-anim plp-anim-delay-2">{{ $subheadline }}</p>
                    @endif
                    @if ($page->show_price && $price !== null)
                        <div class="plp-hero-price plp-anim plp-anim-delay-3">{{ FormatHelper::priceForList($price, $priceFormat) }}</div>
                    @endif
                    @if ($ctaHref)
                        <a href="{{ $ctaHref }}" class="plp-cta plp-hero-cta plp-anim plp-anim-delay-4" target="_blank" rel="noopener">{{ $ctaText }}</a>
                    @endif
                </div>
                @if ($mainPhotoUrl)
                    <div class="plp-hero-visual">
                        @if (count($displayPhotoUrls) > 1)
                            <div class="plp-gallery plp-gallery--hero">
                                @foreach ($displayPhotoUrls as $url)
                                    <img src="{{ $url }}" alt="" class="plp-hero-img plp-gallery-img plp-anim plp-anim-delay-1" loading="eager">
                                @endforeach
                            </div>
                        @else
                            <img src="{{ $mainPhotoUrl }}" alt="" class="plp-hero-img" loading="eager">
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if ($page->show_notes && $page->notes_text)
        <div class="plp-notes">{{ nl2br(e($page->notes_text)) }}</div>
    @endif

    @if ($page->show_share_buttons && $page->public_url)
        <div class="plp-share-wrap">
            <div class="plp-share-title">اشتراک‌گذاری</div>
            <div class="plp-share-row">
                <button type="button" id="plp-copy-link" class="plp-share-btn plp-share-btn--copy" data-url="{{ $page->public_url }}" aria-label="کپی لینک">کپی لینک</button>
                <a href="https://wa.me/?text={{ urlencode($headline . ' ' . $page->public_url) }}" class="plp-share-btn plp-share-btn--wa" target="_blank" rel="noopener">واتساپ</a>
                <a href="https://t.me/share/url?url={{ urlencode($page->public_url) }}&text={{ urlencode($headline) }}" class="plp-share-btn plp-share-btn--tg" target="_blank" rel="noopener">تلگرام</a>
            </div>
        </div>
    @endif

    @if ($hasFooter)
        <footer class="plp-footer">
            <div class="plp-footer-inner">
            <div class="plp-footer-grid">
                @if ($page->show_social && ($insta || $telegram || $whatsapp))
                    <div class="plp-footer-block">
                        <div class="plp-footer-title">شبکه‌های اجتماعی</div>
                        <div class="plp-social-row">
                            @if ($insta)
                                <a href="https://instagram.com/{{ $insta }}" class="plp-social-btn plp-social-btn--ig" target="_blank" rel="noopener" aria-label="اینستاگرام">
                                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                            @endif
                            @if ($telegram)
                                <a href="https://t.me/{{ $telegram }}" class="plp-social-btn plp-social-btn--tg" target="_blank" rel="noopener" aria-label="تلگرام">
                                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                                </a>
                            @endif
                            @if ($whatsapp)
                                <a href="https://wa.me/{{ $whatsapp }}" class="plp-social-btn plp-social-btn--wa" target="_blank" rel="noopener" aria-label="واتساپ">
                                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
                @if ($page->show_address && $page->address_text)
                    <div class="plp-footer-block">
                        <div class="plp-footer-title">آدرس</div>
                        <div class="plp-footer-body">{{ nl2br(e($page->address_text)) }}</div>
                    </div>
                @endif
                @if ($page->show_contact && ($page->contact_phone || $page->contact_email))
                    <div class="plp-footer-block">
                        <div class="plp-footer-title">تماس با ما</div>
                        <div class="plp-footer-body">
                            @if ($page->contact_phone)
                                <a href="tel:{{ preg_replace('/\D/', '', $page->contact_phone) }}" class="plp-contact-link">{{ $page->contact_phone }}</a>
                            @endif
                            @if ($page->contact_phone && $page->contact_email)<br>@endif
                            @if ($page->contact_email)
                                <a href="mailto:{{ $page->contact_email }}" class="plp-contact-link">{{ $page->contact_email }}</a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
            </div>
        </footer>
    @endif
</div>

@if ($page->show_share_buttons && $page->public_url)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var copyBtn = document.getElementById('plp-copy-link');
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
});
</script>
@endpush
@endif
@endsection
