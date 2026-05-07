@php
    $showBank = $showBank ?? false;
    $showCashSummary = $showCashSummary ?? false;
@endphp
<div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 md:grid-cols-2 lg:grid-cols-4">
    <label class="erp-field"><span class="erp-label">Voucher date</span><input class="erp-input" type="date" name="voucher_date" value="{{ now()->format('Y-m-d') }}"></label>
    <label class="erp-field"><span class="erp-label">Voucher type</span><input class="erp-input" type="text" name="voucher_type" value="{{ $voucherCode ?? '' }}" readonly></label>
    <label class="erp-field"><span class="erp-label">Voucher num</span><input class="erp-input" type="text" name="voucher_num" placeholder="Auto" readonly></label>
    <label class="erp-field">
        <span class="erp-label">Fiscal year</span>
        <select class="erp-input" name="financial_year_id">
            @foreach(($financialYears ?? []) as $fy)
                <option value="{{ $fy->id }}">{{ $fy->year_code }}</option>
            @endforeach
        </select>
    </label>
    @if ($showBank)
        <label class="erp-field"><span class="erp-label">Bank code</span><input class="erp-input" type="text" name="bank_code"></label>
        <label class="erp-field md:col-span-2"><span class="erp-label">Bank name</span><input class="erp-input w-full" type="text" name="bank_name"></label>
    @endif
    <div class="flex items-end gap-4 lg:col-span-2">
        <label class="inline-flex items-center gap-1 text-[11px]"><input type="checkbox" name="feed_ind" class="h-3.5 w-3.5"> Feed ind</label>
        <label class="inline-flex items-center gap-1 text-[11px]"><input type="checkbox" name="post_ind" class="h-3.5 w-3.5"> Post ind</label>
    </div>
    @if ($showCashSummary)
        <div class="grid grid-cols-3 gap-2 border border-slate-300 bg-white p-2 text-[11px] md:col-span-2 lg:col-span-4 lg:grid-cols-3">
            <label class="erp-field"><span class="erp-label">Opening cash</span><input class="erp-input text-right font-mono" type="text" readonly value="0.00"></label>
            <label class="erp-field"><span class="erp-label">Entry effect</span><input class="erp-input text-right font-mono" type="text" readonly value="0.00"></label>
            <label class="erp-field"><span class="erp-label">Closing cash</span><input class="erp-input text-right font-mono" type="text" readonly value="0.00"></label>
        </div>
    @endif
</div>
