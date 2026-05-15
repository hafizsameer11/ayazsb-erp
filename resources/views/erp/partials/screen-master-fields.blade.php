@php
    $fieldMap = $fieldMap ?? [];
    $primaryLabel = $fieldMap['primaryLabel'] ?? 'Transaction #';
    $secondaryLabel = $fieldMap['secondaryLabel'] ?? 'Code';
    $partyLabel = $fieldMap['partyLabel'] ?? 'Party / Account name';
    $statusLabel = $fieldMap['statusLabel'] ?? 'Status';
@endphp
<div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 md:grid-cols-2 lg:grid-cols-4">
    <label class="erp-field"><span class="erp-label">{{ $primaryLabel }}</span><input class="erp-input" type="text" name="trans_no" placeholder="Auto"></label>
    <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="trans_date" :required="true" /></label>
    <label class="erp-field"><span class="erp-label">{{ $secondaryLabel }}</span><input class="erp-input" type="text" name="trans_code"></label>
    <label class="erp-field">
        <span class="erp-label">{{ $statusLabel }}</span>
        <select class="erp-input" name="status"><option>Draft</option><option>Final</option><option>Running</option></select>
    </label>
    <label class="erp-field md:col-span-2"><span class="erp-label">{{ $partyLabel }}</span><input class="erp-input" type="text" name="party_id" placeholder="Party id"></label>
    <label class="erp-field"><span class="erp-label">Remarks</span><input class="erp-input" type="text" name="remarks"></label>
    <label class="erp-field"><span class="erp-label">Reference</span><input class="erp-input" type="text" name="reference"></label>
</div>

