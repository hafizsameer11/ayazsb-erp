@php
    $meta = $meta ?? [];
    $prefix = $prefix ?? 'meta';
    $showCheckbox = $showCheckbox ?? true;
@endphp
<div class="grid gap-2 border border-slate-400 bg-[#f0f0f0] p-2 md:grid-cols-4 lg:grid-cols-5">
    <label class="erp-field"><span class="erp-label">Voucher ID</span><input class="erp-input bg-[#f8f8f8]" name="{{ $prefix }}[voucher_id]" value="{{ $meta['voucher_id'] ?? '' }}" readonly></label>
    <label class="erp-field"><span class="erp-label">Voucher #</span><input class="erp-input bg-[#f8f8f8]" name="{{ $prefix }}[voucher_num]" value="{{ $meta['voucher_num'] ?? '' }}" readonly></label>
    <label class="erp-field"><span class="erp-label">Voucher Date</span><input class="erp-input bg-[#f8f8f8]" name="{{ $prefix }}[voucher_date]" value="{{ $meta['voucher_date'] ?? '' }}" readonly></label>
    <label class="erp-field"><span class="erp-label">Voucher Type</span><input class="erp-input bg-[#f8f8f8]" name="{{ $prefix }}[voucher_type]" value="{{ $meta['voucher_type'] ?? '' }}" readonly></label>
    @if ($showCheckbox)
        <label class="erp-field flex items-end gap-2 pb-1"><input type="checkbox" name="{{ $prefix }}[voucher_posted]" value="1" @checked(! empty($meta['voucher_posted'])) disabled><span class="text-[11px]">Voucher</span></label>
    @endif
</div>
