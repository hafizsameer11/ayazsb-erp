@php
    $deleteUrl = $deleteUrl ?? null;
    $deleteConfirm = $deleteConfirm ?? 'Delete this record?';
@endphp
@if ($deleteUrl && \App\Support\AdminGate::canDeleteRecords())
    <button
        type="button"
        data-erp-delete
        data-delete-url="{{ $deleteUrl }}"
        data-delete-confirm="{{ $deleteConfirm }}"
        class="ml-1 rounded border border-red-600 bg-red-50 px-2 py-0.5 text-[11px] text-red-800 hover:bg-red-100"
    >Delete</button>
@endif
