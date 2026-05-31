import { calculateYarnTotals } from './yarn-contract-calculations';

const LBS_PER_KG = 2.20462;

function parsePayload(option) {
    try {
        return JSON.parse(option?.dataset?.payload || '{}');
    } catch {
        return {};
    }
}

function setVal(el, val, dec = 4) {
    if (!el) {
        return;
    }
    const num = Number(val) || 0;
    el.value = dec === 2 ? num.toFixed(2) : String(num.toFixed(dec)).replace(/\.?0+$/, '') || '0';
}

function applyYarnItem(form, item) {
    if (!item) {
        return;
    }
    const desc = form.querySelector('[data-yarn-item-description]');
    if (desc) {
        desc.value = item.name ?? '';
    }
    const packing = form.querySelector('[data-yarn-packing-size]');
    if (packing) {
        packing.value = item.pack_size_cones ?? item.packing_size ?? '';
    }
    const packingWeight = form.querySelector('[data-yarn-packing-weight]');
    if (packingWeight && item.packing_weight !== undefined) {
        packingWeight.value = item.packing_weight;
    }
    const rate = form.querySelector('[data-yarn-rate]');
    if (rate && !rate.readOnly && item.purchase_rate) {
        rate.value = item.purchase_rate;
    }
    const itemRate = form.querySelector('[data-receipt-item-rate]');
    if (itemRate && item.purchase_rate) {
        itemRate.value = item.purchase_rate;
    }
}

function recalcStandardForm(form) {
    const totals = calculateYarnTotals({
        no_of_bags: form.querySelector('[data-yarn-bags]')?.value ?? 0,
        no_of_cones: form.querySelector('[data-yarn-cones]')?.value ?? 0,
        packing_size: form.querySelector('[data-yarn-packing-size]')?.value ?? 0,
        rate: form.querySelector('[data-yarn-rate]')?.value ?? 0,
    });
    setVal(form.querySelector('[data-yarn-weight-lbs]'), totals.weight_lbs);
    setVal(form.querySelector('[data-yarn-total-kgs]'), totals.total_kgs);
    setVal(form.querySelector('[data-yarn-amount]'), totals.total_amount, 2);
}

function recalcReceiptForm(form) {
    const grossKgs = parseFloat(form.querySelector('[data-receipt-gross-kgs]')?.value ?? 0) || 0;
    const loss = parseFloat(form.querySelector('[data-receipt-loss]')?.value ?? 0) || 0;
    const totalKgs = grossKgs;
    const totalLbs = totalKgs * LBS_PER_KG;
    const consumedLbs = totalLbs * (1 - loss / 100);
    const labourRate = parseFloat(form.querySelector('[data-receipt-labour-rate]')?.value ?? 0) || 0;
    const itemRate = parseFloat(form.querySelector('[data-receipt-item-rate]')?.value ?? 0) || 0;

    setVal(form.querySelector('[data-receipt-total-kgs]'), totalKgs);
    setVal(form.querySelector('[data-receipt-total-lbs]'), totalLbs);
    setVal(form.querySelector('[data-receipt-consumed-lbs]'), consumedLbs);
    setVal(form.querySelector('[data-receipt-labour-amount]'), consumedLbs * labourRate, 2);
    setVal(form.querySelector('[data-receipt-yarn-amount]'), consumedLbs * itemRate, 2);
    recalcStandardForm(form);
}

function bindConsumptionRow(row) {
    const issueSelect = row.querySelector('[data-consumption-issue]');
    issueSelect?.addEventListener('change', () => {
        const payload = parsePayload(issueSelect.selectedOptions[0]);
        if (!payload.item_id) {
            return;
        }
        row.querySelector('[data-consumption-item-id]').value = payload.item_id ?? '';
        row.querySelector('[data-consumption-item-name]').value = payload.item_name ?? '';
        row.querySelector('[data-consumption-rate]').value = payload.rate ?? '';
        const weight = row.querySelector('[data-consumption-weight]');
        if (weight && !weight.value) {
            weight.value = payload.weight_lbs ?? '';
        }
        const w = parseFloat(weight?.value ?? 0) || 0;
        const r = parseFloat(payload.rate ?? 0) || 0;
        setVal(row.querySelector('[data-consumption-amount]'), w * r, 2);
    });

    row.querySelector('[data-consumption-weight]')?.addEventListener('input', () => {
        const w = parseFloat(row.querySelector('[data-consumption-weight]')?.value ?? 0) || 0;
        const r = parseFloat(row.querySelector('[data-consumption-rate]')?.value ?? 0) || 0;
        setVal(row.querySelector('[data-consumption-amount]'), w * r, 2);
    });
}

