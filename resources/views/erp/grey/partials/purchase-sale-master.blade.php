@php
    $direction = $direction ?? 'purchase';
    $editingTransaction = $editingTransaction ?? null;
    $meta = $meta ?? [];
    $line0 = $line0 ?? null;
    $lineMeta = $line0?->meta ?? [];
    $isPurchase = $direction === 'purchase';
    $sectionTitle = $isPurchase ? 'Grey Purchase' : 'Grey Sale';
    $idLabel = $isPurchase ? 'Purchase Id' : 'Sale Id';
    $defaultVoucher = $isPurchase ? 'GPV' : 'GSV';
    $loomTypes = ['AUTO', 'SHUTTLE LESS', 'SHUTTLE LESS DOBBY', 'POWER', 'HAND LOOM'];
    $brokeryTypes = ['PERCENTAGE', 'PER MTR', 'FIXED'];
@endphp
<fieldset class="border border-slate-400 bg-[#f4f4f4] p-2">
    <legend class="px-1 text-[11px] font-semibold">{{ $sectionTitle }}</legend>
    <div class="mb-2 flex flex-wrap items-start justify-between gap-2">
        @if (! $isPurchase)
            <button type="button" class="rounded border border-slate-500 bg-white px-3 py-1 text-[11px] font-semibold hover:bg-sky-50">Print Bill</button>
        @endif
        <div class="flex gap-2">
            <button type="button" class="rounded border border-slate-500 bg-slate-100 px-2 py-1 text-[11px]">Voucher Post</button>
            <button type="button" class="rounded border border-slate-500 bg-slate-100 px-2 py-1 text-[11px]">Voucher Print</button>
        </div>
    </div>

    <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_12rem]">
        <div class="space-y-2">
            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
                <label class="erp-field"><span class="erp-label">System Lot #</span><input class="erp-input bg-[#f0f0f0]" name="meta[system_lot_no]" value="{{ old('meta.system_lot_no', $meta['system_lot_no'] ?? '') }}" readonly></label>
                <label class="erp-field"><span class="erp-label">Manual Lot #</span><input class="erp-input" name="meta[manual_lot_no]" value="{{ old('meta.manual_lot_no', $meta['manual_lot_no'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">{{ $idLabel }}</span><input class="erp-input bg-[#f0f0f0]" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
                <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
                <label class="erp-field"><span class="erp-label">Parchi No</span><input class="erp-input" name="meta[parchi_no]" value="{{ old('meta.parchi_no', $meta['parchi_no'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Bill No</span><input class="erp-input" name="meta[bill_no]" value="{{ old('meta.bill_no', $meta['bill_no'] ?? '') }}"></label>
                <label class="erp-field flex items-end gap-3 pb-1 lg:col-span-2"><span class="inline-flex items-center gap-1 text-[11px]"><input type="checkbox" name="meta[is_final]" value="1" @checked(old('meta.is_final', $meta['is_final'] ?? true))> Final</span><span class="inline-flex items-center gap-1 text-[11px]"><input type="checkbox" name="meta[voucher_ind]" value="1" @checked(old('meta.voucher_ind', $meta['voucher_ind'] ?? false)) disabled> Voucher</span></label>
            </div>

            <div class="grid gap-2 md:grid-cols-2">
                <label class="erp-field"><span class="erp-label">Party Code</span>@include('erp.grey.partials.code-name-pair', ['selectName' => 'account_id', 'selectedId' => old('account_id', $editingTransaction?->account_id), 'options' => $accountParties, 'required' => true, 'targetId' => 'grey-party-' . $direction])</label>
                <label class="erp-field"><span class="erp-label">Loom Type</span>
                    <select class="erp-input" name="meta[loom_type]">
                        <option value=""></option>
                        @foreach ($loomTypes as $loom)
                            <option value="{{ $loom }}" @selected(old('meta.loom_type', $meta['loom_type'] ?? '') === $loom)>{{ $loom }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                <label class="erp-field lg:col-span-2"><span class="erp-label">Quality Code</span>@include('erp.grey.partials.quality-pair', ['selectName' => 'lines[0][meta][grey_quality_id]', 'selectedId' => old('lines.0.meta.grey_quality_id', $lineMeta['grey_quality_id'] ?? ''), 'targetId' => 'grey-quality-' . $direction])</label>
                <label class="erp-field"><span class="erp-label">Loom Width</span><input class="erp-input" name="meta[loom_width]" value="{{ old('meta.loom_width', $meta['loom_width'] ?? '') }}"></label>
                <label class="erp-field lg:col-span-2"><span class="erp-label">Stok Quality Code</span>@include('erp.grey.partials.quality-pair', ['selectName' => 'meta[stock_grey_quality_id]', 'selectedId' => old('meta.stock_grey_quality_id', $meta['stock_grey_quality_id'] ?? ''), 'targetId' => 'grey-stock-quality-' . $direction])</label>
                <label class="erp-field"><span class="erp-label">Loom Panna</span><input class="erp-input" name="meta[loom_panna]" value="{{ old('meta.loom_panna', $meta['loom_panna'] ?? '') }}"></label>
            </div>

            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                <label class="erp-field lg:col-span-2"><span class="erp-label">Broker Code</span>@include('erp.grey.partials.code-name-pair', ['selectName' => 'meta[broker_account_id]', 'selectedId' => old('meta.broker_account_id', $meta['broker_account_id'] ?? ''), 'options' => $accountParties, 'targetId' => 'grey-broker-' . $direction])</label>
                <label class="erp-field"><span class="erp-label">Brokery Type / Rate</span>
                    <div class="flex gap-1">
                        <select class="erp-input w-28 shrink-0" name="meta[brokery_type]">
                            <option value=""></option>
                            @foreach ($brokeryTypes as $bt)
                                <option value="{{ $bt }}" @selected(old('meta.brokery_type', $meta['brokery_type'] ?? '') === $bt)>{{ $bt }}</option>
                            @endforeach
                        </select>
                        <input class="erp-input min-w-0 flex-1 text-right" data-grey-brokery-rate name="meta[brokery_rate]" type="number" step="0.01" value="{{ old('meta.brokery_rate', $meta['brokery_rate'] ?? '') }}">
                    </div>
                </label>
                <label class="erp-field lg:col-span-2"><span class="erp-label">Checker Code</span>@include('erp.grey.partials.code-name-pair', ['selectName' => 'meta[checker_account_id]', 'selectedId' => old('meta.checker_account_id', $meta['checker_account_id'] ?? ''), 'options' => $accountParties, 'targetId' => 'grey-checker-' . $direction])</label>
                <label class="erp-field"><span class="erp-label">Checker Rate (Mtr)</span><input class="erp-input text-right" data-grey-checker-rate name="meta[checker_rate_mtr]" type="number" step="0.01" value="{{ old('meta.checker_rate_mtr', $meta['checker_rate_mtr'] ?? '') }}"></label>
            </div>

            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-5">
                <label class="erp-field"><span class="erp-label">Munshiana / Comm %</span>
                    <div class="flex gap-1">
                        <input class="erp-input text-right" data-grey-munshiana name="meta[munshiana]" type="number" step="0.01" value="{{ old('meta.munshiana', $meta['munshiana'] ?? '') }}">
                        <input class="erp-input text-right" data-grey-commission name="meta[commission_percent]" type="number" step="0.01" value="{{ old('meta.commission_percent', $meta['commission_percent'] ?? '') }}">
                    </div>
                </label>
                <label class="erp-field"><span class="erp-label">Than / Qty (Mtr)</span>
                    <div class="flex gap-1">
                        <input class="erp-input w-14 text-right" data-grey-than-qty name="meta[than_qty]" type="number" step="0.01" value="{{ old('meta.than_qty', $meta['than_qty'] ?? '') }}" placeholder="Than">
                        <input class="erp-input min-w-0 flex-1 text-right" name="meta[mtr_qty]" type="number" step="0.01" value="{{ old('meta.mtr_qty', $meta['mtr_qty'] ?? $line0?->qty) }}">
                    </div>
                </label>
                <label class="erp-field"><span class="erp-label">L/Short</span>
                    <div class="flex items-center gap-1 text-[11px]">
                        <input class="erp-input w-12 text-right" data-grey-long-qty name="meta[long_qty]" type="number" step="0.01" value="{{ old('meta.long_qty', $meta['long_qty'] ?? '') }}">
                        <span>/</span>
                        <input class="erp-input w-12 text-right" data-grey-short-qty name="meta[short_qty]" type="number" step="0.01" value="{{ old('meta.short_qty', $meta['short_qty'] ?? '') }}">
                        <input class="erp-input min-w-0 flex-1 bg-slate-100 text-right font-semibold" data-grey-net-qty name="meta[net_qty]" readonly value="{{ old('meta.net_qty', $meta['net_qty'] ?? '') }}">
                    </div>
                </label>
                <label class="erp-field"><span class="erp-label">Grey Rate (Mtr)</span><input class="erp-input text-right" data-grey-rate name="meta[grey_rate_mtr]" type="number" step="0.01" value="{{ old('meta.grey_rate_mtr', $meta['grey_rate_mtr'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Gowdown Id</span>@include('erp.grey.partials.godown-pair', ['selectedId' => old('from_godown_id', $editingTransaction?->from_godown_id), 'targetId' => 'grey-godown-' . $direction])</label>
            </div>

            <div class="grid gap-2 md:grid-cols-3">
                <label class="erp-field"><span class="erp-label">Bilty</span><input class="erp-input" name="meta[bilty]" value="{{ old('meta.bilty', $meta['bilty'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Driver</span><input class="erp-input" name="meta[driver]" value="{{ old('meta.driver', $meta['driver'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Vehicle</span><input class="erp-input" name="meta[vehicle]" value="{{ old('meta.vehicle', $meta['vehicle'] ?? '') }}"></label>
            </div>
            <label class="erp-field block"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>

            @unless ($isPurchase)
                <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                    <label class="erp-field"><span class="erp-label">Loom Width</span><input class="erp-input" name="meta[detail_loom_width]" value="{{ old('meta.detail_loom_width', $meta['detail_loom_width'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Loom Panna</span><input class="erp-input" name="meta[detail_loom_panna]" value="{{ old('meta.detail_loom_panna', $meta['detail_loom_panna'] ?? '') }}"></label>
                    <label class="erp-field lg:col-span-2"><span class="erp-label">Munshiana / Comm %</span>
                        <div class="flex gap-1">
                            <input class="erp-input text-right" name="meta[detail_munshiana]" value="{{ old('meta.detail_munshiana', $meta['detail_munshiana'] ?? '') }}">
                            <input class="erp-input text-right" name="meta[detail_commission]" value="{{ old('meta.detail_commission', $meta['detail_commission'] ?? '') }}">
                        </div>
                    </label>
                </div>
            @endunless
        </div>

        @include('erp.grey.partials.totals-box', ['meta' => $meta])
    </div>

    <input type="hidden" name="meta[voucher_type]" value="{{ old('meta.voucher_type', $meta['voucher_type'] ?? $defaultVoucher) }}">
    <input type="hidden" name="lines[0][qty]" data-grey-line-qty value="{{ old('lines.0.qty', $line0?->qty) }}">
    <input type="hidden" name="lines[0][rate]" data-grey-line-rate value="{{ old('lines.0.rate', $line0?->rate) }}">
    <input type="hidden" name="lines[0][amount]" data-grey-line-amount value="{{ old('lines.0.amount', $line0?->amount) }}">
    <input type="hidden" name="lines[0][description]" value="{{ $isPurchase ? 'Grey purchase' : 'Grey sale' }}">
</fieldset>
