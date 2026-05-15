/**
 * ERP form behaviour: dates as DD-MM-YYYY, no submit on Enter (Save only).
 */

function normalizeDateInput(el) {
    let digits = el.value.replace(/\D/g, '').slice(0, 8);
    if (digits.length >= 5) {
        digits = `${digits.slice(0, 2)}-${digits.slice(2, 4)}-${digits.slice(4)}`;
    } else if (digits.length >= 3) {
        digits = `${digits.slice(0, 2)}-${digits.slice(2)}`;
    }
    el.value = digits;
}

function isValidErpDate(value) {
    const match = /^(\d{2})-(\d{2})-(\d{4})$/.exec(value);
    if (!match) {
        return false;
    }
    const day = Number(match[1]);
    const month = Number(match[2]);
    const year = Number(match[3]);
    const date = new Date(year, month - 1, day);
    return (
        date.getFullYear() === year &&
        date.getMonth() === month - 1 &&
        date.getDate() === day
    );
}

export function initErpForms() {
    document.querySelectorAll('.erp-date-input').forEach((input) => {
        input.addEventListener('input', () => normalizeDateInput(input));
        input.addEventListener('blur', () => {
            normalizeDateInput(input);
            if (input.value && !isValidErpDate(input.value)) {
                input.setCustomValidity('Use DD-MM-YYYY');
            } else {
                input.setCustomValidity('');
            }
        });
    });

    document.addEventListener(
        'keydown',
        (event) => {
            if (event.key !== 'Enter') {
                return;
            }

            const target = event.target;
            if (!(target instanceof HTMLElement)) {
                return;
            }

            if (target.tagName === 'TEXTAREA') {
                return;
            }

            if (target instanceof HTMLButtonElement && target.type === 'submit') {
                return;
            }

            const form = target.closest('form');
            if (!form || form.dataset.erpAllowEnter === 'true') {
                return;
            }

            event.preventDefault();
        },
        true,
    );
}
