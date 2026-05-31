function parseOpeningNum(value) {
    const n = parseFloat(String(value ?? '').replace(/,/g, ''));
    return Number.isFinite(n) ? n : 0;
}

function recalcGreyOpening(form) {
    let count = 0;
    let totalQty = 0;
    let totalAmount = 0;

    form.querySelectorAll('[data-grey-opening-row]').forEach((row) => {
        const qty = parseOpeningNum(row.querySelector('[data-grey-line-qty]')?.value);
        const rate = parseOpeningNum(row.querySelector('[data-grey-line-rate]')?.value);
        const amountField = row.querySelector('[data-grey-line-amount]');
        if (amountField) {
            const amount = qty > 0 && rate > 0 ? Math.round(qty * rate * 100) / 100 : parseOpeningNum(amountField.value);
            amountField.value = amount > 0 ? String(amount) : '';
            if (qty > 0 || amount > 0) {
                count += 1;
            }
            totalQty += qty;
            totalAmount += amount;
        }
    });

    const set = (sel, val) => {
        const el = form.querySelector(sel);
        if (el) {
            el.value = val > 0 ? String(Math.round(val * 1000) / 1000) : '';
        }
    };

    set('[data-grey-opening-count]', count);
    set('[data-grey-opening-total-qty]', totalQty);
    set('[data-grey-opening-total-amount]', totalAmount);
}

document.querySelectorAll('[data-grey-opening-form]').forEach((form) => {
    const handler = () => recalcGreyOpening(form);
    form.addEventListener('input', handler);
    form.addEventListener('change', handler);
    handler();
});
