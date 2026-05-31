@php
    $autoConsumption = $autoConsumption ?? false;
    $editingTransaction = $editingTransaction ?? null;
    $meta = $editingTransaction?->meta ?? [];
    $line = $editingTransaction?->lines->first();
    $lookups = $yarnFormLookups ?? [];
    $consumptionRows = old('meta.consumption_lines', $meta['consumption_lines'] ?? []);
    $blendRows = old('meta.blend_lines', $meta['blend_lines'] ?? []);
    while (count($consumptionRows) < 4) { $consumptionRows[] = []; }
    while (count($blendRows) < 2) { $blendRows[] = []; }
    $cancelUrl = route('erp.yarn.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
    $defaultVoucher = 'YRV';
@endphp

<div class="erp-panel border border-slate-500 bg-white shadow-md">
    <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
        {{ $screen['code'] }} — YARN RECEIPT (PROCESSED)
        @if($autoConsumption)
            — AUTO CONSUMPTION
        @endif
    </div>
    <form class="space-y-3 p-3" data-yarn-receipt-form data-yarn-screen-form data-erp-ajax-save data-auto-consumption="{{ $autoConsumption ? '1' : '0' }}" @if($editingTransaction) data-erp-editing="1" @endif
        action="{{ $editingTransaction ? route('erp.yarn.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.yarn.screen.store', ['screen' => $screen['slug']]) }}" method="post">
        @csrf @if($editingTransaction) @method('PATCH') @endif
        <div data-erp-form-feedback class="hidden"></div>
        @if ($editingTransaction) @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingTransaction->trans_no, 'cancelUrl' => $cancelUrl]) @endif

        <div class="grid gap-3 lg:grid-cols-[1fr_220px]">
            <fieldset class="border border-slate-400 bg-[#f4f4f4] p-2">
                <legend class="px-1 text-[11px] font-semibold">Receipt</legend>
                <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                    <label class="erp-field"><span class="erp-label">Receipt #</span><input class="erp-input bg-[#f0f0f0]" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
                    <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
                    <label class="erp-field"><span class="erp-label">Receipt Form</span>
                        <select class="erp-input" name="meta[receipt_form]">@foreach($lookups['receipt_forms'] ?? [] as $f)<option value="{{ $f }}" @selected(old('meta.receipt_form',$meta['receipt_form']??'')===$f)>{{ $f }}</option>@endforeach</select>
                    </label>
                    <label class="erp-field"><span class="erp-label">Yarn Tag</span>
                        <select class="erp-input" name="meta[yarn_tag]">@foreach($lookups['yarn_tags'] ?? [] as $t)<option value="{{ $t }}" @selected(old('meta.yarn_tag',$meta['yarn_tag']??'')===$t)>{{ $t }}</option>@endforeach</select>
                    </label>
                    <label class="erp-field md:col-span-2"><span class="erp-label">Supplier Code and Name</span>
                        <select class="erp-input" name="account_id" required>
                            <option value=""></option>
                            @foreach($accountParties as $a)<option value="{{ $a->id }}" @selected((string)old('account_id',$editingTransaction?->account_id)===(string)$a->id)>{{ $a->code }} — {{ $a->name }}</option>@endforeach
                        </select>
                    </label>
                    <label class="erp-field"><span class="erp-label">DO #</span><input class="erp-input" name="meta[do_no]" value="{{ old('meta.do_no', $meta['do_no'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Voucher Type</span><input class="erp-input bg-[#f0f0f0]" name="meta[voucher_type]" value="{{ old('meta.voucher_type', $meta['voucher_type'] ?? $defaultVoucher) }}" readonly></label>
                    @include('erp.yarn.partials.yarn-item-field', ['selected' => old('item_id', $line?->item_id), 'required' => true, 'class' => 'md:col-span-2'])
                    <label class="erp-field"><span class="erp-label">Bilty</span><input class="erp-input" name="meta[bility_no]" value="{{ old('meta.bility_no', $meta['bility_no'] ?? '') }}"></label>
                    <label class="erp-field md:col-span-2"><span class="erp-label">Godown Id and Name</span>
                        <select class="erp-input" name="from_godown_id" required>
                            <option value=""></option>
                            @foreach($godowns as $g)<option value="{{ $g->id }}" @selected((string)old('from_godown_id',$editingTransaction?->from_godown_id)===(string)$g->id)>{{ $g->id }} — {{ $g->name }}</option>@endforeach
                        </select>
                    </label>
                    <label class="erp-field"><span class="erp-label">Yarn Use</span>
                        <select class="erp-input" name="meta[yarn_use]">@foreach($lookups['yarn_uses'] ?? ['any'] as $u)<option value="{{ $u }}" @selected(old('meta.yarn_use',$meta['yarn_use']??'any')===$u)>{{ strtoupper($u) }}</option>@endforeach</select>
                    </label>
                    <label class="erp-field"><span class="erp-label">Vehicle</span><input class="erp-input" name="meta[vehicle_no]" value="{{ old('meta.vehicle_no', $meta['vehicle_no'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Driver</span><input class="erp-input" name="meta[driver_name]" value="{{ old('meta.driver_name', $meta['driver_name'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Packing Size</span><input class="erp-input bg-slate-50" name="packing_size" data-yarn-packing-size value="{{ old('packing_size', $meta['packing_size'] ?? '') }}" readonly></label>
                    <label class="erp-field"><span class="erp-label">Qty</span><input class="erp-input" name="quantity" data-yarn-bags type="number" step="0.0001" value="{{ old('quantity', $line?->qty) }}"></label>
                    <label class="erp-field"><span class="erp-label">Unit</span>
                        <select class="erp-input" name="meta[qty_unit]">@foreach($lookups['qty_units'] ?? ['BAGS'] as $u)<option value="{{ $u }}" @selected(old('meta.qty_unit',$meta['qty_unit']??'BAGS')===$u)>{{ $u }}</option>@endforeach</select>
                    </label>
                    <label class="erp-field"><span class="erp-label">Cones</span><input class="erp-input" name="no_of_cones" data-yarn-cones type="number" step="0.0001" value="{{ old('no_of_cones', $meta['no_of_cones'] ?? 0) }}"></label>
                    <label class="erp-field"><span class="erp-label">Gross Weight (KGs)</span><input class="erp-input" name="meta[gross_weight_kgs]" data-receipt-gross-kgs type="number" step="0.0001" value="{{ old('meta.gross_weight_kgs', $meta['gross_weight_kgs'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Total Weight (KGs)</span><input class="erp-input bg-slate-50" name="meta[total_weight_kgs]" data-receipt-total-kgs readonly value="{{ old('meta.total_weight_kgs', $meta['total_weight_kgs'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Total Weight (LBs)</span><input class="erp-input bg-slate-50" name="meta[total_weight_lbs]" data-receipt-total-lbs readonly value="{{ old('meta.total_weight_lbs', $meta['total_weight_lbs'] ?? $line?->weight_lbs) }}"></label>
                    <label class="erp-field"><span class="erp-label">Loss %</span><input class="erp-input" name="meta[loss_percent]" data-receipt-loss type="number" step="0.0001" value="{{ old('meta.loss_percent', $meta['loss_percent'] ?? 0) }}"></label>
                    <label class="erp-field"><span class="erp-label">Yarn Consumed (LBs)</span><input class="erp-input bg-slate-50" name="meta[yarn_consumed_lbs]" data-receipt-consumed-lbs readonly value="{{ old('meta.yarn_consumed_lbs', $meta['yarn_consumed_lbs'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Labour Rate / LBs</span><input class="erp-input" name="meta[labour_rate]" data-receipt-labour-rate type="number" step="0.0001" value="{{ old('meta.labour_rate', $meta['labour_rate'] ?? 0) }}"></label>
                    <label class="erp-field"><span class="erp-label">Total Labour Amount</span><input class="erp-input bg-slate-50" name="meta[total_labour_amount]" data-receipt-labour-amount readonly value="{{ old('meta.total_labour_amount', $meta['total_labour_amount'] ?? 0) }}"></label>
                    <label class="erp-field"><span class="erp-label">Item Rate / LBs</span><input class="erp-input" name="meta[item_rate]" data-receipt-item-rate type="number" step="0.0001" value="{{ old('meta.item_rate', $meta['item_rate'] ?? $line?->rate) }}"></label>
                    <label class="erp-field"><span class="erp-label">Yarn Amount</span><input class="erp-input bg-slate-50 font-semibold" name="meta[yarn_amount]" data-receipt-yarn-amount readonly value="{{ old('meta.yarn_amount', $meta['yarn_amount'] ?? $line?->amount) }}"></label>
                    <label class="erp-field md:col-span-4"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
                </div>
            </fieldset>
            <fieldset class="border border-slate-400 bg-[#f4f4f4] p-2 text-[11px]">
                <legend class="px-1 font-semibold">Voucher</legend>
                <p class="text-slate-600">Type: {{ $defaultVoucher }}</p>
                <p class="mt-2 text-slate-500">Post voucher after saving receipt.</p>
            </fieldset>
        </div>

        @if($autoConsumption)
            <fieldset class="border border-slate-400 p-2">
                <legend class="px-1 text-[11px] font-semibold">Blend ratio</legend>
                <table class="w-full border-collapse text-[11px]">
                    <thead><tr class="bg-[#d8d8d8]"><th class="border px-1">Yarn</th><th class="border px-1">Blend</th><th class="border px-1">Thread</th><th class="border px-1">Yarn Count Name</th><th class="border px-1">Ratio %</th><th class="border px-1">Weight</th></tr></thead>
                    <tbody>
                        @foreach($blendRows as $i => $row)
                            <tr>
                                <td class="border p-0.5"><select class="erp-input w-full" name="meta[blend_lines][{{ $i }}][item_id]" data-blend-yarn-select><option value=""></option>@foreach($yarnBlendItemsPayload ?? [] as $item)<option value="{{ $item['id'] }}" data-payload="{{ json_encode($item) }}" @selected((string)($row['item_id']??'')===(string)$item['id'])>{{ $item['code'] }}</option>@endforeach</select></td>
                                <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="meta[blend_lines][{{ $i }}][blend]" data-blend-field readonly value="{{ $row['blend'] ?? '' }}"></td>
                                <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="meta[blend_lines][{{ $i }}][thread]" data-thread-field readonly value="{{ $row['thread'] ?? '' }}"></td>
                                <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="meta[blend_lines][{{ $i }}][yarn_count_name]" data-count-field readonly value="{{ $row['yarn_count_name'] ?? '' }}"></td>
                                <td class="border p-0.5"><input class="erp-input w-full" name="meta[blend_lines][{{ $i }}][ratio_percent]" data-blend-ratio type="number" step="0.01" value="{{ $row['ratio_percent'] ?? '' }}"></td>
                                <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="meta[blend_lines][{{ $i }}][weight]" data-blend-weight readonly value="{{ $row['weight'] ?? '' }}"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="button" class="mt-2 rounded border border-slate-500 bg-slate-100 px-3 py-1 text-[11px] font-semibold" data-generate-consumption>Generate Consumption</button>
            </fieldset>
        @endif

        <fieldset class="border border-slate-400 p-2">
            <legend class="px-1 text-[11px] font-semibold">
                Issuance Consumption
                @if($autoConsumption)
                    (Auto)
                @endif
            </legend>
            <table class="w-full min-w-[900px] border-collapse text-[11px]">
                <thead><tr class="bg-[#d8d8d8]">
                    <th class="border px-1">Yarn Tag</th><th class="border px-1">Issue Id</th><th class="border px-1">Yarn Id</th>
                    <th class="border px-1">Yarn Description</th><th class="border px-1">Weight (LBs)</th><th class="border px-1">Yarn Rate</th><th class="border px-1">Total Yarn Amount</th>
                </tr></thead>
                <tbody data-consumption-rows>
                    @foreach($consumptionRows as $i => $row)
                        <tr data-consumption-row>
                            <td class="border p-0.5"><select class="erp-input w-full" name="meta[consumption_lines][{{ $i }}][yarn_tag]">@foreach($lookups['yarn_tags'] ?? ['FRESH'] as $t)<option value="{{ $t }}" @selected(($row['yarn_tag']??'FRESH')===$t)>{{ $t }}</option>@endforeach</select></td>
                            <td class="border p-0.5"><select class="erp-input w-full" name="meta[consumption_lines][{{ $i }}][issue_id]" data-consumption-issue><option value=""></option>@foreach($yarnIssuanceConsumptionOptions ?? [] as $opt)<option value="{{ $opt['issue_id'] }}" data-payload="{{ json_encode($opt) }}" @selected((string)($row['issue_id']??'')===(string)$opt['issue_id'] && (string)($row['item_id']??'')===(string)$opt['item_id'])>{{ $opt['issue_no'] }}</option>@endforeach</select></td>
                            <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="meta[consumption_lines][{{ $i }}][item_id]" data-consumption-item-id readonly value="{{ $row['item_id'] ?? '' }}"></td>
                            <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="meta[consumption_lines][{{ $i }}][item_name]" data-consumption-item-name readonly value="{{ $row['item_name'] ?? '' }}"></td>
                            <td class="border p-0.5"><input class="erp-input w-full" name="meta[consumption_lines][{{ $i }}][weight_lbs]" data-consumption-weight type="number" step="0.0001" value="{{ $row['weight_lbs'] ?? '' }}"></td>
                            <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="meta[consumption_lines][{{ $i }}][rate]" data-consumption-rate readonly value="{{ $row['rate'] ?? '' }}"></td>
                            <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="meta[consumption_lines][{{ $i }}][amount]" data-consumption-amount readonly value="{{ $row['amount'] ?? '' }}"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </fieldset>

        <input type="hidden" name="submit_action" value="post">
        <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold">{{ $editingTransaction ? 'Update' : 'Save' }}</button>
    </form>
    @include('erp.yarn.partials.recent-transactions')
</div>
