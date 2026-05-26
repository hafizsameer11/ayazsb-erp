@php
    $formVariant = $formVariant ?? 'issuance';
    $editingTransaction = $editingTransaction ?? null;
    $meta = $editingTransaction?->meta ?? [];
    $cancelUrl = route('erp.yarn.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
    $editingLines = $editingTransaction?->lines ?? collect();
    $lineRows = [];
    foreach ($editingLines as $line) {
        $lineRows[] = $line;
    }
    while (count($lineRows) < ($formVariant === 'issuance-transfer' ? 1 : 4)) {
        $lineRows[] = null;
    }
    $voucherDefaults = ['issuance' => 'YIS', 'issuance-return' => 'YIR', 'issuance-transfer' => 'YIT'];
    $defaultVoucher = $voucherDefaults[$formVariant] ?? 'YMV';
    $issuancePartyIds = $issuancePartyAccountIds ?? [];
    $greyPayload = collect($greyConversionContractsPayload ?? [])->keyBy('id');
    $editingFromGreyId = old('from_grey_conversion_contract_id', $meta['from_grey_conversion_contract_id'] ?? $editingTransaction?->sourceTransaction?->grey_conversion_contract_id ?? '');
    $editingGreyContract = $greyPayload->get((string) old('grey_conversion_contract_id', $editingTransaction?->grey_conversion_contract_id));
    $editingFromGreyContract = $greyPayload->get((string) $editingFromGreyId);
@endphp

<div class="erp-panel border border-slate-500 bg-white shadow-md">
    <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — {{ strtoupper($screen['label']) }}</div>
    <form class="space-y-3 p-3" data-yarn-issuance-form data-yarn-line-form data-erp-ajax-save data-yarn-form-variant="{{ $formVariant }}" @if($editingTransaction) data-erp-editing="1" @endif
        action="{{ $editingTransaction ? route('erp.yarn.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.yarn.screen.store', ['screen' => $screen['slug']]) }}" method="post">
        @csrf @if($editingTransaction) @method('PATCH') @endif
        <div data-erp-form-feedback class="hidden"></div>
        @if ($editingTransaction) @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingTransaction->trans_no, 'cancelUrl' => $cancelUrl]) @endif

        <fieldset class="border border-slate-400 bg-[#f4f4f4] p-2">
            <legend class="px-1 text-[11px] font-semibold">Master</legend>
            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                <label class="erp-field"><span class="erp-label">Trans ID</span><input class="erp-input bg-[#f0f0f0]" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
                <label class="erp-field"><span class="erp-label">Date</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
                <label class="erp-field"><span class="erp-label">Voucher Type</span><input class="erp-input bg-[#f0f0f0]" name="meta[voucher_type]" value="{{ old('meta.voucher_type', $meta['voucher_type'] ?? $defaultVoucher) }}" readonly></label>

                @if($formVariant === 'issuance-transfer')
                    <label class="erp-field md:col-span-2"><span class="erp-label">From Party ID and Name</span>
                        <select class="erp-input" name="from_account_id" required>
                            <option value=""></option>
                            @foreach($accountParties as $a)
                                @if(in_array($a->id, $issuancePartyIds, true) || $issuancePartyIds === [])
                                    <option value="{{ $a->id }}" @selected((string)old('from_account_id',$editingTransaction?->from_account_id)===(string)$a->id)>{{ $a->code }} — {{ $a->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </label>
                    <label class="erp-field md:col-span-2"><span class="erp-label">From Contract ID (Grey Conversion)</span>
                        <select class="erp-input" name="from_grey_conversion_contract_id" required data-initial-value="{{ $editingFromGreyId }}">
                            <option value=""></option>
                            @if($editingFromGreyContract)
                                <option value="{{ $editingFromGreyContract['id'] }}" data-payload="{{ json_encode($editingFromGreyContract) }}" selected>{{ $editingFromGreyContract['lov_label'] ?? $editingFromGreyContract['contract_code'] }}</option>
                            @endif
                        </select>
                    </label>
                    <div class="md:col-span-4 hidden rounded border border-amber-300 bg-amber-50 px-2 py-1 text-[11px] text-amber-900" data-yarn-contract-summary></div>
                    <label class="erp-field md:col-span-2"><span class="erp-label">To Party ID and Name</span>
                        <select class="erp-input" name="to_account_id" required>
                            <option value=""></option>
                            @foreach($accountParties as $a)<option value="{{ $a->id }}" @selected((string)old('to_account_id',$editingTransaction?->to_account_id)===(string)$a->id)>{{ $a->code }} — {{ $a->name }}</option>@endforeach
                        </select>
                    </label>
                    <label class="erp-field md:col-span-2"><span class="erp-label">To Contract (Grey Conversion)</span>
                        <select class="erp-input" name="to_grey_conversion_contract_id" required>
                            <option value=""></option>
                            @foreach($greyConversionContractsPayload ?? [] as $c)
                                <option value="{{ $c['id'] }}" data-payload="{{ json_encode($c) }}" @selected((string)old('to_grey_conversion_contract_id',$editingTransaction?->grey_conversion_contract_id)===(string)$c['id'])>{{ $c['lov_label'] ?? $c['contract_code'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <input type="hidden" name="source_transaction_id" value="{{ old('source_transaction_id', $editingTransaction?->source_transaction_id) }}">
                @else
                    <label class="erp-field md:col-span-2"><span class="erp-label">Party ID and Name</span>
                        <select class="erp-input" name="account_id" required>
                            <option value=""></option>
                            @foreach($accountParties as $a)
                                @if($formVariant !== 'issuance-return' || in_array($a->id, $issuancePartyIds, true) || $issuancePartyIds === [])
                                    <option value="{{ $a->id }}" @selected((string)old('account_id',$editingTransaction?->account_id)===(string)$a->id)>{{ $a->code }} — {{ $a->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </label>

                    @if($formVariant === 'issuance-return')
                        <label class="erp-field md:col-span-2"><span class="erp-label">Yarn Issuance ID</span>
                            <select class="erp-input" name="source_transaction_id" required>
                                <option value=""></option>
                                @foreach($yarnIssuanceOptions ?? [] as $issue)
                                    <option value="{{ $issue['id'] }}" data-payload="{{ json_encode($issue) }}" @selected((string)old('source_transaction_id',$editingTransaction?->source_transaction_id)===(string)$issue['id'])>{{ $issue['trans_no'] }} — {{ $issue['contract_code'] }} — {{ collect($issue['lines'])->pluck('item_code')->filter()->join(', ') }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="erp-field md:col-span-2"><span class="erp-label">Contract ID</span>
                            <input class="erp-input bg-slate-50" readonly name="meta[contract_display]" value="{{ old('meta.contract_display', $meta['contract_display'] ?? $editingGreyContract['lov_label'] ?? '') }}">
                            <input type="hidden" name="grey_conversion_contract_id" value="{{ old('grey_conversion_contract_id', $editingTransaction?->grey_conversion_contract_id) }}">
                        </label>
                    @else
                        <label class="erp-field md:col-span-2"><span class="erp-label">Contract ID (Grey Conversion)</span>
                            <select class="erp-input" name="grey_conversion_contract_id" required>
                                <option value=""></option>
                                @foreach($greyConversionContractsPayload ?? [] as $c)
                                    <option value="{{ $c['id'] }}" data-payload="{{ json_encode($c) }}" @selected((string)old('grey_conversion_contract_id',$editingTransaction?->grey_conversion_contract_id)===(string)$c['id'])>{{ $c['lov_label'] ?? $c['contract_code'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <div class="md:col-span-4 hidden rounded border border-amber-300 bg-amber-50 px-2 py-1 text-[11px] text-amber-900" data-yarn-contract-summary>@if($editingGreyContract)Required: {{ $editingGreyContract['required_bags'] ?? 0 }} | Issued: {{ $editingGreyContract['issued_bags'] ?? 0 }} | Remaining: {{ $editingGreyContract['remaining_bags'] ?? 0 }}@endif</div>
                    @endif
                @endif

                <label class="erp-field"><span class="erp-label">DO #</span><input class="erp-input" name="meta[do_no]" value="{{ old('meta.do_no', $meta['do_no'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Bility #</span><input class="erp-input" name="meta[bility_no]" value="{{ old('meta.bility_no', $meta['bility_no'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Vehicle #</span><input class="erp-input" name="meta[vehicle_no]" value="{{ old('meta.vehicle_no', $meta['vehicle_no'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Driver Name</span><input class="erp-input" name="meta[driver_name]" value="{{ old('meta.driver_name', $meta['driver_name'] ?? '') }}"></label>
                <label class="erp-field md:col-span-2"><span class="erp-label">Godown Id and Name</span>
                    <select class="erp-input" name="from_godown_id" required>
                        <option value=""></option>
                        @foreach($godowns as $g)<option value="{{ $g->id }}" @selected((string)old('from_godown_id',$editingTransaction?->from_godown_id)===(string)$g->id)>{{ $g->id }} — {{ $g->name }}</option>@endforeach
                    </select>
                </label>
                <label class="erp-field md:col-span-2"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
            </div>
        </fieldset>

        @if($formVariant === 'issuance-transfer')
            <div class="grid gap-3 lg:grid-cols-2">
                @foreach(['from' => 'From yarn', 'to' => 'To yarn'] as $side => $title)
                    <fieldset class="border border-slate-400 p-2">
                        <legend class="px-1 text-[11px] font-semibold">{{ $title }}</legend>
                        @php $line = $lineRows[0]; $prefix = $side === 'from' ? 'lines[0]' : 'meta[to_line]'; @endphp
                        <div class="grid gap-2" data-yarn-line-row>
                            <label class="erp-field"><span class="erp-label">Yarn Id</span>
                                @if($side === 'from')
                                    <select class="erp-input" name="lines[0][item_id]" data-yarn-item-select>
                                        <option value=""></option>
                                        @foreach($items as $item)<option value="{{ $item->id }}" @selected((string)old('lines.0.item_id',$line?->item_id)===(string)$item->id)>{{ $item->code }}</option>@endforeach
                                    </select>
                                @else
                                    <select class="erp-input" name="meta[to_line][item_id]" data-yarn-item-select><option value=""></option>@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->code }}</option>@endforeach</select>
                                @endif
                            </label>
                            <label class="erp-field"><span class="erp-label">Packing Size</span><input class="erp-input bg-slate-50" name="{{ $side === 'from' ? 'lines[0][meta][packing_size]' : 'meta[to_line][packing_size]' }}" data-yarn-packing-size value="{{ old($side === 'from' ? 'lines.0.meta.packing_size' : 'meta.to_line.packing_size', $line?->meta['packing_size'] ?? '') }}" readonly></label>
                            <label class="erp-field"><span class="erp-label">No of Bags</span><input class="erp-input" name="{{ $side === 'from' ? 'lines[0][qty]' : 'meta[to_line][qty]' }}" data-yarn-bags type="number" step="0.0001" value="{{ old($side === 'from' ? 'lines.0.qty' : 'meta.to_line.qty', $line?->qty) }}"></label>
                            <label class="erp-field"><span class="erp-label">No of Cones</span><input class="erp-input" name="{{ $side === 'from' ? 'lines[0][meta][no_of_cones]' : 'meta[to_line][no_of_cones]' }}" data-yarn-cones type="number" step="0.0001" value="{{ old($side === 'from' ? 'lines.0.meta.no_of_cones' : 'meta.to_line.no_of_cones', $line?->meta['no_of_cones'] ?? '') }}"></label>
                            <label class="erp-field"><span class="erp-label">Rate / LBs</span><input class="erp-input" name="{{ $side === 'from' ? 'lines[0][rate]' : 'meta[to_line][rate]' }}" data-yarn-rate type="number" step="0.0001" value="{{ old($side === 'from' ? 'lines.0.rate' : 'meta.to_line.rate', $line?->rate) }}" @if($side==='from') readonly @endif></label>
                            <label class="erp-field"><span class="erp-label">Total LBs</span><input class="erp-input bg-slate-50" name="{{ $side === 'from' ? 'lines[0][weight_lbs]' : 'meta[to_line][weight_lbs]' }}" data-yarn-weight-lbs value="{{ old($side === 'from' ? 'lines.0.weight_lbs' : 'meta.to_line.weight_lbs', $line?->weight_lbs) }}" readonly></label>
                            <label class="erp-field"><span class="erp-label">Total KGs</span><input class="erp-input bg-slate-50" data-yarn-total-kgs readonly></label>
                            <label class="erp-field"><span class="erp-label">Total Amount</span><input class="erp-input bg-slate-50" name="{{ $side === 'from' ? 'lines[0][amount]' : 'meta[to_line][amount]' }}" data-yarn-amount value="{{ old($side === 'from' ? 'lines.0.amount' : 'meta.to_line.amount', $line?->amount) }}" readonly></label>
                        </div>
                    </fieldset>
                @endforeach
            </div>
        @else
            <fieldset class="border border-slate-400 p-2">
                <legend class="px-1 text-[11px] font-semibold">Table data</legend>
                <table class="w-full min-w-[1000px] border-collapse text-[11px]">
                    <thead><tr class="bg-[#d8d8d8]">
                        <th class="border px-1">Yarn Id</th><th class="border px-1">Type</th><th class="border px-1">Pack</th>
                        <th class="border px-1">Bags</th><th class="border px-1">Cones</th><th class="border px-1">Total LBs</th>
                        <th class="border px-1">Total KGs</th><th class="border px-1">Rate/LBs</th><th class="border px-1">Amount</th>
                    </tr></thead>
                    <tbody data-yarn-line-rows>
                        @foreach($lineRows as $i => $line)
                            <tr data-yarn-line-row>
                                <td class="border p-0.5">
                                    <select class="erp-input w-full" @if($formVariant!=='issuance-return') name="lines[{{ $i }}][item_id]" @endif data-yarn-item-select @if($formVariant==='issuance-return') disabled @endif>
                                        <option value=""></option>
                                        @foreach($items as $item)
                                            @php $yarnRow = collect($yarnItemsPayload ?? [])->firstWhere('id', $item->id); @endphp
                                            <option value="{{ $item->id }}" data-payload="{{ json_encode($yarnRow) }}" @selected((string)old("lines.$i.item_id",$line?->item_id)===(string)$item->id)>{{ $yarnRow['lov_label'] ?? $item->code }}</option>
                                        @endforeach
                                    </select>
                                    @if($formVariant==='issuance-return' && ($line?->item_id || old("lines.$i.item_id")))
                                        <input type="hidden" name="lines[{{ $i }}][item_id]" value="{{ old("lines.$i.item_id", $line?->item_id) }}">
                                    @endif
                                </td>
                                <td class="border p-0.5"><select class="erp-input w-full" name="lines[{{ $i }}][meta][yarn_type]">@foreach(['any','warp','weft'] as $t)<option value="{{ $t }}" @selected(old("lines.$i.meta.yarn_type",$line?->meta['yarn_type']??'any')===$t)>{{ strtoupper($t) }}</option>@endforeach</select></td>
                                <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="lines[{{ $i }}][meta][packing_size]" data-yarn-packing-size value="{{ old("lines.$i.meta.packing_size", $line?->meta['packing_size'] ?? '') }}" readonly></td>
                                <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][qty]" data-yarn-bags type="number" step="0.0001" value="{{ old("lines.$i.qty", $line?->qty) }}"></td>
                                <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][meta][no_of_cones]" data-yarn-cones type="number" step="0.0001" value="{{ old("lines.$i.meta.no_of_cones", $line?->meta['no_of_cones'] ?? $line?->meta['cones'] ?? '') }}"></td>
                                <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="lines[{{ $i }}][weight_lbs]" data-yarn-weight-lbs value="{{ old("lines.$i.weight_lbs", $line?->weight_lbs) }}" readonly></td>
                                <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="lines[{{ $i }}][meta][total_kgs]" data-yarn-total-kgs value="{{ old("lines.$i.meta.total_kgs", $line?->meta['total_kgs'] ?? '') }}" readonly></td>
                                <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][rate]" data-yarn-rate type="number" step="0.0001" value="{{ old("lines.$i.rate", $line?->rate) }}" @if($formVariant==='issuance-return') readonly @endif></td>
                                <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="lines[{{ $i }}][amount]" data-yarn-amount value="{{ old("lines.$i.amount", $line?->amount) }}" readonly></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </fieldset>
        @endif

        <input type="hidden" name="submit_action" value="post">
        <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold">{{ $editingTransaction ? 'Update' : 'Save' }}</button>
    </form>
    @include('erp.yarn.partials.recent-transactions')
</div>
