@php
$selectedIds = $selectedIds ?? [];
$users = $users ?? collect();
$showMeLabel = $showMeLabel ?? false;
@endphp
<div class="assignees-block" style="margin-bottom: 1.5rem;">
    <label class="assignees-label" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.5rem;">واگذار به</label>
    <p style="margin: 0 0 0.5rem 0; font-size: 0.75rem; color: #78716c;">با واگذاری، اعضای تیم نیز وظیفه را می‌بینند. جستجو کنید یا از لیست انتخاب کنید.</p>
    <input type="text" class="assignees-search ds-input" placeholder="جستجو نام عضو تیم…" autocomplete="off"
           style="width: 100%; margin-bottom: 0.5rem; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 0.9375rem;">
    <div class="assignees-count" style="font-size: 0.75rem; color: #78716c; margin-bottom: 0.375rem;"></div>
    <div class="assignees-list" style="max-height: 11rem; overflow-y: auto; padding: 0.25rem; border: 2px solid #e7e5e4; border-radius: 0.5rem; background: #fafaf9;">
        @foreach ($users as $u)
            <label class="assignee-row" data-name="{{ $u->name }}"
                   style="display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.6rem; border-radius: 0.375rem; cursor: pointer; font-size: 0.875rem; margin-bottom: 0.125rem;">
                <input type="checkbox" name="assigned_user_ids[]" value="{{ $u->id }}" {{ in_array($u->id, $selectedIds) ? 'checked' : '' }} class="assignee-cb" style="width: 1.125rem; height: 1.125rem; accent-color: #059669;">
                <span>{{ $u->name }}{{ $showMeLabel && $u->id === auth()->id() ? ' (من)' : '' }}</span>
            </label>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
(function () {
    function initAssigneesBlock(block) {
        var search = block.querySelector('.assignees-search');
        var list = block.querySelector('.assignees-list');
        var countEl = block.querySelector('.assignees-count');
        var rows = list ? list.querySelectorAll('.assignee-row') : [];
        var checkboxes = block.querySelectorAll('.assignee-cb');

        function updateCount() {
            var n = block.querySelectorAll('.assignee-cb:checked').length;
            countEl.textContent = n > 0 ? n + ' نفر انتخاب شده' : '';
        }

        function filterList() {
            var q = (search && search.value.trim()) ? search.value.trim().toLowerCase() : '';
            var visible = 0;
            rows.forEach(function (row) {
                var name = (row.getAttribute('data-name') || '').toLowerCase();
                var show = !q || name.indexOf(q) !== -1;
                row.style.display = show ? 'flex' : 'none';
                if (show) visible++;
            });
        }

        if (search && list) {
            search.addEventListener('input', filterList);
        }
        checkboxes.forEach(function (cb) {
            cb.addEventListener('change', updateCount);
        });
        filterList();
        updateCount();
    }

    function run() {
        document.querySelectorAll('.assignees-block').forEach(initAssigneesBlock);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
</script>
@endpush
