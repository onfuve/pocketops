{{-- Shared searchable expandable tag section for leads, contacts, invoices --}}
@php
    $selectedTagIds = old('tag_ids');
    if ($selectedTagIds === null && isset($entity) && $entity && $entity->exists) {
        $selectedTagIds = $entity->tags->pluck('id')->toArray();
    }
    $selectedTagIds = (array) ($selectedTagIds ?? []);
    $accentColor = $accentColor ?? 'var(--ds-primary)';
    $embedded = $embedded ?? false;
@endphp
@push('styles')
<style>
.tag-section .tag-section-header { cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; padding: 0.5rem 0; user-select: none; }
.tag-section .tag-section-header:hover { color: var(--ds-primary); }
.tag-section .tag-section-header .tag-toggle { color: var(--ds-text-subtle); transition: transform 0.2s; flex-shrink: 0; }
.tag-section.expanded .tag-section-header .tag-toggle { transform: rotate(180deg); }
.tag-section .tag-section-body { display: none; padding-top: 0.75rem; }
.tag-section.expanded .tag-section-body { display: block; }
.tag-section .tag-search-wrap { margin-bottom: 0.75rem; }
.tag-section .tag-search-wrap .ds-input { min-height: 44px; }
.tag-section .tag-list { display: flex; flex-wrap: wrap; gap: 0.5rem; max-height: 14rem; overflow-y: auto; padding: 0.75rem; border-radius: var(--ds-radius); background: var(--ds-bg-muted); }
.tag-section .tag-chip-wrap { transition: opacity 0.15s; }
.tag-section .tag-chip-wrap.tag-hidden { display: none; }
.tag-section .tag-chip-wrap label { cursor: pointer; margin: 0; }
.tag-section .tag-summary { font-size: 0.75rem; color: var(--ds-text-subtle); margin-top: 0.5rem; }
.tag-section-embedded .tag-list { max-height: 10rem; }
</style>
@endpush
@isset($tags)
<div class="{{ $embedded ? '' : 'ds-form-card ' }}tag-section {{ $embedded ? 'tag-section-embedded' : '' }} {{ $tags->count() > 8 ? '' : 'expanded' }}" id="tag-section">
    <div class="tag-section-header" id="tag-section-toggle">
        <div>
            <h2 class="ds-form-card-title" style="margin: 0; border: none; padding: 0;">برچسب‌ها</h2>
            <span id="tag-header-summary" class="tag-summary" style="margin: 0.25rem 0 0 0;"></span>
        </div>
        <span class="tag-toggle" aria-hidden="true">▼</span>
    </div>
    @if ($tags->isEmpty())
        <p style="font-size: 0.875rem; color: var(--ds-text-subtle); margin: 0.5rem 0 0 0;">هنوز برچسبی ثبت نشده است. <a href="{{ route('tags.create') }}" style="color: var(--ds-primary); text-decoration: none;">اولین برچسب را اضافه کنید</a></p>
    @else
        <div class="tag-section-body">
            <div class="tag-search-wrap">
                <input type="text" id="tag-search" class="ds-input" placeholder="جستجو برچسب…" autocomplete="off">
            </div>
            <div id="tag-list" class="tag-list">
                @foreach ($tags as $tag)
                    <div class="tag-chip-wrap" data-tag-name="{{ strtolower($tag->name) }}">
                        <label class="ds-chip">
                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}" {{ in_array($tag->id, (array)$selectedTagIds) ? 'checked' : '' }} style="accent-color: {{ $accentColor }};">
                            <span style="display: inline-block; width: 0.875rem; height: 0.875rem; border-radius: 0.25rem; background: {{ $tag->color }};"></span>
                            <span style="font-size: 0.875rem; font-weight: 500; color: var(--ds-text-muted);">{{ $tag->name }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
            <p id="tag-summary" class="tag-summary"></p>
        </div>
    @endif
</div>
@endisset
@if(isset($tags) && !$tags->isEmpty())
@push('scripts')
<script>
(function () {
    var tagSection = document.getElementById('tag-section');
    var tagToggle = document.getElementById('tag-section-toggle');
    var tagSearch = document.getElementById('tag-search');
    var tagList = document.getElementById('tag-list');
    var tagSummary = document.getElementById('tag-summary');
    var tagHeaderSummary = document.getElementById('tag-header-summary');
    if (tagSection && tagToggle && tagList) {
        tagToggle.addEventListener('click', function () { tagSection.classList.toggle('expanded'); });
        if (tagSearch) {
            tagSearch.addEventListener('input', filterTags);
            tagSearch.addEventListener('keyup', filterTags);
        }
        tagList.addEventListener('change', updateTagSummary);
        function filterTags() {
            var q = (tagSearch ? tagSearch.value.trim().toLowerCase() : '');
            tagList.querySelectorAll('.tag-chip-wrap').forEach(function (wrap) {
                var name = wrap.getAttribute('data-tag-name') || '';
                wrap.classList.toggle('tag-hidden', !(!q || name.indexOf(q) !== -1));
            });
            updateTagSummary();
        }
        function updateTagSummary() {
            var chips = tagList.querySelectorAll('.tag-chip-wrap');
            var selected = 0, visible = 0;
            chips.forEach(function (wrap) {
                if (!wrap.classList.contains('tag-hidden')) visible++;
                if (wrap.querySelector('input[type="checkbox"]:checked')) selected++;
            });
            var parts = [];
            if (selected > 0) parts.push(selected + ' انتخاب شده');
            parts.push(chips.length + ' برچسب');
            if (tagHeaderSummary) tagHeaderSummary.textContent = parts.join(' · ');
            if (tagSummary) tagSummary.textContent = 'نمایش ' + visible + ' از ' + chips.length + (selected > 0 ? ' · ' + selected + ' انتخاب شده' : '');
        }
        updateTagSummary();
    }
})();
</script>
@endpush
@endif
