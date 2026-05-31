@php
    $editingTransaction = $editingTransaction ?? null;
    $meta = $meta ?? [];
    $line0 = $editingTransaction?->lines->first();
    $lineMeta = $line0?->meta ?? [];
    $warpRows = old('meta.warp_rows', $meta['warp_rows'] ?? []);
    $weftRows = old('meta.weft_rows', $meta['weft_rows'] ?? []);
    while (count($warpRows) < 2) { $warpRows[] = []; }
    while (count($weftRows) < 3) { $weftRows[] = []; }
    $greyTags = ['FRESH', 'SECOND', 'REJECTION'];
    $loomTypes = ['SHUTTLE LESS DOBBY', 'SHUTTLE LESS', 'AUTO'];
    $brokeryTypes = ['PERCENTAGE', 'PER MTR'];
@endphp
<fieldset class="border border-slate-400 bg-[#f4f4f4] p-2">
    <legend class="px-1 text-[11px] font-semibold">Grey Inward</legend>
    <div class="mb-2 flex flex-wrap justify-between gap-2">
        <button type="button" class="rounded border border-slate-500 bg-white px-3 py-1 text-[11px] font-semibold">Inward Inspection Report</button>
        <div class="flex gap-2">
            <button type="button" class="rounded border border-slate-500 bg-slate-100 px-2 py-1 text-[11px]">Voucher Post</button>
            <button type="button" class="rounded border border-slate-500 bg-slate-100 px-2 py-1 text-[11px]">Voucher Print</button>
        </div>
    </div>
    <div class="grid gap-3 xl:grid-cols-[minmax(0,1fr)_12rem]">
        <div class="space-y-2">
            <div class="grid gap-2 lg:grid-cols-4 xl:grid-cols-6">
                <label class="erp-field"><span class="erp-label">System Lot #</span><input class="erp-input bg-[#f0f0f0]" name="meta[system_lot_no]" value="{{ old('meta.system_lot_no', $meta['system_lot_no'] ?? '') }}" readonly></label>
                <label class="erp-field"><span class="erp-label">Manual Lot #</span><input class="erp-input" name="meta[manual_lot_no]" value="{{ old('meta.manual_lot_no', $meta['manual_lot_no'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Inward Id</span><input class="erp-input bg-[#f0f0f0]" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
                <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
                <label class="erp-field"><span class="erp-label">Parchi No</span><input class="erp-input" name="meta[parchi_no]" value="{{ old('meta.parchi_no', $meta['parchi_no'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Bill No</span><input class="erp-input" name="meta[bill_no]" value="{{ old('meta.bill_no', $meta['bill_no'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Grey Tag</span>
                    <select class="erp-input" name="meta[grey_tag]">
                        @foreach ($greyTags as $tag)
                            <option value="{{ $tag }}" @selected(old('meta.grey_tag', $meta['grey_tag'] ?? 'FRESH') === $tag)>{{ $tag }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field flex items-end gap-3 pb-1"><input type="checkbox" name="meta[is_final]" value="1" @checked(old('meta.is_final', $meta['is_final'] ?? true))> <span class="text-[11px]">Final</span> <input type="checkbox" name="meta[voucher_ind]" value="1" @checked(old('meta.voucher_ind', $meta['voucher_ind'] ?? false)) disabled> <span class="text-[11px]">Voucher</span></label>
            </div>
            <label class="erp-field block max-w-2xl"><span class="erp-label">Party Code</span>@include('erp.grey.partials.code-name-pair', ['selectName' => 'account_id', 'selectedId' => old('account_id', $editingTransaction?->account_id), 'options' => $accountParties, 'targetId' => 'inward-party'])</label>
            <div class="grid gap-2 md:grid-cols-2">
                <label class="erp-field"><span class="erp-label">Contact Id</span>
                    <select class="erp-input" name="meta[grey_conversion_contract_id]">
                        <option value=""></option>
                        @foreach ($conversionContracts as $contract)
                            <option value="{{ $contract->id }}" @selected((string) old('meta.grey_conversion_contract_id', $meta['grey_conversion_contract_id'] ?? $editingTransaction?->grey_conversion_contract_id) === (string) $contract->id)>{{ $contract->id }} — {{ $contract->nature ?? $contract->contract_no }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field lg:col-span-2"><span class="erp-label">Contract Quality</span>@include('erp.grey.partials.quality-pair', ['selectName' => 'meta[contract_grey_quality_id]', 'selectedId' => old('meta.contract_grey_quality_id', $meta['contract_grey_quality_id'] ?? ''), 'targetId' => 'inward-contract-quality'])</label>
            </div>
            <label class="erp-field block"><span class="erp-label">Quality Code (In)</span>@include('erp.grey.partials.quality-pair', ['selectName' => 'lines[0][meta][grey_quality_id]', 'selectedId' => old('lines.0.meta.grey_quality_id', $lineMeta['grey_quality_id'] ?? ''), 'targetId' => 'inward-quality-in'])</label>
            <label class="erp-field block"><span class="erp-label">Stok Quality Code</span>@include('erp.grey.partials.quality-pair', ['selectName' => 'meta[stock_grey_quality_id]', 'selectedId' => old('meta.stock_grey_quality_id', $meta['stock_grey_quality_id'] ?? ''), 'targetId' => 'inward-stock-quality'])</label>
            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                <label class="erp-field lg:col-span-2"><span class="erp-label">Broker Code</span>@include('erp.grey.partials.code-name-pair', ['selectName' => 'meta[broker_account_id]', 'selectedId' => old('meta.broker_account_id', $meta['broker_account_id'] ?? ''), 'options' => $accountParties, 'targetId' => 'inward-broker'])</label>
                <label class="erp-field"><span class="erp-label">Brokery Type / Rate</span>
                    <div class="flex gap-1">
                        <select class="erp-input w-28" name="meta[brokery_type]">@foreach($brokeryTypes as $bt)<option value="{{ $bt }}" @selected(old('meta.brokery_type', $meta['brokery_type'] ?? '') === $bt)>{{ $bt }}</option>@endforeach</select>
                        <input class="erp-input flex-1 text-right" data-grey-brokery-rate name="meta[brokery_rate]" value="{{ old('meta.brokery_rate', $meta['brokery_rate'] ?? '') }}">
                    </div>
                </label>
                <label class="erp-field lg:col-span-2"><span class="erp-label">Checker Code</span>@include('erp.grey.partials.code-name-pair', ['selectName' => 'meta[checker_account_id]', 'selectedId' => old('meta.checker_account_id', $meta['checker_account_id'] ?? ''), 'options' => $accountParties, 'targetId' => 'inward-checker'])</label>
                <label class="erp-field"><span class="erp-label">Checker Rate (Mtr)</span><input class="erp-input text-right" data-grey-checker-rate name="meta[checker_rate_mtr]" value="{{ old('meta.checker_rate_mtr', $meta['checker_rate_mtr'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Gowdown Id</span>@include('erp.grey.partials.godown-pair', ['selectedId' => old('from_godown_id', $editingTransaction?->from_godown_id), 'targetId' => 'inward-godown'])</label>
                <label class="erp-field"><span class="erp-label">Than / Qty (Mtr)</span><div class="flex gap-1"><input class="erp-input w-14 text-right" data-grey-than-qty name="meta[than_qty]" value="{{ old('meta.than_qty', $meta['than_qty'] ?? '') }}"><input class="erp-input flex-1 text-right" name="meta[mtr_qty]" value="{{ old('meta.mtr_qty', $meta['mtr_qty'] ?? $line0?->qty) }}"></div></label>
                <label class="erp-field"><span class="erp-label">L/Short</span><div class="flex gap-1"><input class="erp-input w-12 text-right" data-grey-long-qty name="meta[long_qty]" value="{{ old('meta.long_qty', $meta['long_qty'] ?? '') }}"><span>/</span><input class="erp-input w-12 text-right" data-grey-short-qty name="meta[short_qty]" value="{{ old('meta.short_qty', $meta['short_qty'] ?? '') }}"><input class="erp-input flex-1 bg-slate-100 text-right font-semibold" data-grey-net-qty name="meta[net_qty]" readonly value="{{ old('meta.net_qty', $meta['net_qty'] ?? '') }}"></div></label>
                <label class="erp-field"><span class="erp-label">Conv Per Mtr Rate</span><input class="erp-input text-right" name="meta[conv_per_mtr_rate]" value="{{ old('meta.conv_per_mtr_rate', $meta['conv_per_mtr_rate'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Per Pick Rate (Paisa)</span><input class="erp-input text-right" name="meta[per_pick_rate_paisa]" value="{{ old('meta.per_pick_rate_paisa', $meta['per_pick_rate_paisa'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Pick (In)</span><input class="erp-input text-right" name="meta[pick_in]" value="{{ old('meta.pick_in', $meta['pick_in'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Loom Type</span><select class="erp-input" name="meta[loom_type]">@foreach($loomTypes as $lt)<option value="{{ $lt }}" @selected(old('meta.loom_type', $meta['loom_type'] ?? '') === $lt)>{{ $lt }}</option>@endforeach</select></label>
                <label class="erp-field"><span class="erp-label">Loom Width</span><input class="erp-input" name="meta[loom_width]" value="{{ old('meta.loom_width', $meta['loom_width'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Loom Panna</span><input class="erp-input" name="meta[loom_panna]" value="{{ old('meta.loom_panna', $meta['loom_panna'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Munshiana / Comm (Mtr)</span><div class="flex gap-1"><input class="erp-input text-right" data-grey-munshiana name="meta[munshiana]" value="{{ old('meta.munshiana', $meta['munshiana'] ?? '') }}"><input class="erp-input text-right" data-grey-commission name="meta[commission_percent]" value="{{ old('meta.commission_percent', $meta['commission_percent'] ?? '') }}"></div></label>
                <label class="erp-field"><span class="erp-label">Fabric Rate (Mtr)</span><input class="erp-input text-right" data-grey-rate name="meta[fabric_rate_mtr]" value="{{ old('meta.fabric_rate_mtr', $meta['fabric_rate_mtr'] ?? $line0?->rate) }}"></label>
                <label class="erp-field"><span class="erp-label">Fabric Amount</span><input class="erp-input text-right bg-slate-50" name="meta[fabric_amount]" value="{{ old('meta.fabric_amount', $meta['fabric_amount'] ?? $line0?->amount) }}"></label>
            </div>
            <div class="text-center"><button type="button" class="rounded border border-emerald-700 bg-emerald-50 px-4 py-1 text-[11px] font-semibold text-emerald-900">Generate Inward Consumption</button></div>
        </div>
        @include('erp.grey.partials.totals-box', ['meta' => $meta, 'showConversion' => true])
    </div>
    <div class="mt-2 grid gap-2 md:grid-cols-3">
        <label class="erp-field"><span class="erp-label">Bilty</span><input class="erp-input" name="meta[bilty]" value="{{ old('meta.bilty', $meta['bilty'] ?? '') }}"></label>
        <label class="erp-field"><span class="erp-label">Driver</span><input class="erp-input" name="meta[driver]" value="{{ old('meta.driver', $meta['driver'] ?? '') }}"></label>
        <label class="erp-field"><span class="erp-label">Vehicle</span><input class="erp-input" name="meta[vehicle]" value="{{ old('meta.vehicle', $meta['vehicle'] ?? '') }}"></label>
    </div>
    <label class="erp-field block"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
    @include('erp.grey.partials.voucher-strip', ['meta' => $meta])
    <input type="hidden" name="meta[voucher_type]" value="GCV">
    <input type="hidden" name="lines[0][qty]" data-grey-line-qty value="{{ old('lines.0.qty', $line0?->qty) }}">
    <input type="hidden" name="lines[0][rate]" data-grey-line-rate value="{{ old('lines.0.rate', $line0?->rate) }}">
    <input type="hidden" name="lines[0][amount]" data-grey-line-amount value="{{ old('lines.0.amount', $line0?->amount) }}">
    <input type="hidden" name="lines[0][description]" value="Grey conversion inward">
</fieldset>

@foreach (['warp_rows' => 'Warp Details', 'weft_rows' => 'Weft Details'] as $rowKey => $title)
    <fieldset class="mt-2 border border-slate-400 p-2">
        <legend class="px-1 text-[11px] font-semibold">{{ $title }}</legend>
        @php $rows = $rowKey === 'warp_rows' ? $warpRows : $weftRows; @endphp
        <table class="mb-2 w-full border-collapse text-[11px]">
            <thead><tr class="bg-[#d8d8d8]"><th class="border px-1">Count</th><th class="border px-1">Thread</th><th class="border px-1">Blend</th><th class="border px-1">Yarn</th><th class="border px-1">{{ $rowKey === 'warp_rows' ? 'Ends' : 'Picks' }}</th><th class="border px-1">Calc Count</th><th class="border px-1">Yarn Weight</th><th class="border px-1">Total Bags</th><th class="border px-1">Total Lbs</th></tr></thead>
            <tbody>
                @foreach ($rows as $i => $row)
                    <tr>
                        <td class="border p-0.5"><input class="erp-input w-full" name="meta[{{ $rowKey }}][{{ $i }}][count]" value="{{ $row['count'] ?? '' }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full" name="meta[{{ $rowKey }}][{{ $i }}][thread]" value="{{ $row['thread'] ?? '' }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full" name="meta[{{ $rowKey }}][{{ $i }}][blend]" value="{{ $row['blend'] ?? '' }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full" name="meta[{{ $rowKey }}][{{ $i }}][yarn]" value="{{ $row['yarn'] ?? '' }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[{{ $rowKey }}][{{ $i }}][ends_or_picks]" value="{{ $row['ends_or_picks'] ?? '' }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[{{ $rowKey }}][{{ $i }}][calc_count]" value="{{ $row['calc_count'] ?? '' }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[{{ $rowKey }}][{{ $i }}][yarn_weight]" value="{{ $row['yarn_weight'] ?? '' }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[{{ $rowKey }}][{{ $i }}][total_bags]" value="{{ $row['total_bags'] ?? '' }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[{{ $rowKey }}][{{ $i }}][total_lbs]" value="{{ $row['total_lbs'] ?? '' }}"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="text-[11px] font-semibold text-slate-700">Issue</div>
        <table class="w-full border-collapse text-[11px]">
            <thead><tr class="bg-[#d8d8d8]"><th class="border px-1">Issue Id</th><th class="border px-1">Yarn Id</th><th class="border px-1">Yarn Description</th><th class="border px-1">Qty</th><th class="border px-1">Rate</th><th class="border px-1">Amount</th></tr></thead>
            <tbody>
                @for ($j = 0; $j < 2; $j++)
                    <tr>
                        <td class="border p-0.5"><input class="erp-input w-full" name="meta[{{ $rowKey }}][issue][{{ $j }}][issue_id]" value="{{ old('meta.'.$rowKey.'.issue.'.$j.'.issue_id', data_get($meta, $rowKey.'.issue.'.$j.'.issue_id')) }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full" name="meta[{{ $rowKey }}][issue][{{ $j }}][yarn_id]" value="{{ old('meta.'.$rowKey.'.issue.'.$j.'.yarn_id', data_get($meta, $rowKey.'.issue.'.$j.'.yarn_id')) }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full" name="meta[{{ $rowKey }}][issue][{{ $j }}][yarn_description]" value="{{ old('meta.'.$rowKey.'.issue.'.$j.'.yarn_description', data_get($meta, $rowKey.'.issue.'.$j.'.yarn_description')) }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[{{ $rowKey }}][issue][{{ $j }}][qty]" value="{{ old('meta.'.$rowKey.'.issue.'.$j.'.qty', data_get($meta, $rowKey.'.issue.'.$j.'.qty')) }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[{{ $rowKey }}][issue][{{ $j }}][rate]" value="{{ old('meta.'.$rowKey.'.issue.'.$j.'.rate', data_get($meta, $rowKey.'.issue.'.$j.'.rate')) }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[{{ $rowKey }}][issue][{{ $j }}][amount]" value="{{ old('meta.'.$rowKey.'.issue.'.$j.'.amount', data_get($meta, $rowKey.'.issue.'.$j.'.amount')) }}"></td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </fieldset>
@endforeach
