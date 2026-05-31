@php
    $side = $side ?? 'gain';
    $title = $side === 'gain' ? 'YARN GAIN' : 'YARN SHORTAGE';
    $idLabel = $side === 'gain' ? 'Gain ID' : 'Short ID';
    $forLabel = $side === 'gain' ? 'Gain For' : 'Short For';
    $voucherType = 'YGSV';
    $editingTransaction = ($editingTransaction ?? null) && strtolower((string)(($editingTransaction->meta ?? [])['adjustment_type'] ?? '')) === $side ? $editingTransaction : null;
    $meta = $editingTransaction?->meta ?? [];
    $line = $editingTransaction?->lines->first();
    $lookups = $yarnFormLookups ?? [];
@endphp

<div class="erp-panel border border-slate-500 bg-white shadow-md">
    <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — {{ $title }}</div>
    <form class="space-y-3 p-3" data-yarn-screen-form data-yarn-line-form data-erp-ajax-save @if($editingTransaction) data-erp-editing="1" @endif
        action="{{ $editingTransaction ? route('erp.yarn.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.yarn.screen.store', ['screen' => $screen['slug']]) }}" method="post">
        @csrf @if($editingTransaction) @method('PATCH') @endif
        <input type="hidden" name="adjustment_type" value="{{ $side }}">
        <div data-erp-form-feedback class="hidden"></div>

        <fieldset class="border border-slate-400 bg-[#f4f4f4] p-2">
            <legend class="px-1 text-[11px] font-semibold">{{ $title }}</legend>
            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                <label class="erp-field"><span class="erp-label">{{ $idLabel }}</span><input class="erp-input bg-[#f0f0f0]" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
                <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
                <label class="erp-field"><span class="erp-label">{{ $forLabel }}</span>
                    <select class="erp-input" name="gain_short_for">@foreach($lookups['gain_short_for'] ?? ['LOOMS'] as $v)<option value="{{ $v }}" @selected(old('gain_short_for',$meta['gain_short_for']??'LOOMS')===$v)>{{ $v }}</option>@endforeach</select>
                </label>
                <label class="erp-field md:col-span-2"><span class="erp-label">Party</span>
                    <select class="erp-input" name="account_id" required>
                        <option value=""></option>
                        @foreach($accountParties as $a)<option value="{{ $a->id }}" @selected((string)old('account_id',$editingTransaction?->account_id)===(string)$a->id)>{{ $a->code }} — {{ $a->name }}</option>@endforeach
                    </select>
                </label>
                @if($side === 'shortage')
                    <label class="erp-field md:col-span-2"><span class="erp-label">Issue Id</span>
                        <select class="erp-input" name="source_transaction_id">
                            <option value=""></option>
                            @foreach($yarnIssuanceOptions ?? [] as $issue)
                                <option value="{{ $issue['id'] }}" data-payload="{{ json_encode($issue) }}" @selected((string)old('source_transaction_id',$editingTransaction?->source_transaction_id)===(string)$issue['id'])>{{ $issue['trans_no'] }} — {{ $issue['contract_code'] }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
                @include('erp.yarn.partials.yarn-item-field', ['selected' => old('item_id', $line?->item_id), 'required' => true, 'class' => 'md:col-span-2', 'readonly' => $side === 'shortage'])
                <label class="erp-field"><span class="erp-label">Pack Size (Cones)</span><input class="erp-input bg-slate-50" name="packing_size" data-yarn-packing-size value="{{ old('packing_size', $meta['packing_size'] ?? $line?->meta['packing_size'] ?? '') }}" readonly></label>
                <label class="erp-field"><span class="erp-label">Packing Weight</span><input class="erp-input" name="packing_weight" data-yarn-packing-weight type="number" step="0.0001" value="{{ old('packing_weight', $meta['packing_weight'] ?? '100') }}"></label>
                <label class="erp-field"><span class="erp-label">Packing Weight Unit</span>
                    <select class="erp-input" name="packing_unit">@foreach($lookups['packing_units'] ?? ['LBS'] as $u)<option value="{{ $u }}" @selected(old('packing_unit',$meta['packing_unit']??'LBS')===$u)>{{ $u }}</option>@endforeach</select>
                </label>
                <label class="erp-field"><span class="erp-label">Yarn Use</span>
                    <select class="erp-input" name="yarn_use">@foreach($lookups['yarn_uses'] ?? ['any'] as $u)<option value="{{ $u }}" @selected(old('yarn_use',$meta['yarn_use']??'any')===$u)>{{ strtoupper($u) }}</option>@endforeach</select>
                </label>
                <label class="erp-field"><span class="erp-label">Yarn Condition</span>
                    <select class="erp-input" name="yarn_condition">@foreach($lookups['yarn_conditions'] ?? ['FRESH'] as $c)<option value="{{ $c }}" @selected(old('yarn_condition',$meta['yarn_condition']??'FRESH')===$c)>{{ $c }}</option>@endforeach</select>
                </label>
                @if($side === 'gain')
                    <label class="erp-field"><span class="erp-label">Contract Id</span>
                        <select class="erp-input" name="yarn_contract_id">
                            <option value="0"></option>
                            @foreach($yarnContractsPayload ?? [] as $c)<option value="{{ $c['id'] }}" @selected((string)old('yarn_contract_id',$editingTransaction?->yarn_contract_id)===(string)$c['id'])>{{ $c['lov_label'] }}</option>@endforeach
                        </select>
                    </label>
                @else
                    <label class="erp-field"><span class="erp-label">Reverse Count</span><input class="erp-input" name="meta[reverse_count]" type="number" step="0.0001" value="{{ old('meta.reverse_count', $meta['reverse_count'] ?? '') }}"></label>
                @endif
                <label class="erp-field"><span class="erp-label">Qty</span><input class="erp-input" name="quantity" data-yarn-bags type="number" step="0.0001" value="{{ old('quantity', $line?->qty) }}" required></label>
                <label class="erp-field"><span class="erp-label">Unit</span>
                    <select class="erp-input" name="qty_unit">@foreach($lookups['qty_units'] ?? ['BAGS'] as $u)<option value="{{ $u }}" @selected(old('qty_unit',$meta['qty_unit']??'BAGS')===$u)>{{ $u }}</option>@endforeach</select>
                </label>
                <label class="erp-field"><span class="erp-label">Rate</span><input class="erp-input" name="rate" data-yarn-rate type="number" step="0.0001" value="{{ old('rate', $line?->rate) }}" @if($side==='shortage') readonly @endif required></label>
                <label class="erp-field"><span class="erp-label">Total Weight Lbs</span><input class="erp-input bg-slate-50" data-yarn-weight-lbs readonly value="{{ old('meta.weight_lbs', $line?->weight_lbs) }}"></label>
                <label class="erp-field"><span class="erp-label">Total Weight Kg</span><input class="erp-input bg-slate-50" data-yarn-total-kgs readonly value="{{ old('meta.total_kgs', $meta['total_kgs'] ?? $line?->meta['total_kgs'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Total {{ $side === 'gain' ? 'Gross' : '' }} Amnt</span><input class="erp-input bg-slate-50" data-yarn-amount readonly value="{{ old('meta.total_amount', $line?->amount) }}"></label>
                <label class="erp-field"><span class="erp-label">Voucher Type</span><input class="erp-input bg-[#f0f0f0]" name="meta[voucher_type]" value="{{ old('meta.voucher_type', $meta['voucher_type'] ?? $voucherType) }}" readonly></label>
                <label class="erp-field md:col-span-4"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
            </div>
        </fieldset>
        <input type="hidden" name="submit_action" value="post">
        <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold">Save {{ ucfirst($side) }}</button>
    </form>
</div>
