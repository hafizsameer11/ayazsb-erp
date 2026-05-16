@php
    use App\Support\RecordHistory;

    $editingVoucher = $editingVoucher ?? null;
    $voucherSlug = $voucherSlug ?? 'jv';
    $formAction = $editingVoucher
        ? route('erp.accounts.vouchers.update', $editingVoucher)
        : route('erp.accounts.vouchers.store', ['voucherType' => strtolower($voucherCode)]);
    $cancelUrl = route('erp.accounts.vouchers.' . $voucherSlug, \App\Support\RecordHistory::historyQuery(request()));
@endphp
<form class="erp-voucher-entry shrink-0 space-y-1 p-2" action="{{ $formAction }}" method="post" data-erp-ajax-save>
    <div data-erp-form-feedback class="hidden" aria-live="polite"></div>
    @csrf
    @if ($editingVoucher)
        @method('PATCH')
        @include('erp.partials.erp-editing-banner', [
            'editingLabel' => $editingVoucher->voucher_number,
            'cancelUrl' => $cancelUrl,
        ])
    @endif
