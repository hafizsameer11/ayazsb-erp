@php
    $editingTransaction = $editingTransaction ?? null;
    $cancelUrl = route('erp.yarn.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
    $editingLines = $editingTransaction?->lines ?? collect();
    $rows = [];
    foreach ($editingLines as $line) {
        $rows[] = [
            'item_id' => $line->item_id,
            'yarn_type' => $line->meta['yarn_type'] ?? 'any',
            'packing_size' => $line->meta['packing_size'] ?? $line->item?->pack_size_cones,
            'qty' => $line->qty,
            'no_of_cones' => $line->meta['no_of_cones'] ?? $line->meta['cones'] ?? 0,
            'weight_lbs' => $line->weight_lbs,
            'total_kgs' => $line->meta['total_kgs'] ?? '',
            'rate' => $line->rate,
            'amount' => $line->amount,
        ];
    }
    while (count($rows) < 8) {
        $rows[] = ['item_id' => '', 'yarn_type' => 'any', 'packing_size' => '', 'qty' => '', 'no_of_cones' => '', 'weight_lbs' => '', 'total_kgs' => '', 'rate' => '', 'amount' => ''];
    }
@endphp

<div class="erp-panel border border-slate-500 bg-white shadow-md">
    <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — YARN OPENING</div>
    <form class="space-y-3 p-3" data-yarn-line-form data-erp-ajax-save @if($editingTransaction) data-erp-editing="1" @endif
        action="{{ $editingTransaction ? route('erp.yarn.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.yarn.screen.store', ['screen' => $screen['slug']]) }}" method="post">
        @csrf
        @if($editingTransaction) @method('PATCH') @endif
        <div data-erp-form-feedback class="hidden"></div>
        @if ($editingTransaction) @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingTransaction->trans_no, 'cancelUrl' => $cancelUrl]) @endif

        <fieldset class="border border-slate-400 bg-[#f4f4f4] p-2">
            <legend class="px-1 text-[11px] font-semibold">Master</legend>
            <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                <label class="erp-field"><span class="erp-label">Trans ID</span><input class="erp-input bg-[#f0f0f0]" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
                <label class="erp-field"><span class="erp-label">Date</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
                <label class="erp-field md:col-span-2"><span class="erp-label">Party ID and Name</span>
                    <select class="erp-input" name="account_id" required>
                        <option value=""></option>
                        @foreach($accountParties as $a)<option value="{{ $a->id }}" @selected((string)old('account_id',$editingTransaction?->account_id)===(string)$a->id)>{{ $a->code }} — {{ $a->name }}</option>@endforeach
                    </select>
                </label>
                <label class="erp-field md:col-span-2"><span class="erp-label">Godown Id and Name</span>
                    <select class="erp-input" name="from_godown_id" required>
                        <option value=""></option>
                        @foreach($godowns as $g)<option value="{{ $g->id }}" @selected((string)old('from_godown_id',$editingTransaction?->from_godown_id)===(string)$g->id)>{{ $g->id }} — {{ $g->name }}</option>@endforeach
                    </select>
                </label>
                <label class="erp-field md:col-span-4"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
            </div>
        </fieldset>

        <fieldset class="border border-slate-400 p-2">
            <legend class="px-1 text-[11px] font-semibold">Opening lines</legend>
            <table class="w-full min-w-[1000px] border-collapse text-[11px]">
                <thead><tr class="bg-[#d8d8d8]">
                    <th class="border px-1 py-1">Yarn Id</th><th class="border px-1 py-1">Yarn Type</th><th class="border px-1 py-1">Packing Size</th>
                    <th class="border px-1 py-1">No of Bags</th><th class="border px-1 py-1">No of Cones</th><th class="border px-1 py-1">Total LBs</th>
                    <th class="border px-1 py-1">Total KGs</th><th class="border px-1 py-1">Rate / LBs</th><th class="border px-1 py-1">Total Amount</th>
                </tr></thead>
                <tbody>
                    @foreach($rows as $i => $row)
                        <tr data-yarn-line-row>
                            <td class="border p-0.5"><select class="erp-input w-full" name="lines[{{ $i }}][item_id]" data-yarn-item-select><option value=""></option>@foreach($items as $item)<option value="{{ $item->id }}" @selected((string)$row['item_id']===(string)$item->id)>{{ $item->code }}</option>@endforeach</select></td>
                            <td class="border p-0.5"><select class="erp-input w-full" name="lines[{{ $i }}][meta][yarn_type]">@foreach(['any','warp','weft'] as $t)<option value="{{ $t }}" @selected(($row['yarn_type']??'any')===$t)>{{ strtoupper($t) }}</option>@endforeach</select></td>
                            <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="lines[{{ $i }}][meta][packing_size]" data-yarn-packing-size value="{{ $row['packing_size'] }}" readonly></td>
                            <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][qty]" type="number" step="0.0001" data-yarn-bags value="{{ $row['qty'] }}"></td>
                            <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][meta][no_of_cones]" type="number" step="0.0001" data-yarn-cones value="{{ $row['no_of_cones'] }}"></td>
                            <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="lines[{{ $i }}][weight_lbs]" data-yarn-weight-lbs value="{{ $row['weight_lbs'] }}" readonly></td>
                            <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="lines[{{ $i }}][meta][total_kgs]" data-yarn-total-kgs value="{{ $row['total_kgs'] }}" readonly></td>
                            <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][rate]" type="number" step="0.0001" data-yarn-rate value="{{ $row['rate'] }}"></td>
                            <td class="border p-0.5"><input class="erp-input w-full bg-slate-50" name="lines[{{ $i }}][amount]" data-yarn-amount value="{{ $row['amount'] }}" readonly></td>
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
