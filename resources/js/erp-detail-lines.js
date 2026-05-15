/**
 * Dynamic detail-line tables: a few starter rows, Add more button, and Enter on last row adds another line.
 */

function reindexDetailLineNames(tbody, namePrefix) {
    const prefix = namePrefix || 'lines';
    const pattern = new RegExp(`^${prefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\[\\d+\\]`);

    tbody.querySelectorAll('tr').forEach((row, index) => {
        row.querySelectorAll('[name]').forEach((field) => {
            if (field.name) {
                field.name = field.name.replace(pattern, `${prefix}[${index}]`);
            }
        });
    });
}

function clearDetailLineRow(row) {
    row.querySelectorAll('input, select, textarea').forEach((field) => {
        if (field.type === 'checkbox' || field.type === 'radio') {
            field.checked = false;
        } else {
            field.value = '';
        }

        if (field.tagName === 'SELECT') {
            field.selectedIndex = 0;
            delete field.dataset.tomselectReady;
            const wrapper = field.parentElement?.querySelector('.ts-wrapper');
            if (wrapper) {
                wrapper.remove();
            }
            field.classList.remove('tomselected');
        }
    });
}

function initAccountSearchOnRow(row) {
    if (typeof window.erpInitAccountSearch !== 'function') {
        return;
    }

    row.querySelectorAll('select.js-account-search').forEach((select) => {
        window.erpInitAccountSearch(select);
    });
}

function cloneTemplateRow(template) {
    if (template.content) {
        const row = template.content.firstElementChild;
        return row ? row.cloneNode(true) : null;
    }

    return template.cloneNode(true);
}

function getRowFocusables(row) {
    return Array.from(
        row.querySelectorAll(
            'input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled])',
        ),
    ).filter((el) => {
        if (el instanceof HTMLInputElement && el.readOnly && el.type !== 'text') {
            return false;
        }

        return true;
    });
}

function focusFirstInRow(row) {
    const accountSelect = row.querySelector('select.js-account-search');
    if (accountSelect) {
        if (accountSelect.tomselect) {
            accountSelect.tomselect.focus();
            return;
        }

        accountSelect.focus();
        return;
    }

    const fields = getRowFocusables(row);
    fields[0]?.focus();
}

function isTomSelectDropdownOpen() {
    return document.querySelector('.ts-dropdown.active') !== null;
}

function isFieldInLastRow(field, tbody) {
    const row = field.closest('tr');
    if (!row || !tbody.contains(row)) {
        return false;
    }

    const rows = tbody.querySelectorAll('tr');
    return rows.length > 0 && rows[rows.length - 1] === row;
}

function canAddRowFromEnter(target) {
    if (target.tagName === 'TEXTAREA') {
        return false;
    }

    if (target instanceof HTMLButtonElement) {
        return false;
    }

    if (isTomSelectDropdownOpen()) {
        return false;
    }

    if (!(target instanceof HTMLInputElement || target instanceof HTMLSelectElement)) {
        return false;
    }

    if (target instanceof HTMLInputElement && target.readOnly) {
        return false;
    }

    return true;
}

function addDetailLineRow(container) {
    const tbody = container.querySelector('[data-erp-detail-lines-body]');
    const template = container.querySelector('[data-erp-detail-line-template]');
    const namePrefix = container.dataset.namePrefix || 'lines';

    if (!tbody || !template) {
        return null;
    }

    const row = cloneTemplateRow(template);
    if (!row || row.tagName !== 'TR') {
        return null;
    }

    tbody.appendChild(row);
    clearDetailLineRow(row);
    reindexDetailLineNames(tbody, namePrefix);
    initAccountSearchOnRow(row);

    return row;
}

function handleDetailLineEnter(event) {
    if (event.key !== 'Enter') {
        return;
    }

    const target = event.target;
    if (!(target instanceof HTMLElement)) {
        return;
    }

    const container = target.closest('[data-erp-detail-lines]');
    if (!container) {
        return;
    }

    const tbody = container.querySelector('[data-erp-detail-lines-body]');
    if (!tbody || !isFieldInLastRow(target, tbody) || !canAddRowFromEnter(target)) {
        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();

    const newRow = addDetailLineRow(container);
    if (newRow) {
        focusFirstInRow(newRow);
    }
}

let enterListenerBound = false;

export function initErpDetailLines() {
    if (!enterListenerBound) {
        document.addEventListener('keydown', handleDetailLineEnter, true);
        enterListenerBound = true;
    }

    document.querySelectorAll('[data-erp-detail-lines]').forEach((container) => {
        const tbody = container.querySelector('[data-erp-detail-lines-body]');
        const addButton = container.querySelector('[data-erp-detail-lines-add]');
        const template = container.querySelector('[data-erp-detail-line-template]');

        if (!tbody || !addButton || !template) {
            return;
        }

        if (addButton.dataset.erpDetailLinesBound === '1') {
            return;
        }

        addButton.dataset.erpDetailLinesBound = '1';

        addButton.addEventListener('click', () => {
            const newRow = addDetailLineRow(container);
            if (newRow) {
                focusFirstInRow(newRow);
            }
        });

        reindexDetailLineNames(tbody, container.dataset.namePrefix || 'lines');
    });
}
