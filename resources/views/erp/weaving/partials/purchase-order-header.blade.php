@php
    $editingTransaction = $editingTransaction ?? null;
    $meta = $meta ?? [];
@endphp
<div class="grid gap-2 md:grid-cols-4 lg:grid-cols-6">
    @if ($editingTransaction)
        <label class="erp-field"><span class="erp-label">Purchase #</span><input class="erp-input bg-[#f8f8f8]" value="{{ $editingTransaction->trans_no }}" readonly></label>
    @endif
    <label class="erp-field"><span class="erp-label">Purchase Date</span><input class="erp-input" type="date" name="trans_date" value="{{ old('trans_date', optional($editingTransaction?->trans_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required></label>
    <label class="erp-field md:col-span-2"><span class="erp-label">Party Code</span>
        @include('erp.grey.partials.code-name-pair', [
            'selectName' => 'account_id',
            'selectedId' => old('account_id', $editingTransaction?->account_id),
            'options' => $accountParties,
            'required' => true,
            'targetId' => 'weaving-po-party',
        ])
    </label>
    <label class="erp-field"><span class="erp-label">Pay Term</span>
        <select class="erp-input" name="meta[pay_term]">
            @foreach (['CREDIT', 'CASH', 'ADVANCE'] as $term)
                <option value="{{ $term }}" @selected(old('meta.pay_term', $meta['pay_term'] ?? 'CREDIT') === $term)>{{ $term }}</option>
            @endforeach
        </select>
    </label>
    <label class="erp-field"><span class="erp-label">Payment Days</span><input class="erp-input text-right" name="meta[payment_days]" value="{{ old('meta.payment_days', $meta['payment_days'] ?? '') }}"></label>
    <label class="erp-field"><span class="erp-label">GP No</span><input class="erp-input" name="meta[gp_no]" value="{{ old('meta.gp_no', $meta['gp_no'] ?? '') }}"></label>
    <label class="erp-field"><span class="erp-label">GP Date</span><input class="erp-input" type="date" name="meta[gp_date]" value="{{ old('meta.gp_date', $meta['gp_date'] ?? '') }}"></label>
    <label class="erp-field md:col-span-2"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
</div>
