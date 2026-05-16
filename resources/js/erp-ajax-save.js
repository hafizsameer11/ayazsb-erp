/**
 * AJAX save for ERP forms marked with data-erp-ajax-save.
 * Keeps form data on validation errors; redirects only after success.
 */

function syncTomSelectValues(form) {
    form.querySelectorAll('select.js-account-search').forEach((select) => {
        if (select.tomselect) {
            const value = select.tomselect.getValue();
            select.value = value ?? '';
        }
    });
}

function findOrCreateFeedback(form) {
    let el = form.querySelector('[data-erp-form-feedback]');
    if (el) {
        return el;
    }

    el = document.createElement('div');
    el.dataset.erpFormFeedback = '1';
    el.className = 'mb-2 hidden border px-2 py-1 text-[12px]';
    form.prepend(el);
    return el;
}

function showFeedback(form, type, message) {
    const el = findOrCreateFeedback(form);
    el.classList.remove('hidden', 'border-red-300', 'bg-red-50', 'text-red-700', 'border-green-300', 'bg-green-50', 'text-green-700');

    if (type === 'error') {
        el.classList.add('border-red-300', 'bg-red-50', 'text-red-700');
    } else {
        el.classList.add('border-green-300', 'bg-green-50', 'text-green-700');
    }

    el.textContent = message;
}

function hideFeedback(form) {
    const el = form.querySelector('[data-erp-form-feedback]');
    if (el) {
        el.classList.add('hidden');
    }
}

function formatValidationErrors(payload) {
    if (payload?.message && typeof payload.message === 'string') {
        return payload.message;
    }

    const errors = payload?.errors;
    if (!errors || typeof errors !== 'object') {
        return 'Could not save. Please check your entries.';
    }

    const parts = [];
    Object.values(errors).forEach((messages) => {
        if (Array.isArray(messages)) {
            messages.forEach((m) => parts.push(m));
        }
    });

    return parts.length > 0 ? parts.join(' ') : 'Could not save. Please check your entries.';
}

async function submitFormAjax(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalLabel = submitBtn?.textContent ?? '';

    syncTomSelectValues(form);
    hideFeedback(form);

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving…';
    }

    const body = new FormData(form);
    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput?.value) {
        body.set('_method', methodInput.value);
    }

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        const contentType = response.headers.get('content-type') ?? '';
        let payload = null;

        if (contentType.includes('application/json')) {
            payload = await response.json();
        }

        if (response.status === 422) {
            showFeedback(form, 'error', formatValidationErrors(payload));
            return;
        }

        if (!response.ok) {
            showFeedback(form, 'error', payload?.message ?? `Save failed (${response.status}).`);
            return;
        }

        if (payload?.redirect) {
            window.location.href = payload.redirect;
            return;
        }

        const message = payload?.message ?? 'Saved successfully.';
        showFeedback(form, 'success', message);
        if (submitBtn) {
            submitBtn.textContent = form.dataset.erpEditing === '1' ? 'Updated' : 'Saved';
        }

        if (!payload?.redirect) {
            const url = new URL(window.location.href);
            if (url.searchParams.has('edit')) {
                url.searchParams.delete('edit');
                window.setTimeout(() => {
                    window.location.href = url.toString();
                }, 600);
            }
        }
    } catch {
        showFeedback(form, 'error', 'Network error. Please try again.');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            if (submitBtn.textContent === 'Saving…') {
                submitBtn.textContent = originalLabel;
            }
        }
    }
}

export function initErpAjaxSave() {
    document.querySelectorAll('form[data-erp-ajax-save]').forEach((form) => {
        if (!(form instanceof HTMLFormElement) || form.dataset.erpAjaxSaveBound === '1') {
            return;
        }

        form.dataset.erpAjaxSaveBound = '1';

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            void submitFormAjax(form);
        });
    });
}
