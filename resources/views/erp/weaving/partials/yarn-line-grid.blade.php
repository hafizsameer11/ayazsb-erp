@php
    $lines = collect($editingTransaction?->lines ?? []);
    $meta = $meta ?? ($editingTransaction?->meta ?? []);
    $rowCount = max(8, $lines->count() + 2);
    $blankRow = ['item_id' => '', 'qty' => '', 'rate' => '', 'amount' => '', 'meta' => []];
    $rows = $lines->map(fn ($l) => [
        'item_id' => $l->item_id,
        'qty' => $l->qty,
        'rate' => $l->rate,
        'amount' => $l->amount,
        'meta' => $l->meta ?? [],
    ])->all();
    while (count($rows) < $rowCount) {
        $rows[] = $blankRow;
    }
    $stockMap = match ($yarnPool ?? 'stock') {
        'sizing' => $sizingStockMap ?? [],
        'production' => $productionStockMap ?? [],
        default => $yarnStockMap ?? [],
    };
    $showContract = in_array($screen['slug'], ['yarn-issuance-to-sizing', 'yarn-issuance-stock-to-production'], true);
@endphp
<div class="grid gap-2 md:grid-cols-4 lg:grid-cols-6">
    @if ($screen['slug'] === 'yarn-issuance-to-sizing')
        <label class="erp-field md:col-span-2"><span class="erp-label">Sizing Party Code</span>
            @include('erp.grey.partials.code-name-pair', [
                'selectName' => 'meta[sizing_party_account_id]',
                'selectedId' => old('meta.sizing_party_account_id', $meta['sizing_party_account_id'] ?? $editingTransaction?->meta['sizing_party_account_id'] ?? ''),
                'options' => $accountParties,
                'required' => true,
                'targetId' => 'weaving-sizing-party-yarn',
            ])
        </label>
    @endif
    @if ($showContract)
        <label class="erp-field md:col-span-2"><span class="erp-label">Contract</span>
            <select class="erp-input" name="grey_conversion_contract_id">
                <option value="">—</option>
                @foreach ($conversionContracts as $c)
                    <option value="{{ $c->id }}" @selected(old('grey_conversion_contract_id', $editingTransaction?->grey_conversion_contract_id) == $c->id)>{{ $c->contract_no }} — {{ $c->quality?->quality_name }}</option>
                @endforeach
            </select>
        </label>
    @endif
    @if ($screen['slug'] === 'yarn-return-stock-to-party')
        <label class="erp-field md:col-span-2"><span class="erp-label">Party Code</span>
            @include('erp.grey.partials.code-name-pair', [
                'selectName' => 'account_id',
                'selectedId' => old('account_id', $editingTransaction?->account_id),
                'options' => $accountParties,
                'required' => true,
                'targetId' => 'weaving-yarn-party',
            ])
        </label>
    @endif
</div>
<div class="overflow-x-auto border border-slate-400">
    <table class="w-full min-w-[800px] border-collapse text-[11px]" data-erp-detail-lines data-weaving-yarn-grid>
        <thead class="bg-[#d8d8d8]">
            <tr>
                <th class="border border-slate-400 px-1 py-1">Yarn</th>
                <th class="border border-slate-400 px-1 py-1">Brand</th>
                <th class="border border-slate-400 px-1 py-1">Stock</th>
                <th class="border border-slate-400 px-1 py-1">Qty</th>
                <th class="border border-slate-400 px-1 py-1">Rate</th>
                <th class="border border-slate-400 px-1 py-1">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr data-erp-detail-line>
                    <td class="border border-slate-300 p-0.5">
                        <select class="erp-input w-full" name="lines[{{ $i }}][item_id]" data-weaving-item-select data-stock-map='@json($stockMap)'>
                            <option value="">—</option>
                            @foreach ($yarnItems as $yi)
                                <option value="{{ $yi->id }}" @selected($row['item_id'] == $yi->id)>{{ $yi->code }} — {{ $yi->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][meta][brand]" value="{{ $row['meta']['brand'] ?? '' }}"></td>
                    <td class="border border-slate-300 px-1 text-right"><span data-weaving-stock>{{ $row['item_id'] ? ($stockMap[$row['item_id']] ?? 0) : '' }}</span></td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][qty]" value="{{ $row['qty'] }}" data-weaving-qty></td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][rate]" value="{{ $row['rate'] }}" data-weaving-rate></td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right bg-[#f8f8f8]" name="lines[{{ $i }}][amount]" value="{{ $row['amount'] }}" data-weaving-amount readonly></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-[#ececec]">
            <tr>
                <td colspan="3" class="border border-slate-400 px-2 py-1 text-right font-semibold">Totals</td>
                <td class="border border-slate-400 px-1 text-right font-semibold" data-weaving-total-qty>0</td>
                <td class="border border-slate-400"></td>
                <td class="border border-slate-400 px-1 text-right font-semibold" data-weaving-total-amount>0</td>
            </tr>
        </tfoot>
    </table>
</div>
