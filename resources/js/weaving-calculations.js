function parseNum(value) {
    const n = parseFloat(String(value ?? '').replace(/,/g, ''));
    return Number.isFinite(n) ? n : 0;
}

function formatNum(value) {
    return parseNum(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function recalcWeavingRow(row) {
    const qty = parseNum(row.querySelector('[data-weaving-qty]')?.value);
    const rate = parseNum(row.querySelector('[data-weaving-rate]')?.value);
    const amountInput = row.querySelector('[data-weaving-amount]');
    if (amountInput) {
        amountInput.value = qty && rate ? formatNum(qty * rate) : '';
    }
}

function recalcWeavingTotals(form) {
    let totalQty = 0;
    let totalAmount = 0;
    form.querySelectorAll('[data-erp-detail-line]').forEach((row) => {
        totalQty += parseNum(row.querySelector('[data-weaving-qty]')?.value);
        totalAmount += parseNum(row.querySelector('[data-weaving-amount]')?.value);
    });
    const qtyEl = form.querySelector('[data-weaving-total-qty]');
    const amtEl = form.querySelector('[data-weaving-total-amount]');
    if (qtyEl) qtyEl.textContent = formatNum(totalQty);
    if (amtEl) amtEl.textContent = formatNum(totalAmount);
}

function bindWeavingItemSelect(select) {
    select.addEventListener('change', () => {
        const row = select.closest('[data-erp-detail-line]');
        if (!row) return;
        const option = select.selectedOptions[0];
        const stockMap = JSON.parse(select.dataset.stockMap || '{}');
        const itemId = select.value;
        const stockEl = row.querySelector('[data-weaving-stock]');
        const uomEl = row.querySelector('[data-weaving-uom]');
        if (stockEl) stockEl.textContent = itemId ? formatNum(stockMap[itemId] ?? 0) : '';
        if (uomEl) uomEl.textContent = option?.dataset.unit ?? '';
    });
}

function bindBeamSelect(select) {
    select.addEventListener('change', () => {
        const row = select.closest('[data-erp-detail-line]');
        const lengthInput = row?.querySelector('[data-beam-length]');
        const length = select.selectedOptions[0]?.dataset.length;
        if (lengthInput && length) lengthInput.value = length;
    });
}

function initWeavingCalculations() {
    document.querySelectorAll('[data-weaving-totals-form]').forEach((form) => {
        form.addEventListener('input', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) return;
            if (target.matches('[data-weaving-qty], [data-weaving-rate]')) {
                const row = target.closest('[data-erp-detail-line]');
                if (row) recalcWeavingRow(row);
            }
            recalcWeavingTotals(form);
        });

        form.querySelectorAll('[data-weaving-item-select]').forEach(bindWeavingItemSelect);
        form.querySelectorAll('[data-beam-select]').forEach(bindBeamSelect);
        recalcWeavingTotals(form);
    });
}

document.addEventListener('DOMContentLoaded', initWeavingCalculations);

export { initWeavingCalculations };
