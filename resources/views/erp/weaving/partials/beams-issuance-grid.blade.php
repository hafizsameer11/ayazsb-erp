@php
    $lines = collect($editingTransaction?->lines ?? []);
    $rowCount = max(6, $lines->count() + 2);
    $blankRow = ['meta' => []];
    $rows = $lines->map(fn ($l) => ['meta' => $l->meta ?? [], 'qty' => $l->qty])->all();
    while (count($rows) < $rowCount) {
        $rows[] = $blankRow;
    }
    $beamOptions = collect($weavingSets ?? [])->flatMap(function ($set) {
        return $set->beams->map(fn ($beam) => [
            'id' => $beam->id,
            'label' => $set->set_no . ' / ' . $beam->beam_no,
            'set_id' => $set->id,
            'contract_id' => $set->grey_conversion_contract_id,
            'quality_id' => $set->grey_quality_id,
            'width' => $set->width,
            'length' => $beam->beam_length,
        ]);
    });
@endphp
<div class="overflow-x-auto border border-slate-400">
    <table class="w-full min-w-[1100px] border-collapse text-[11px]" data-erp-detail-lines>
        <thead class="bg-[#d8d8d8]">
            <tr>
                <th class="border border-slate-400 px-1 py-1">Contract</th>
                <th class="border border-slate-400 px-1 py-1">Set</th>
                <th class="border border-slate-400 px-1 py-1">Quality</th>
                <th class="border border-slate-400 px-1 py-1">Width</th>
                <th class="border border-slate-400 px-1 py-1">Beam</th>
                <th class="border border-slate-400 px-1 py-1">Length</th>
                <th class="border border-slate-400 px-1 py-1">Loom</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                @php $meta = $row['meta'] ?? []; @endphp
                <tr data-erp-detail-line>
                    <td class="border border-slate-300 p-0.5">
                        <select class="erp-input w-full" name="lines[{{ $i }}][meta][grey_conversion_contract_id]">
                            <option value="">—</option>
                            @foreach ($conversionContracts as $c)
                                <option value="{{ $c->id }}" @selected(($meta['grey_conversion_contract_id'] ?? '') == $c->id)>{{ $c->contract_no }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][meta][set_no]" value="{{ $meta['set_no'] ?? '' }}"></td>
                    <td class="border border-slate-300 p-0.5">
                        <select class="erp-input w-full" name="lines[{{ $i }}][meta][grey_quality_id]">
                            <option value="">—</option>
                            @foreach ($greyQualities as $q)
                                <option value="{{ $q->id }}" @selected(($meta['grey_quality_id'] ?? '') == $q->id)>{{ $q->quality_no }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][meta][width]" value="{{ $meta['width'] ?? '' }}"></td>
                    <td class="border border-slate-300 p-0.5">
                        <select class="erp-input w-full" name="lines[{{ $i }}][meta][beam_id]" data-beam-select>
                            <option value="">—</option>
                            @foreach ($beamOptions as $opt)
                                <option value="{{ $opt['id'] }}" data-length="{{ $opt['length'] }}" @selected(($meta['beam_id'] ?? '') == $opt['id'])>{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][qty]" value="{{ $row['qty'] ?? ($meta['length'] ?? '') }}" data-beam-length></td>
                    <td class="border border-slate-300 p-0.5">
                        <select class="erp-input w-full" name="lines[{{ $i }}][meta][loom_id]">
                            <option value="">—</option>
                            @foreach ($looms as $loom)
                                <option value="{{ $loom->id }}" @selected(($meta['loom_id'] ?? '') == $loom->id)>{{ $loom->loom_no }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
