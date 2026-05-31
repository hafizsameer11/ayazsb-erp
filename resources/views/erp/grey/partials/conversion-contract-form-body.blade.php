@php
    $editingContract = $editingContract ?? null;
    $warpRows = old('warp_details', $editingContract?->warp_details ?? []);
    $weftRows = old('weft_details', $editingContract?->weft_details ?? []);
    while (count($warpRows) < 4) { $warpRows[] = []; }
    while (count($weftRows) < 4) { $weftRows[] = []; }
    $statuses = ['running' => 'RUNNING', 'closed' => 'CLOSED'];
    $natures = ['EMANI', 'NORMAL', 'OTHER'];
    $loomTypes = ['SHUTTLE LESS', 'AUTO', 'SHUTTLE LESS DOBBY', 'POWER'];
    $brokeryTypes = ['PERCENTAGE', 'PER MTR', 'FIXED'];
@endphp
<fieldset class="border border-slate-400 bg-[#f4f4f4] p-2">
    <legend class="px-1 text-[11px] font-semibold">Conversion Contract Master</legend>
    <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_12rem]">
        <div class="space-y-2">
            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-5">
                <label class="erp-field"><span class="erp-label">Contract Id</span><input class="erp-input bg-[#f0f0f0]" readonly value="{{ $editingContract?->id ?? 'Auto' }}"></label>
                <label class="erp-field"><span class="erp-label">Contract #</span><input class="erp-input" name="contract_no" value="{{ old('contract_no', $editingContract?->contract_no) }}" required @if($editingContract) readonly @endif></label>
                <label class="erp-field"><span class="erp-label">Contract Code</span><input class="erp-input" name="contract_code" value="{{ old('contract_code', $editingContract?->contract_code) }}"></label>
                <label class="erp-field"><span class="erp-label">Contract Type</span><input class="erp-input" name="contract_type" value="{{ old('contract_type', $editingContract?->contract_type ?? 'CONV') }}"></label>
                <label class="erp-field"><span class="erp-label">Contract Status</span>
                    <select class="erp-input" name="status">
                        @foreach ($statuses as $val => $label)
                            <option value="{{ $val }}" @selected(old('status', $editingContract?->status ?? 'running') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field"><span class="erp-label">Contract Date</span><x-erp-date-input name="contract_date" :value="old('contract_date', $editingContract?->contract_date)" :required="true" /></label>
                <label class="erp-field"><span class="erp-label">Loom Type</span>
                    <select class="erp-input" name="loom_type">
                        <option value=""></option>
                        @foreach ($loomTypes as $lt)
                            <option value="{{ $lt }}" @selected(old('loom_type', $editingContract?->loom_type) === $lt)>{{ $lt }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label class="erp-field block max-w-2xl"><span class="erp-label">Party Code</span>@include('erp.grey.partials.code-name-pair', ['selectName' => 'account_id', 'selectedId' => old('account_id', $editingContract?->account_id), 'options' => $accountParties, 'required' => true, 'targetId' => 'conv-contract-party'])</label>
            <div class="grid gap-2 md:grid-cols-3">
                <label class="erp-field"><span class="erp-label">Nature</span>
                    <select class="erp-input" name="nature">
                        <option value=""></option>
                        @foreach ($natures as $n)
                            <option value="{{ $n }}" @selected(old('nature', $editingContract?->nature) === $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field"><span class="erp-label">Loom Width</span><input class="erp-input" name="loom_width" type="number" step="0.01" value="{{ old('loom_width', $editingContract?->loom_width) }}"></label>
                <label class="erp-field"><span class="erp-label">Loom Panna</span><input class="erp-input" name="loom_panna" value="{{ old('loom_panna', $editingContract?->loom_panna) }}"></label>
            </div>
            <label class="erp-field block"><span class="erp-label">Quality Code</span>@include('erp.grey.partials.quality-pair', ['selectName' => 'grey_quality_id', 'selectedId' => old('grey_quality_id', $editingContract?->grey_quality_id), 'targetId' => 'conv-contract-quality'])</label>
            <label class="erp-field block"><span class="erp-label">Manual Quality Name</span><input class="erp-input" name="manual_quality_name" value="{{ old('manual_quality_name', $editingContract?->manual_quality_name) }}"></label>
            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                <label class="erp-field"><span class="erp-label">Qty (Mtr)</span><input class="erp-input text-right" name="qty_mtr" type="number" step="0.01" data-grey-contract-qty value="{{ old('qty_mtr', $editingContract?->qty_mtr) }}"></label>
                <label class="erp-field"><span class="erp-label">Conv / Pick</span><input class="erp-input text-right" name="conv_per_pick" type="number" step="0.0001" value="{{ old('conv_per_pick', $editingContract?->conv_per_pick) }}"></label>
                <label class="erp-field"><span class="erp-label">Per Mtr Rate</span><input class="erp-input text-right" name="per_mtr_rate" type="number" step="0.0001" data-grey-contract-rate value="{{ old('per_mtr_rate', $editingContract?->per_mtr_rate) }}"></label>
                <label class="erp-field"><span class="erp-label">Fabric Rate</span><input class="erp-input text-right" name="fabric_rate" type="number" step="0.0001" value="{{ old('fabric_rate', $editingContract?->fabric_rate) }}"></label>
                <label class="erp-field"><span class="erp-label">Looms Plan</span><input class="erp-input" name="looms_plan" value="{{ old('looms_plan', $editingContract?->looms_plan) }}"></label>
                <label class="erp-field"><span class="erp-label">Comp Date</span><x-erp-date-input name="completion_date" :value="old('completion_date', $editingContract?->completion_date)" :default-blank="true" /></label>
            </div>
            <label class="erp-field block"><span class="erp-label">Inv Qlty Code</span>@include('erp.grey.partials.quality-pair', ['selectName' => 'invoice_quality_id', 'selectedId' => old('invoice_quality_id', $editingContract?->invoice_quality_id), 'targetId' => 'conv-invoice-quality'])</label>
            <div class="grid gap-2 md:grid-cols-2">
                <label class="erp-field lg:col-span-2"><span class="erp-label">Broker Code</span>@include('erp.grey.partials.code-name-pair', ['selectName' => 'broker_account_id', 'selectedId' => old('broker_account_id', $editingContract?->broker_account_id), 'options' => $accountParties, 'targetId' => 'conv-broker'])</label>
                <label class="erp-field"><span class="erp-label">Brokery Type</span>
                    <select class="erp-input" name="brokery_type">
                        @foreach ($brokeryTypes as $bt)
                            <option value="{{ $bt }}" @selected(old('brokery_type', $editingContract?->brokery_type ?? 'PERCENTAGE') === $bt)>{{ $bt }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field"><span class="erp-label">Brokery Rate</span><input class="erp-input text-right" name="brokery_rate" type="number" step="0.01" data-grey-contract-brokery value="{{ old('brokery_rate', $editingContract?->brokery_rate) }}"></label>
                <label class="erp-field"><span class="erp-label">Munshiana / Comm</span>
                    <div class="flex gap-1">
                        <input class="erp-input text-right" name="munshiana" type="number" step="0.01" data-grey-contract-munshiana value="{{ old('munshiana', $editingContract?->munshiana) }}">
                        <input class="erp-input text-right" name="commission_percent" type="number" step="0.01" value="{{ old('commission_percent', $editingContract?->commission_percent) }}">
                    </div>
                </label>
                <label class="erp-field lg:col-span-2"><span class="erp-label">Checkry Code</span>@include('erp.grey.partials.code-name-pair', ['selectName' => 'checker_account_id', 'selectedId' => old('checker_account_id', $editingContract?->checker_account_id), 'options' => $accountParties, 'targetId' => 'conv-checker'])</label>
                <label class="erp-field"><span class="erp-label">Checkry Rate</span><input class="erp-input text-right" name="checker_rate" type="number" step="0.01" data-grey-contract-checker value="{{ old('checker_rate', $editingContract?->checker_rate) }}"></label>
            </div>
            <label class="erp-field block"><span class="erp-label">Freight Term</span><input class="erp-input" name="freight_term" value="{{ old('freight_term', $editingContract?->freight_term) }}"></label>
            <label class="erp-field block"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingContract?->remarks) }}"></label>
        </div>
        <fieldset class="border border-slate-500 bg-[#eef3ff] p-2">
            <legend class="px-1 text-[11px] font-semibold">Totals</legend>
            <div class="space-y-1.5 text-[11px]">
                <label class="erp-field block"><span class="erp-label">Total Amount</span><input class="erp-input w-full text-right bg-white" name="total_amount" data-grey-contract-total-amount value="{{ old('total_amount', $editingContract?->total_amount) }}" readonly></label>
                <label class="erp-field block"><span class="erp-label">Total Brokery</span><input class="erp-input w-full text-right bg-white" name="total_brokery" data-grey-contract-total-brokery value="{{ old('total_brokery', $editingContract?->total_brokery) }}" readonly></label>
                <label class="erp-field block"><span class="erp-label">Total Checkry</span><input class="erp-input w-full text-right bg-white" name="total_checkery" data-grey-contract-total-checkery value="{{ old('total_checkery', $editingContract?->total_checkery) }}" readonly></label>
                <label class="erp-field block"><span class="erp-label">Total Munshiana</span><input class="erp-input w-full text-right bg-white" name="total_munshiana" data-grey-contract-total-munshiana value="{{ old('total_munshiana', $editingContract?->total_munshiana) }}" readonly></label>
                <label class="erp-field block"><span class="erp-label font-semibold">Net Amount</span><input class="erp-input w-full text-right bg-white font-bold" name="total_net_amount" data-grey-contract-total-net value="{{ old('total_net_amount', $editingContract?->total_net_amount) }}" readonly></label>
            </div>
        </fieldset>
    </div>
</fieldset>

@foreach (['warp_details' => 'Warp Details', 'weft_details' => 'Weft Details'] as $key => $title)
    @php $rows = $key === 'warp_details' ? $warpRows : $weftRows; @endphp
    <fieldset class="mt-2 border border-slate-400 p-2">
        <legend class="px-1 text-[11px] font-semibold">{{ $title }}</legend>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] border-collapse text-[11px]">
                <thead>
                    <tr class="bg-[#d8d8d8]">
                        <th class="border px-1 py-1">Count</th>
                        <th class="border px-1 py-1">Thread</th>
                        <th class="border px-1 py-1">Blend</th>
                        <th class="border px-1 py-1">Yarn</th>
                        <th class="border px-1 py-1">{{ $key === 'warp_details' ? 'Ends' : 'Picks' }}</th>
                        <th class="border px-1 py-1">Calc Count</th>
                        <th class="border px-1 py-1">Yarn Weight</th>
                        <th class="border px-1 py-1">Rate</th>
                        <th class="border px-1 py-1">Total Bags</th>
                        <th class="border px-1 py-1">Net Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $i => $row)
                        <tr>
                            <td class="border p-0.5"><select class="erp-input w-full" name="{{ $key }}[{{ $i }}][yarn_count_id]"><option value=""></option>@foreach($yarnCounts as $c)<option value="{{ $c->id }}" @selected((string)($row['yarn_count_id']??'')===(string)$c->id)>{{ $c->count }}</option>@endforeach</select></td>
                            <td class="border p-0.5"><select class="erp-input w-full" name="{{ $key }}[{{ $i }}][yarn_thread_id]"><option value=""></option>@foreach($yarnThreads ?? [] as $t)<option value="{{ $t->id }}" @selected((string)($row['yarn_thread_id']??'')===(string)$t->id)>{{ $t->thread }}</option>@endforeach</select></td>
                            <td class="border p-0.5"><select class="erp-input w-full" name="{{ $key }}[{{ $i }}][yarn_blend_id]"><option value=""></option>@foreach($yarnBlends ?? [] as $b)<option value="{{ $b->id }}" @selected((string)($row['yarn_blend_id']??'')===(string)$b->id)>{{ $b->blend }}</option>@endforeach</select></td>
                            <td class="border p-0.5"><input class="erp-input w-full" name="{{ $key }}[{{ $i }}][line_name]" value="{{ $row['line_name'] ?? '' }}"></td>
                            <td class="border p-0.5"><input class="erp-input w-full text-right" name="{{ $key }}[{{ $i }}][{{ $key === 'warp_details' ? 'ends' : 'picks' }}]" value="{{ $row[$key === 'warp_details' ? 'ends' : 'picks'] ?? '' }}"></td>
                            <td class="border p-0.5"><input class="erp-input w-full text-right" name="{{ $key }}[{{ $i }}][calc_count]" value="{{ $row['calc_count'] ?? '' }}"></td>
                            <td class="border p-0.5"><input class="erp-input w-full text-right" name="{{ $key }}[{{ $i }}][weight]" value="{{ $row['weight'] ?? '' }}"></td>
                            <td class="border p-0.5"><input class="erp-input w-full text-right" name="{{ $key }}[{{ $i }}][rate]" value="{{ $row['rate'] ?? '' }}"></td>
                            <td class="border p-0.5"><input class="erp-input w-full text-right" name="{{ $key }}[{{ $i }}][total_bags]" value="{{ $row['total_bags'] ?? '' }}"></td>
                            <td class="border p-0.5"><input class="erp-input w-full text-right" name="{{ $key }}[{{ $i }}][net_rate]" value="{{ $row['net_rate'] ?? '' }}"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </fieldset>
@endforeach
