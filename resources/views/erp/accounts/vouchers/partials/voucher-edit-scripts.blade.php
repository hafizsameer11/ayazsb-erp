@if ($editingVoucher ?? null)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('select.js-account-search').forEach((select) => {
                    if (select.tomselect) {
                        select.tomselect.destroy();
                    }
                    select.dataset.tomselectReady = '0';
                    if (typeof window.erpInitAccountSearch === 'function') {
                        window.erpInitAccountSearch(select);
                    }
                });
            });
        </script>
    @endpush
@endif
