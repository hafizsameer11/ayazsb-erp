function parseNum(value) {
    const n = parseFloat(String(value ?? '').replace(/,/g, ''));
    return Number.isFinite(n) ? n : 0;
}

function round2(n) {
    return Math.round(n * 100) / 100;
}

function recalcGreyTotals(form) {
    const thanQty = parseNum(form.querySelector('[data-grey-than-qty]')?.value);
    const longQty = parseNum(form.querySelector('[data-grey-long-qty]')?.value);
    const shortQty = parseNum(form.querySelector('[data-grey-short-qty]')?.value);
    let netQty = Math.max(0, thanQty - longQty + shortQty);
    const netField = form.querySelector('[data-grey-net-qty]');
    if (netField && !netField.readOnly) {
        const manual = parseNum(netField.value);
        if (manual > 0) {
            netQty = manual;
        }
    }
    if (netField) {
        netField.value = netQty > 0 ? String(netQty) : '';
    }

    const greyRate = parseNum(form.querySelector('[data-grey-rate]')?.value);
    const commissionPercent = parseNum(form.querySelector('[data-grey-commission]')?.value);
    const brokeryRate = parseNum(form.querySelector('[data-grey-brokery-rate]')?.value);
    const checkerRate = parseNum(form.querySelector('[data-grey-checker-rate]')?.value);
    const munshiana = parseNum(form.querySelector('[data-grey-munshiana]')?.value);

    const totalGross = round2(netQty * greyRate);
    const totalCommission = round2((totalGross * commissionPercent) / 100);
    const totalBrokery = brokeryRate > 0 && brokeryRate < 100
        ? round2((totalGross * brokeryRate) / 100)
        : round2(brokeryRate);
    const totalCheckary = round2(netQty * checkerRate);
    const totalMunshiana = round2(munshiana);
    const netAmount = round2(totalGross + totalCommission + totalBrokery + totalCheckary + totalMunshiana);

    const set = (sel, val) => {
        const el = form.querySelector(sel);
        if (el) {
            el.value = val > 0 || sel.includes('net') ? String(val) : '';
        }
    };

    set('[data-grey-total-gross]', totalGross);
    set('[data-grey-total-commission]', totalCommission);
    set('[data-grey-total-brokery]', totalBrokery);
    set('[data-grey-total-checkary]', totalCheckary);
    set('[data-grey-total-munshiana]', totalMunshiana);
    set('[data-grey-total-net]', netAmount);

    const lineQty = form.querySelector('[data-grey-line-qty]');
    const lineRate = form.querySelector('[data-grey-line-rate]');
    const lineAmount = form.querySelector('[data-grey-line-amount]');
    if (lineQty) {
        lineQty.value = String(netQty);
    }
    if (lineRate) {
        lineRate.value = String(greyRate);
    }
    if (lineAmount) {
        lineAmount.value = String(netAmount);
    }
}

document.querySelectorAll('[data-grey-totals-form]').forEach((form) => {
    const handler = () => recalcGreyTotals(form);
    form.addEventListener('input', handler);
    form.addEventListener('change', handler);
    handler();
});
