@php
    $editingTransaction = $editingTransaction ?? null;
@endphp
<div class="grid gap-2 md:grid-cols-4 lg:grid-cols-6">
    @if ($editingTransaction)
        <label class="erp-field"><span class="erp-label">Issue #</span><input class="erp-input bg-[#f8f8f8]" value="{{ $editingTransaction->trans_no }}" readonly></label>
    @endif
    <label class="erp-field"><span class="erp-label">Issue Date</span><input class="erp-input" type="date" name="trans_date" value="{{ old('trans_date', optional($editingTransaction?->trans_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required></label>
    <label class="erp-field md:col-span-2"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
</div>
