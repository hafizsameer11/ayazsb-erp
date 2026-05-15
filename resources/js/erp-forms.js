/**
 * ERP form behaviour: dates as DD-MM-YYYY, no submit on Enter (Save only), auto uppercase text.
 */

function shouldAutoUppercase(el) {
    if (!(el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement)) {
        return false;
    }

    if (!el.closest('.erp-body')) {
        return false;
    }

    if (el.dataset.erpPreserveCase === 'true' || el.classList.contains('erp-preserve-case')) {
        return false;
    }

    if (el.classList.contains('erp-date-input')) {
        return false;
    }

    if (el.closest('.ts-control')) {
        return false;
    }

    const form = el.closest('form');
    if (form?.getAttribute('action')?.includes('login')) {
        return false;
    }

    if (el instanceof HTMLInputElement) {
        if (['password', 'email', 'number', 'hidden', 'search'].includes(el.type)) {
            return false;
        }

        if (el.readOnly) {
            return false;
        }

        if (el.inputMode === 'decimal' || el.inputMode === 'numeric') {
            return false;
        }

        if (el.classList.contains('font-mono')) {
            return false;
        }

        return el.type === 'text' || el.type === '';
    }

    return el.tagName === 'TEXTAREA';
}

function applyAutoUppercase(el) {
    if (!shouldAutoUppercase(el)) {
        return;
    }

    const upper = el.value.toLocaleUpperCase('en-US');
    if (el.value === upper) {
        return;
    }

    const start = el.selectionStart;
    const end = el.selectionEnd;
    el.value = upper;

    if (start !== null && end !== null) {
        el.setSelectionRange(start, end);
    }
}

function bindAutoUppercase(el) {
    if (!shouldAutoUppercase(el)) {
        return;
    }

    el.addEventListener('input', () => applyAutoUppercase(el));
    el.addEventListener('blur', () => applyAutoUppercase(el));
}

function initAutoUppercase() {
    const root = document.querySelector('.erp-body');
    if (!root) {
        return;
    }

    root.querySelectorAll('input[type="text"], textarea').forEach((el) => {
        bindAutoUppercase(el);
        applyAutoUppercase(el);
    });

    root.addEventListener(
        'input',
        (event) => {
            const target = event.target;
            if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement) {
                applyAutoUppercase(target);
            }
        },
        true,
    );

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (!(node instanceof HTMLElement)) {
                    return;
                }

                node.querySelectorAll('input[type="text"], textarea').forEach(bindAutoUppercase);
                if (
                    (node instanceof HTMLInputElement && node.type === 'text') ||
                    node.tagName === 'TEXTAREA'
                ) {
                    bindAutoUppercase(node);
                }
            });
        });
    });

    observer.observe(root, { childList: true, subtree: true });
}

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
    initAutoUppercase();

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

            if (event.defaultPrevented) {
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
