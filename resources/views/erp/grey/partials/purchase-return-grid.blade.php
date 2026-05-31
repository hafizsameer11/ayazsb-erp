@php
    $returnLines = $returnLines ?? collect();
    $returnRows = [];
    foreach ($returnLines as $line) {
        $returnRows[] = $line;
    }
    while (count($returnRows) < 4) {
        $returnRows[] = null;
    }
@endphp
<fieldset class="border border-slate-400 bg-[#f7f7f7] p-2">
    <legend class="px-1 text-[11px] font-semibold">Purchase Return</legend>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1100px] border-collapse text-[11px]">
            <thead>
                <tr class="bg-[#d8d8d8]">
                    <th class="border border-slate-400 px-1 py-1">Return Date</th>
                    <th class="border border-slate-400 px-1 py-1">Stock Id</th>
                    <th class="border border-slate-400 px-1 py-1">Quality Code</th>
                    <th class="border border-slate-400 px-1 py-1">Qty</th>
                    <th class="border border-slate-400 px-1 py-1">Rate</th>
                    <th class="border border-slate-400 px-1 py-1">Total Gross Amnt</th>
                    <th class="border border-slate-400 px-1 py-1">Comm</th>
                    <th class="border border-slate-400 px-1 py-1">Brokery</th>
                    <th class="border border-slate-400 px-1 py-1">Checkary</th>
                    <th class="border border-slate-400 px-1 py-1">Munshiana</th>
                    <th class="border border-slate-400 px-1 py-1">Total Net Amnt</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($returnRows as $i => $retLine)
                    @php $idx = $i + 1; $rowMeta = $retLine?->meta ?? []; @endphp
                    <tr>
                        <td class="border border-slate-300 p-0.5"><x-erp-date-input class="erp-input w-full" :name="'lines['.$idx.'][meta][return_date]'" :value="old('lines.'.$idx.'.meta.return_date', $rowMeta['return_date'] ?? '')" :default-blank="true" /></td>
                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="lines[{{ $idx }}][meta][stock_id]" value="{{ old('lines.'.$idx.'.meta.stock_id', $rowMeta['stock_id'] ?? '') }}"></td>
                        <td class="border border-slate-300 p-0.5">
                            <select class="erp-input w-full font-mono" name="lines[{{ $idx }}][meta][grey_quality_id]">
                                <option value=""></option>
                                @foreach ($greyQualities as $q)
                                    <option value="{{ $q->id }}" @selected((string) old('lines.'.$idx.'.meta.grey_quality_id', $rowMeta['grey_quality_id'] ?? '') === (string) $q->id)>{{ $q->quality_no }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $idx }}][qty]" type="number" step="0.01" value="{{ old('lines.'.$idx.'.qty', $retLine?->qty) }}"></td>
                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $idx }}][rate]" type="number" step="0.01" value="{{ old('lines.'.$idx.'.rate', $retLine?->rate) }}"></td>
                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $idx }}][meta][total_gross]" value="{{ old('lines.'.$idx.'.meta.total_gross', $rowMeta['total_gross'] ?? '') }}"></td>
                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $idx }}][meta][commission]" value="{{ old('lines.'.$idx.'.meta.commission', $rowMeta['commission'] ?? '') }}"></td>
                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $idx }}][meta][brokery]" value="{{ old('lines.'.$idx.'.meta.brokery', $rowMeta['brokery'] ?? '') }}"></td>
                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $idx }}][meta][checkary]" value="{{ old('lines.'.$idx.'.meta.checkary', $rowMeta['checkary'] ?? '') }}"></td>
                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $idx }}][meta][munshiana]" value="{{ old('lines.'.$idx.'.meta.munshiana', $rowMeta['munshiana'] ?? '') }}"></td>
                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $idx }}][amount]" type="number" step="0.01" value="{{ old('lines.'.$idx.'.amount', $retLine?->amount) }}"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('erp.grey.partials.voucher-strip', ['meta' => $meta ?? [], 'prefix' => 'meta[return_voucher]'])
    <div class="mt-2 flex justify-end gap-2">
        <button type="button" class="rounded border border-slate-500 bg-slate-100 px-2 py-1 text-[11px]">Voucher Post</button>
        <button type="button" class="rounded border border-slate-500 bg-slate-100 px-2 py-1 text-[11px]">Voucher Print</button>
    </div>
</fieldset>
