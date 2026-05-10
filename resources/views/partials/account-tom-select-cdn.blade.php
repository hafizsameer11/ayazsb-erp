<script src="https://cdn.jsdelivr.net/npm/tom-select@2.6.2/dist/js/tom-select.complete.min.js" crossorigin="anonymous"></script>
@verbatim
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof TomSelect === 'undefined') {
            return;
        }

        function syncAccountDesc(selectEl, value) {
            var targetId = selectEl.getAttribute('data-account-desc-target');
            if (!targetId) {
                return;
            }
            var inp = document.getElementById(targetId);
            if (!inp) {
                return;
            }
            if (!value) {
                inp.value = '';
                return;
            }
            var opt = null;
            var v = String(value);
            for (var i = 0; i < selectEl.options.length; i++) {
                if (selectEl.options[i].value === v) {
                    opt = selectEl.options[i];
                    break;
                }
            }
            inp.value = opt && opt.getAttribute('data-account-desc') ? opt.getAttribute('data-account-desc') : '';
        }

        function initAccountSearch(selectEl) {
            if (selectEl.dataset.tomselectReady === '1') {
                return;
            }
            selectEl.dataset.tomselectReady = '1';

            var ts = new TomSelect(selectEl, {
                allowEmptyOption: true,
                create: false,
                maxOptions: 5000,
                dropdownParent: 'body',
                placeholder: 'Select account',
                sortField: { field: 'text', direction: 'asc' },
                onChange: function (value) {
                    syncAccountDesc(selectEl, value);
                },
            });

            syncAccountDesc(selectEl, ts.getValue());
        }

        document.querySelectorAll('select.js-account-search').forEach(initAccountSearch);
    });
</script>
@endverbatim