function generateAutoConsumption(form) {
    const consumed = parseFloat(form.querySelector('[data-receipt-consumed-lbs]')?.value ?? 0) || 0;
    if (consumed <= 0) {
        return;
    }
    const tbody = form.querySelector('[data-consumption-rows]');
    if (!tbody) {
        return;
    }

    let rowIndex = 0;
    form.querySelectorAll('[data-blend-ratio]').forEach((ratioInput) => {
        const ratio = parseFloat(ratioInput.value ?? 0) || 0;
        if (ratio <= 0) {
            return;
        }
        const weight = consumed * (ratio / 100);
        const row = ratioInput.closest('tr');
        row?.querySelector('[data-blend-weight]')?.setAttribute('value', String(weight.toFixed(4)));

        const blendSelect = row?.querySelector('[data-blend-yarn-select]');
        const itemId = blendSelect?.value;
        if (!itemId) {
            return;
        }

        const consumptionRow = tbody.querySelectorAll('[data-consumption-row]')[rowIndex];
        if (!consumptionRow) {
            return;
        }
        consumptionRow.querySelector('[data-consumption-item-id]').value = itemId;
        consumptionRow.querySelector('[data-consumption-item-name]').value = parsePayload(blendSelect.selectedOptions[0]).name ?? '';
        consumptionRow.querySelector('[data-consumption-weight]').value = String(weight.toFixed(4));
        rowIndex += 1;
    });
}

export function initYarnScreenCalculations() {
    const items = window.erpYarnItems ?? [];
    const blendItems = window.erpBlendItems ?? [];

    document.querySelectorAll('[data-yarn-screen-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement) || form.dataset.yarnScreenBound === '1') {
            return;
        }
        form.dataset.yarnScreenBound = '1';

        const isReceipt = form.matches('[data-yarn-receipt-form]');
        const recalc = () => (isReceipt ? recalcReceiptForm(form) : recalcStandardForm(form));

        form.querySelectorAll('[data-yarn-bags], [data-yarn-cones], [data-yarn-rate], [data-yarn-packing-size]').forEach((el) => {
            el.addEventListener('input', recalc);
            el.addEventListener('change', recalc);
        });

        if (isReceipt) {
            form.querySelectorAll('[data-receipt-gross-kgs], [data-receipt-loss], [data-receipt-labour-rate], [data-receipt-item-rate]').forEach((el) => {
                el.addEventListener('input', recalc);
            });
        }

        form.querySelector('[data-yarn-item-select]')?.addEventListener('change', (e) => {
            const select = e.target;
            if (!(select instanceof HTMLSelectElement)) {
                return;
            }
            applyYarnItem(form, items.find((r) => String(r.id) === String(select.value)));
            recalc();
        });

        form.querySelector('[name="account_id"]')?.addEventListener('change', () => {
            const accountId = form.querySelector('[name="account_id"]')?.value ?? '';
            form.querySelectorAll('[data-consumption-issue] option').forEach((opt) => {
                if (!opt.value) {
                    return;
                }
                const payload = parsePayload(opt);
                opt.hidden = accountId !== '' && String(payload.account_id) !== accountId;
            });
        });

        form.querySelectorAll('[data-consumption-row]').forEach((row) => bindConsumptionRow(row));

        form.querySelectorAll('[data-blend-yarn-select]').forEach((select) => {
            select.addEventListener('change', () => {
                const item = blendItems.find((r) => String(r.id) === String(select.value));
                const row = select.closest('tr');
                if (!row || !item) {
                    return;
                }
                row.querySelector('[data-blend-field]').value = item.yarn_blend ?? '';
                row.querySelector('[data-thread-field]').value = item.yarn_thread ?? '';
                row.querySelector('[data-count-field]').value = item.yarn_count_name ?? '';
            });
        });

        form.querySelector('[data-generate-consumption]')?.addEventListener('click', () => generateAutoConsumption(form));

        form.querySelector('[name="source_transaction_id"]')?.addEventListener('change', () => {
            const select = form.querySelector('[name="source_transaction_id"]');
            const issuance = parsePayload(select?.selectedOptions[0]);
            if (!issuance.lines?.length) {
                return;
            }
            const line = issuance.lines[0];
            const itemSelect = form.querySelector('[data-yarn-item-select]');
            if (itemSelect) {
                itemSelect.value = String(line.item_id ?? '');
                itemSelect.dispatchEvent(new Event('change'));
            }
            const rate = form.querySelector('[data-yarn-rate]');
            if (rate) {
                rate.value = line.rate ?? '';
            }
        });

        const initialItem = form.querySelector('[data-yarn-item-select]');
        if (initialItem?.value) {
            applyYarnItem(form, items.find((r) => String(r.id) === String(initialItem.value)));
        }
        recalc();
    });
}
