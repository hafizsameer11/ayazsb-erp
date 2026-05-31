@php
    $detailLines = $detailLines ?? collect();
    $rows = $detailLines->values()->all();
    while (count($rows) < 2) {
        $rows[] = null;
    }
    $line0 = $line0 ?? null;
    $meta = $meta ?? [];
    $greyTags = ['FRESH', 'SECOND', 'REJECTION'];
@endphp
<fieldset class="border border-slate-400 bg-[#f7f7f7] p-2">
    <legend class="px-1 text-[11px] font-semibold">Grey Sale Details</legend>
    <table class="w-full border-collapse text-[11px]">
        <thead><tr class="bg-[#d8d8d8]"><th class="border px-1 py-1">Godown</th><th class="border px-1 py-1">Quality Code</th><th class="border px-1 py-1">Qty</th><th class="border px-1 py-1">Rate</th><th class="border px-1 py-1">Total</th></tr></thead>
        <tbody>
            @foreach ($rows as $i => $line)
                @php $idx = $i + 1; @endphp
                <tr>
                    <td class="border p-0.5">@include('erp.grey.partials.godown-pair', ['selectName' => 'lines['.$idx.'][meta][godown_id]', 'selectedId' => old('lines.'.$idx.'.meta.godown_id', $line?->meta['godown_id'] ?? ''), 'targetId' => 'sale-detail-godown-'.$idx])</td>
                    <td class="border p-0.5">@include('erp.grey.partials.quality-pair', ['selectName' => 'lines['.$idx.'][meta][grey_quality_id]', 'selectedId' => old('lines.'.$idx.'.meta.grey_quality_id', $line?->meta['grey_quality_id'] ?? ''), 'targetId' => 'sale-detail-quality-'.$idx])</td>
                    <td class="border p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $idx }}][qty]" type="number" step="0.01" value="{{ old('lines.'.$idx.'.qty', $line?->qty) }}"></td>
                    <td class="border p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $idx }}][rate]" type="number" step="0.01" value="{{ old('lines.'.$idx.'.rate', $line?->rate) }}"></td>
                    <td class="border p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $idx }}][amount]" type="number" step="0.01" value="{{ old('lines.'.$idx.'.amount', $line?->amount) }}"></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-2 grid gap-2 md:grid-cols-2 lg:grid-cols-4">
        <label class="erp-field lg:col-span-2"><span class="erp-label">Actual Qty</span><input class="erp-input" name="meta[actual_qty]" value="{{ old('meta.actual_qty', $meta['actual_qty'] ?? '') }}"></label>
        <label class="erp-field"><span class="erp-label">Loom type</span><input class="erp-input" name="meta[detail_loom_type]" value="{{ old('meta.detail_loom_type', $meta['detail_loom_type'] ?? '') }}"></label>
        <label class="erp-field lg:col-span-2"><span class="erp-label">Lot Party</span>@include('erp.grey.partials.code-name-pair', ['selectName' => 'meta[lot_party_account_id]', 'selectedId' => old('meta.lot_party_account_id', $meta['lot_party_account_id'] ?? ''), 'options' => $accountParties, 'targetId' => 'sale-lot-party'])</label>
        <label class="erp-field"><span class="erp-label">Lot #</span><input class="erp-input" name="meta[lot_no]" value="{{ old('meta.lot_no', $meta['lot_no'] ?? '') }}"></label>
        <label class="erp-field"><span class="erp-label">Grey Tag</span>
            <select class="erp-input" name="meta[grey_tag]">
                @foreach ($greyTags as $tag)
                    <option value="{{ $tag }}" @selected(old('meta.grey_tag', $meta['grey_tag'] ?? 'FRESH') === $tag)>{{ $tag }}</option>
                @endforeach
            </select>
        </label>
    </div>
</fieldset>
