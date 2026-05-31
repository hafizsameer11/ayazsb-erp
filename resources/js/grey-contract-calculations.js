function parseContractNum(value) {
    const n = parseFloat(String(value ?? '').replace(/,/g, ''));
    return Number.isFinite(n) ? n : 0;
}

function recalcGreyContract(form) {
    const qty = parseContractNum(form.querySelector('[data-grey-contract-qty]')?.value);
    const rate = parseContractNum(form.querySelector('[data-grey-contract-rate]')?.value);
    const brokeryRate = parseContractNum(form.querySelector('[data-grey-contract-brokery]')?.value);
    const checkerRate = parseContractNum(form.querySelector('[data-grey-contract-checker]')?.value);
    const munshiana = parseContractNum(form.querySelector('[data-grey-contract-munshiana]')?.value);

    const totalAmount = Math.round(qty * rate * 100) / 100;
    const totalBrokery = Math.round(totalAmount * brokeryRate / 100 * 100) / 100;
    const totalCheckery = Math.round(totalAmount * checkerRate / 100 * 100) / 100;
    const totalMunshiana = Math.round(munshiana * 100) / 100;
    const net = Math.round((totalAmount + totalBrokery + totalCheckery + totalMunshiana) * 100) / 100;

    const set = (sel, val) => {
        const el = form.querySelector(sel);
        if (el) {
            el.value = val > 0 || sel.includes('net') ? String(val) : '';
        }
    };

    set('[data-grey-contract-total-amount]', totalAmount);
    set('[data-grey-contract-total-brokery]', totalBrokery);
    set('[data-grey-contract-total-checkery]', totalCheckery);
    set('[data-grey-contract-total-munshiana]', totalMunshiana);
    set('[data-grey-contract-total-net]', net);
}

document.querySelectorAll('[data-grey-contract-form]').forEach((form) => {
    const handler = () => recalcGreyContract(form);
    form.addEventListener('input', handler);
    form.addEventListener('change', handler);
    handler();
});
