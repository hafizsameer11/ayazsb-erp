@php
    $lines = collect($editingTransaction?->lines ?? []);
    $rowCount = max(8, $lines->count() + 2);
    $blankRow = ['item_id' => '', 'description' => '', 'qty' => '', 'rate' => '', 'amount' => '', 'meta' => []];
    $rows = $lines->map(fn ($l) => [
        'item_id' => $l->item_id,
        'description' => $l->description,
        'qty' => $l->qty,
        'rate' => $l->rate,
        'amount' => $l->amount,
        'meta' => $l->meta ?? [],
    ])->all();
    while (count($rows) < $rowCount) {
        $rows[] = $blankRow;
    }
    $stockMap = $storeStockMap ?? [];
@endphp
<div class="overflow-x-auto border border-slate-400">
    <table class="w-full min-w-[900px] border-collapse text-[11px]" data-erp-detail-lines data-weaving-store-grid>
        <thead class="bg-[#d8d8d8]">
            <tr>
                <th class="border border-slate-400 px-1 py-1">Item</th>
                <th class="border border-slate-400 px-1 py-1">UOM</th>
                <th class="border border-slate-400 px-1 py-1">Stock</th>
                <th class="border border-slate-400 px-1 py-1">Qty</th>
                <th class="border border-slate-400 px-1 py-1">Rate</th>
                <th class="border border-slate-400 px-1 py-1">Amount</th>
                <th class="border border-slate-400 px-1 py-1">CC</th>
                <th class="border border-slate-400 px-1 py-1">Issue As</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                @php $item = ($storeItems ?? collect())->firstWhere('id', $row['item_id']); @endphp
                <tr data-erp-detail-line>
                    <td class="border border-slate-300 p-0.5">
                        <select class="erp-input w-full" name="lines[{{ $i }}][item_id]" data-weaving-item-select data-stock-map='@json($stockMap)'>
                            <option value="">—</option>
                            @foreach ($storeItems as $si)
                                <option value="{{ $si->id }}" data-unit="{{ $si->unit }}" @selected($row['item_id'] == $si->id)>{{ $si->code }} — {{ $si->name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="lines[{{ $i }}][description]" value="{{ $row['description'] }}">
                    </td>
                    <td class="border border-slate-300 px-1"><span data-weaving-uom>{{ $item?->unit ?? '' }}</span></td>
                    <td class="border border-slate-300 px-1 text-right"><span data-weaving-stock>{{ $row['item_id'] ? ($stockMap[$row['item_id']] ?? 0) : '' }}</span></td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][qty]" value="{{ $row['qty'] }}" data-weaving-qty></td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][rate]" value="{{ $row['rate'] }}" data-weaving-rate></td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right bg-[#f8f8f8]" name="lines[{{ $i }}][amount]" value="{{ $row['amount'] }}" data-weaving-amount readonly></td>
                    <td class="border border-slate-300 p-0.5">
                        @include('erp.grey.partials.code-name-pair', [
                            'selectName' => "lines[{$i}][meta][cc_account_id]",
                            'selectedId' => old("lines.{$i}.meta.cc_account_id", $row['meta']['cc_account_id'] ?? ''),
                            'options' => $accountParties,
                            'targetId' => 'weaving-cc-' . $i,
                        ])
                    </td>
                    <td class="border border-slate-300 p-0.5">
                        <select class="erp-input w-full" name="lines[{{ $i }}][meta][issue_as]">
                            @foreach (['Consumption', 'Asset', 'Repair'] as $opt)
                                <option value="{{ $opt }}" @selected(($row['meta']['issue_as'] ?? 'Consumption') === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-[#ececec]">
            <tr>
                <td colspan="3" class="border border-slate-400 px-2 py-1 text-right font-semibold">Totals</td>
                <td class="border border-slate-400 px-1 text-right font-semibold" data-weaving-total-qty>0</td>
                <td class="border border-slate-400"></td>
                <td class="border border-slate-400 px-1 text-right font-semibold" data-weaving-total-amount>0</td>
                <td colspan="2" class="border border-slate-400"></td>
            </tr>
        </tfoot>
    </table>
</div>
