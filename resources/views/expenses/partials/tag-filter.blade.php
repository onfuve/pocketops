{{-- Multi-tag filter: checkboxes + search (for expenses index GET form). Expects: $tags, $selectedTagIds --}}
@php
    $selectedTagIds = isset($selectedTagIds) ? array_map('intval', (array) $selectedTagIds) : [];
@endphp
<div class="exp-tag-filter rounded-xl border px-3 py-3" style="border-color: var(--ds-border); background: #fafaf9;">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
        <span class="text-sm font-medium text-stone-700">برچسب</span>
        <span id="exp-tag-filter-summary" class="text-xs text-stone-500 font-vazir"></span>
    </div>
    @if ($tags->isEmpty())
        <p class="text-xs text-stone-500 m-0">
            برچسبی نیست.
            <a href="{{ route('tags.create') }}" class="font-semibold" style="color: var(--ds-primary);">ساخت برچسب</a>
        </p>
    @else
        <input type="search" id="exp-tag-filter-search" class="ds-input w-full text-sm mb-2" placeholder="جستجوی برچسب…" autocomplete="off" dir="rtl">
        <div id="exp-tag-filter-list" class="exp-tag-filter-list">
            @foreach ($tags as $t)
                <label class="exp-tag-filter-row" data-tag-name="{{ mb_strtolower($t->name, 'UTF-8') }}">
                    <input type="checkbox" name="tag_ids[]" value="{{ $t->id }}" class="shrink-0" style="accent-color: #c2410c;"
                        {{ in_array((int) $t->id, $selectedTagIds, true) ? 'checked' : '' }}>
                    <span class="inline-block w-2.5 h-2.5 rounded-sm shrink-0" style="background: {{ $t->color }};"></span>
                    <span class="text-sm text-stone-700 min-w-0 truncate">{{ $t->name }}</span>
                </label>
            @endforeach
        </div>
        <div class="flex flex-wrap gap-2 mt-2">
            <button type="button" id="exp-tag-filter-clear" class="ds-btn ds-btn-outline text-xs py-1.5 px-2">پاک کردن برچسب‌ها</button>
            <a href="{{ route('tags.index') }}" class="ds-btn ds-btn-outline text-xs py-1.5 px-2">مدیریت برچسب‌ها</a>
        </div>
    @endif
</div>
