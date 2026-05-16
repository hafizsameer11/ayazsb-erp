@php
    $fieldMap = $fieldMap ?? [];
    $editingTransaction = $editingTransaction ?? null;
    $primaryLabel = $fieldMap['primaryLabel'] ?? 'Transaction #';
    $secondaryLabel = $fieldMap['secondaryLabel'] ?? 'Code';
    $partyLabel = $fieldMap['partyLabel'] ?? 'Party / Account name';
    $statusLabel = $fieldMap['statusLabel'] ?? 'Status';
    $accountParties = $accountParties ?? collect();
@endphp
<div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 md:grid-cols-2 lg:grid-cols-4">
    <label class="erp-field">
        <span class="erp-label">{{ $primaryLabel }}</span>
        <input class="erp-input border border-slate-400 bg-[#f0f0f0]" type="text" value="{{ $editingTransaction?->trans_no ?? 'Auto' }}" readonly>
    </label>
    <label class="erp-field">
        <span class="erp-label">Dated</span>
        <x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" />
    </label>
    <label class="erp-field">
        <span class="erp-label">{{ $secondaryLabel }}</span>
        <input class="erp-input" type="text" name="meta[trans_code]" value="{{ old('meta.trans_code', $editingTransaction?->meta['trans_code'] ?? '') }}">
    </label>
    @if ($accountParties->isNotEmpty())
        <label class="erp-field md:col-span-2">
            <span class="erp-label">{{ $partyLabel }}</span>
            <select class="erp-input js-account-search" name="account_id">
                <option value="">Select account</option>
                @foreach ($accountParties as $account)
                    <option value="{{ $account->id }}" @selected((string) old('account_id', $editingTransaction?->account_id) === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                @endforeach
            </select>
        </label>
    @else
        <label class="erp-field md:col-span-2">
            <span class="erp-label">{{ $partyLabel }}</span>
            <input class="erp-input" type="text" name="party_id" value="{{ old('party_id', $editingTransaction?->party_id) }}" placeholder="Party id">
        </label>
    @endif
    <label class="erp-field md:col-span-2">
        <span class="erp-label">Remarks</span>
        <input class="erp-input" type="text" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}">
    </label>
</div>
