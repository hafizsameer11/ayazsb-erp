@php
    $editingTransaction = $editingTransaction ?? null;
    $meta = $editingTransaction?->meta ?? [];
    $line = $editingTransaction?->lines->first();
    $lookups = $yarnFormLookups ?? [];
    $cancelUrl = route('erp.yarn.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
@endphp

<div class="erp-panel border border-slate-500 bg-white shadow-md">
    <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — YARN GODOWN TRANSFER</div>
    <form class="space-y-3 p-3" data-yarn-screen-form data-yarn-line-form data-erp-ajax-save @if($editingTransaction) data-erp-editing="1" @endif
        action="{{ $editingTransaction ? route('erp.yarn.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.yarn.screen.store', ['screen' => $screen['slug']]) }}" method="post">
        @csrf @if($editingTransaction) @method('PATCH') @endif
        <div data-erp-form-feedback class="hidden"></div>
        @if ($editingTransaction) @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingTransaction->trans_no, 'cancelUrl' => $cancelUrl]) @endif

        <fieldset class="border border-slate-400 bg-[#f4f4f4] p-2">
            <legend class="px-1 text-[11px] font-semibold">Master</legend>
            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                <label class="erp-field"><span class="erp-label">Trans Id</span><input class="erp-input bg-[#f0f0f0]" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
                <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
                <label class="erp-field"><span class="erp-label">Voucher Type</span><input class="erp-input bg-[#f0f0f0]" name="meta[voucher_type]" value="{{ old('meta.voucher_type', $meta['voucher_type'] ?? 'YGT') }}" readonly></label>
                <label class="erp-field md:col-span-2"><span class="erp-label">From Godown Id and Name</span>
                    <select class="erp-input" name="from_godown_id" required>
                        <option value=""></option>
                        @foreach($godowns as $g)<option value="{{ $g->id }}" @selected((string)old('from_godown_id',$editingTransaction?->from_godown_id)===(string)$g->id)>{{ $g->id }} — {{ $g->name }}</option>@endforeach
                    </select>
                </label>
                <label class="erp-field md:col-span-2"><span class="erp-label">To Godown Id and Name</span>
                    <select class="erp-input" name="to_godown_id" required>
                        <option value=""></option>
                        @foreach($godowns as $g)<option value="{{ $g->id }}" @selected((string)old('to_godown_id',$editingTransaction?->to_godown_id)===(string)$g->id)>{{ $g->id }} — {{ $g->name }}</option>@endforeach
                    </select>
                </label>
                @include('erp.yarn.partials.yarn-item-field', ['selected' => old('item_id', $line?->item_id), 'required' => true, 'class' => 'md:col-span-2'])
                <label class="erp-field"><span class="erp-label">Packing Size</span><input class="erp-input bg-slate-50" name="packing_size" data-yarn-packing-size value="{{ old('packing_size', $meta['packing_size'] ?? $line?->meta['packing_size'] ?? '') }}" readonly></label>
                <label class="erp-field"><span class="erp-label">Packing Weight</span><input class="erp-input" name="packing_weight" data-yarn-packing-weight type="number" step="0.0001" value="{{ old('packing_weight', $meta['packing_weight'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Packing Weight Unit</span>
                    <select class="erp-input" name="packing_unit">@foreach($lookups['packing_units'] ?? ['LBS'] as $u)<option value="{{ $u }}" @selected(old('packing_unit',$meta['packing_unit']??'LBS')===$u)>{{ $u }}</option>@endforeach</select>
                </label>
                <label class="erp-field"><span class="erp-label">Qty</span><input class="erp-input" name="quantity" data-yarn-bags type="number" step="0.0001" value="{{ old('quantity', $line?->qty) }}" required></label>
                <label class="erp-field"><span class="erp-label">Unit</span>
                    <select class="erp-input" name="qty_unit">@foreach($lookups['qty_units'] ?? ['BAGS'] as $u)<option value="{{ $u }}" @selected(old('qty_unit',$meta['qty_unit']??'BAGS')===$u)>{{ $u }}</option>@endforeach</select>
                </label>
                <label class="erp-field"><span class="erp-label">Cones</span><input class="erp-input" name="no_of_cones" data-yarn-cones type="number" step="0.0001" value="{{ old('no_of_cones', $meta['no_of_cones'] ?? $line?->meta['no_of_cones'] ?? 0) }}"></label>
                <label class="erp-field"><span class="erp-label">Yarn Tag</span>
                    <select class="erp-input" name="yarn_tag">@foreach($lookups['yarn_tags'] ?? ['FRESH'] as $t)<option value="{{ $t }}" @selected(old('yarn_tag',$meta['yarn_tag']??'FRESH')===$t)>{{ $t }}</option>@endforeach</select>
                </label>
                <label class="erp-field"><span class="erp-label">Rate / LBs</span><input class="erp-input" name="rate" data-yarn-rate type="number" step="0.0001" value="{{ old('rate', $line?->rate) }}" required></label>
                <label class="erp-field"><span class="erp-label">Total Weight Lbs</span><input class="erp-input bg-slate-50" name="meta[weight_lbs_display]" data-yarn-weight-lbs readonly value="{{ old('meta.weight_lbs_display', $line?->weight_lbs) }}"></label>
                <label class="erp-field"><span class="erp-label">Total Weight Kg</span><input class="erp-input bg-slate-50" data-yarn-total-kgs readonly value="{{ old('meta.total_kgs', $meta['total_kgs'] ?? $line?->meta['total_kgs'] ?? '') }}"></label>
                <label class="erp-field"><span class="erp-label">Total Net Amnt</span><input class="erp-input bg-slate-50" data-yarn-amount readonly value="{{ old('meta.total_amount', $line?->amount) }}"></label>
                <label class="erp-field md:col-span-4"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
            </div>
        </fieldset>
        <input type="hidden" name="submit_action" value="post">
        <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold">{{ $editingTransaction ? 'Update' : 'Save' }}</button>
    </form>
    @include('erp.yarn.partials.recent-transactions')
</div>
