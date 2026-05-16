/**
 * Soft-delete saved records (admin only). Buttons use data-erp-delete.
 */

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

async function deleteRecord(button) {
    const url = button.dataset.deleteUrl;
    if (!url) {
        return;
    }

    const confirmMessage = button.dataset.deleteConfirm ?? 'Delete this record? It can be restored from the database if needed.';
    if (! window.confirm(confirmMessage)) {
        return;
    }

    const originalLabel = button.textContent;
    button.disabled = true;
    button.textContent = 'Deleting…';

    try {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
        });

        let payload = null;
        const contentType = response.headers.get('content-type') ?? '';
        if (contentType.includes('application/json')) {
            payload = await response.json();
        }

        if (! response.ok) {
            window.alert(payload?.message ?? `Delete failed (${response.status}).`);
            return;
        }

        if (payload?.redirect) {
            window.location.href = payload.redirect;
            return;
        }

        window.location.reload();
    } catch {
        window.alert('Network error. Could not delete.');
    } finally {
        button.disabled = false;
        button.textContent = originalLabel;
    }
}

export function initErpRecordDelete() {
    document.addEventListener('click', (event) => {
        const button = event.target instanceof HTMLElement
            ? event.target.closest('[data-erp-delete]')
            : null;

        if (! button) {
            return;
        }

        event.preventDefault();
        void deleteRecord(button);
    });
}
