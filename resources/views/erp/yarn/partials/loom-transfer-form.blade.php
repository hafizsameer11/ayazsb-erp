@php
    $editingTransaction = $editingTransaction ?? null;
    $meta = $editingTransaction?->meta ?? [];
    $line = $editingTransaction?->lines->first();
    $lookups = $yarnFormLookups ?? [];
    $cancelUrl = route('erp.yarn.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
@endphp

<div class="erp-panel border border-slate-500 bg-white shadow-md">
    <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — LOOM YARN TRANSFER (FOR WARPING)</div>
    <form class="space-y-3 p-3" data-yarn-screen-form data-yarn-line-form data-erp-ajax-save @if($editingTransaction) data-erp-editing="1" @endif
        action="{{ $editingTransaction ? route('erp.yarn.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.yarn.screen.store', ['screen' => $screen['slug']]) }}" method="post">
        @csrf @if($editingTransaction) @method('PATCH') @endif
        <div data-erp-form-feedback class="hidden"></div>
        @if ($editingTransaction) @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingTransaction->trans_no, 'cancelUrl' => $cancelUrl]) @endif

        <fieldset class="border border-slate-400 bg-[#f4f4f4] p-2">
            <legend class="px-1 text-[11px] font-semibold">Master</legend>
            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                <label class="erp-field"><span class="erp-label">Transfer #</span><input class="erp-input bg-[#f0f0f0]" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
                <label class="erp-field"><span class="erp-label">Transfer Dated</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
                <label class="erp-field"><span class="erp-label">Transfer From</span>
                    <select class="erp-input" name="transfer_from" required>@foreach($lookups['transfer_from'] ?? ['ISSUE FOR LOOMS'] as $v)<option value="{{ $v }}" @selected(old('transfer_from',$meta['transfer_from']??'ISSUE FOR LOOMS')===$v)>{{ $v }}</option>@endforeach</select>
                </label>
                <label class="erp-field md:col-span-2"><span class="erp-label">Party Code and Name</span>
                    <select class="erp-input" name="account_id" required>
                        <option value=""></option>
                        @foreach($accountParties as $a)<option value="{{ $a->id }}" @selected((string)old('account_id',$editingTransaction?->account_id)===(string)$a->id)>{{ $a->code }} — {{ $a->name }}</option>@endforeach
                    </select>
                </label>
                @include('erp.yarn.partials.yarn-item-field', ['selected' => old('item_id', $line?->item_id), 'required' => true, 'class' => 'md:col-span-2'])
                <label class="erp-field"><span class="erp-label">To Yarn Use</span>
                    <select class="erp-input" name="to_yarn_use">@foreach($lookups['yarn_uses'] ?? ['warp'] as $u)<option value="{{ $u }}" @selected(old('to_yarn_use',$meta['to_yarn_use']??'warp')===$u)>{{ strtoupper($u) }}</option>@endforeach</select>
                </label>
                <label class="erp-field"><span class="erp-label">Qty</span><input class="erp-input" name="quantity" data-yarn-bags type="number" step="0.0001" value="{{ old('quantity', $line?->qty) }}" required></label>
                <label class="erp-field"><span class="erp-label">Rate</span><input class="erp-input" name="rate" data-yarn-rate type="number" step="0.0001" value="{{ old('rate', $line?->rate) }}" required></label>
                <label class="erp-field"><span class="erp-label">Yarn Tag</span>
                    <select class="erp-input" name="yarn_tag">@foreach($lookups['yarn_tags'] ?? ['FRESH'] as $t)<option value="{{ $t }}" @selected(old('yarn_tag',$meta['yarn_tag']??'FRESH')===$t)>{{ $t }}</option>@endforeach</select>
                </label>
                <label class="erp-field md:col-span-2"><span class="erp-label">Contract Id</span>
                    <select class="erp-input" name="yarn_contract_id">
                        <option value=""></option>
                        @foreach($yarnContractsPayload ?? [] as $c)
                            <option value="{{ $c['id'] }}" data-payload="{{ json_encode($c) }}" @selected((string)old('yarn_contract_id',$editingTransaction?->yarn_contract_id)===(string)$c['id'])>{{ $c['lov_label'] }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field md:col-span-4"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
            </div>
        </fieldset>
        <input type="hidden" name="submit_action" value="post">
        <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold">{{ $editingTransaction ? 'Update' : 'Save' }}</button>
    </form>
    @include('erp.yarn.partials.recent-transactions')
</div>
