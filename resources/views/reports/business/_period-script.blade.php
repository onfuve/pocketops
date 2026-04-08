@push('scripts')
<script>
(function () {
    document.querySelectorAll('.report-period-today').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var t = btn.getAttribute('data-today');
            if (!t) return;
            var form = btn.closest('form');
            if (!form) return;
            var from = form.querySelector('input[name="from"]');
            var to = form.querySelector('input[name="to"]');
            if (from) from.value = t;
            if (to) to.value = t;
        });
    });
})();
</script>
@endpush
