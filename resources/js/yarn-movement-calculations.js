import { calculateYarnTotals } from './yarn-contract-calculations';

function recalcLineRow(row) {
    const bags = row.querySelector('[data-yarn-bags]')?.value ?? 0;
    const cones = row.querySelector('[data-yarn-cones]')?.value ?? 0;
    const packingSize = row.querySelector('[data-yarn-packing-size]')?.value ?? 0;
    const rate = row.querySelector('[data-yarn-rate]')?.value ?? 0;

    const totals = calculateYarnTotals({
        no_of_bags: bags,
        no_of_cones: cones,
        packing_size: packingSize,
        rate,
        commission_percent: 0,
        brokery_percent: 0,
    });

    const set = (sel, val, dec = 4) => {
        const el = row.querySelector(sel);
        if (el) {
            el.value = dec === 2 ? Number(val).toFixed(2) : String(Number(val).toFixed(4)).replace(/\.?0+$/, '') || '0';
        }
    };

    set('[data-yarn-weight-lbs]', totals.weight_lbs);
    set('[data-yarn-total-kgs]', totals.total_kgs);
    set('[data-yarn-amount]', totals.total_amount, 2);
}

function applyItemToRow(row, item) {
    if (!item) {
        return;
    }
    const packing = row.querySelector('[data-yarn-packing-size]');
    if (packing) {
        packing.value = item.pack_size_cones ?? '';
    }
    const rate = row.querySelector('[data-yarn-rate]');
    if (rate && !rate.value && item.purchase_rate) {
        rate.value = item.purchase_rate;
    }
    const maxBags = row.querySelector('[data-yarn-bags]');
    if (maxBags && item.available_bags) {
        maxBags.max = item.available_bags;
        maxBags.title = `Available bags: ${item.available_bags}`;
    }
    recalcLineRow(row);
}

export function initYarnMovementCalculations() {
    document.querySelectorAll('[data-yarn-line-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement) || form.dataset.yarnLineBound === '1') {
            return;
        }
        form.dataset.yarnLineBound = '1';
        const items = window.erpYarnItems ?? [];

        form.querySelectorAll('[data-yarn-line-row]').forEach((row) => {
            row.addEventListener('input', () => recalcLineRow(row));
            row.addEventListener('change', () => recalcLineRow(row));
            const select = row.querySelector('[data-yarn-item-select]');
            select?.addEventListener('change', () => {
                const item = items.find((r) => String(r.id) === String(select.value));
                applyItemToRow(row, item);
            });
            if (select?.value) {
                applyItemToRow(row, items.find((r) => String(r.id) === String(select.value)));
            }
        });
    });

    document.querySelectorAll('[data-yarn-issuance-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement) || form.dataset.yarnIssuanceBound === '1') {
            return;
        }
        form.dataset.yarnIssuanceBound = '1';

        const greyContracts = window.erpGreyConversionContracts ?? [];
        const issuances = window.erpYarnIssuances ?? [];
        const items = window.erpYarnItems ?? [];

        const accountSelect = form.querySelector('[name="account_id"]');
        const greyContractSelect = form.querySelector('[name="grey_conversion_contract_id"]');
        const issuanceSelect = form.querySelector('[name="source_transaction_id"]');
        const fromAccountSelect = form.querySelector('[name="from_account_id"]');
        const toAccountSelect = form.querySelector('[name="to_account_id"]');
        const toGreySelect = form.querySelector('[name="to_grey_conversion_contract_id"]');

        const filterOptions = (select, filterFn) => {
            if (!select) {
                return;
            }
            Array.from(select.options).forEach((opt) => {
                if (!opt.value) {
                    return;
                }
                const data = JSON.parse(opt.dataset.payload || '{}');
                opt.hidden = !filterFn(data);
            });
        };

        accountSelect?.addEventListener('change', () => {
            const accountId = accountSelect.value;
            filterOptions(greyContractSelect, (c) => String(c.account_id) === accountId);
            filterOptions(issuanceSelect, (i) => String(i.account_id) === accountId);
        });

        fromAccountSelect?.addEventListener('change', () => {
            const accountId = fromAccountSelect.value;
            filterOptions(issuanceSelect, (i) => String(i.account_id) === accountId);
        });

        toAccountSelect?.addEventListener('change', () => {
            const accountId = toAccountSelect.value;
            filterOptions(toGreySelect, (c) => String(c.account_id) === accountId);
        });

        issuanceSelect?.addEventListener('change', () => {
            const issuance = issuances.find((i) => String(i.id) === String(issuanceSelect.value));
            if (!issuance) {
                return;
            }
            const set = (name, val) => {
                const el = form.querySelector(`[name="${name}"]`);
                if (el && val !== undefined && val !== null) {
                    el.value = val;
                }
            };
            set('grey_conversion_contract_id', issuance.grey_conversion_contract_id);
            set('account_id', issuance.account_id);
            const tbody = form.querySelector('[data-yarn-line-rows]');
            if (!tbody || !issuance.lines?.length) {
                return;
            }
            tbody.querySelectorAll('[data-yarn-line-row]').forEach((row, idx) => {
                const line = issuance.lines[idx];
                if (!line) {
                    return;
                }
                const itemSelect = row.querySelector('[data-yarn-item-select]');
                if (itemSelect) {
                    itemSelect.value = String(line.item_id ?? '');
                    itemSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
                const bags = row.querySelector('[data-yarn-bags]');
                if (bags) {
                    bags.value = line.qty ?? '';
                }
                const cones = row.querySelector('[data-yarn-cones]');
                if (cones) {
                    cones.value = line.cones ?? '';
                }
                const rate = row.querySelector('[data-yarn-rate]');
                if (rate) {
                    rate.value = line.rate ?? '';
                    rate.readOnly = true;
                }
                recalcLineRow(row);
            });
        });

        greyContractSelect?.addEventListener('change', () => {
            const contract = greyContracts.find((c) => String(c.id) === String(greyContractSelect.value));
            if (contract && accountSelect && !accountSelect.value) {
                accountSelect.value = String(contract.account_id);
            }
        });

        accountSelect?.dispatchEvent(new Event('change'));
        fromAccountSelect?.dispatchEvent(new Event('change'));
        toAccountSelect?.dispatchEvent(new Event('change'));
    });
}
