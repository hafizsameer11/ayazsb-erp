@php
    $meta = $meta ?? [];
    $showConversion = $showConversion ?? false;
@endphp
<fieldset class="border border-slate-500 bg-[#eef3ff] p-2 lg:min-w-[12rem]">
    <legend class="px-1 text-[11px] font-semibold">Total's</legend>
    <div class="space-y-1.5 text-[11px]">
        @if ($showConversion)
            <label class="erp-field block"><span class="erp-label">Conversion Amount</span><input class="erp-input w-full bg-white text-right" name="meta[conversion_amount]" data-grey-conversion-amount value="{{ $meta['conversion_amount'] ?? '' }}" readonly></label>
        @endif
        <label class="erp-field block"><span class="erp-label">Total Gross Amount</span><input class="erp-input w-full bg-white text-right" data-grey-total-gross name="meta[total_gross_amount]" value="{{ $meta['total_gross_amount'] ?? '' }}" readonly></label>
        <label class="erp-field block"><span class="erp-label">Total Commison</span><input class="erp-input w-full bg-white text-right" data-grey-total-commission name="meta[total_commission]" value="{{ $meta['total_commission'] ?? '' }}" readonly></label>
        <label class="erp-field block"><span class="erp-label">Total Brokery</span><input class="erp-input w-full bg-white text-right" data-grey-total-brokery name="meta[total_brokery]" value="{{ $meta['total_brokery'] ?? '' }}" readonly></label>
        <label class="erp-field block"><span class="erp-label">Total Checkary</span><input class="erp-input w-full bg-white text-right" data-grey-total-checkary name="meta[total_checkary]" value="{{ $meta['total_checkary'] ?? '' }}" readonly></label>
        <label class="erp-field block"><span class="erp-label">Total Munshiana</span><input class="erp-input w-full bg-white text-right" data-grey-total-munshiana name="meta[total_munshiana]" value="{{ $meta['total_munshiana'] ?? '' }}" readonly></label>
        <label class="erp-field block"><span class="erp-label font-semibold">Net Amount</span><input class="erp-input w-full bg-white text-right text-[13px] font-bold" data-grey-total-net name="meta[total_net_amount]" value="{{ $meta['total_net_amount'] ?? '' }}" readonly></label>
    </div>
</fieldset>
