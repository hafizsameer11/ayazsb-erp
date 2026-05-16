@php
    $line = $line ?? null;
@endphp
<tr>
    <td class="border border-slate-300 p-1">
        <select class="erp-input" name="lines[{{ $i }}][item_id]">
            <option value="">Select yarn</option>
            @foreach (($items ?? []) as $item)
                <option value="{{ $item->id }}" @selected((string) old("lines.$i.item_id", $line?->item_id) === (string) $item->id)>{{ $item->code }} — {{ $item->name }}</option>
            @endforeach
        </select>
    </td>
    <td class="border border-slate-300 p-1">
        <select class="erp-input" name="lines[{{ $i }}][meta][yarn_type]">
            <option value="">Type</option>
            <option value="WARP" @selected(old("lines.$i.meta.yarn_type", $line?->meta['yarn_type'] ?? '') === 'WARP')>WARP</option>
            <option value="WEFT" @selected(old("lines.$i.meta.yarn_type", $line?->meta['yarn_type'] ?? '') === 'WEFT')>WEFT</option>
        </select>
    </td>
    <td class="border border-slate-300 p-1"><input class="erp-input" name="lines[{{ $i }}][description]" value="{{ old("lines.$i.description", $line?->description ?? '') }}"></td>
    <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][qty]" value="{{ old("lines.$i.qty", $line?->qty ?? '') }}"></td>
    <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][meta][cones]" value="{{ old("lines.$i.meta.cones", $line?->meta['cones'] ?? '') }}"></td>
    <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][weight_lbs]" value="{{ old("lines.$i.weight_lbs", $line?->weight_lbs ?? '') }}"></td>
    <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][rate]" value="{{ old("lines.$i.rate", $line?->rate ?? '') }}"></td>
    <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][meta][transfer_rate]" value="{{ old("lines.$i.meta.transfer_rate", $line?->meta['transfer_rate'] ?? '') }}"></td>
    @if ($showAdjustment ?? false)
        <td class="border border-slate-300 p-1">
            <select class="erp-input" name="lines[{{ $i }}][meta][adjustment_type]">
                <option value="gain" @selected(old("lines.$i.meta.adjustment_type", $line?->meta['adjustment_type'] ?? 'gain') === 'gain')>Gain</option>
                <option value="shortage" @selected(old("lines.$i.meta.adjustment_type", $line?->meta['adjustment_type'] ?? '') === 'shortage')>Shortage</option>
            </select>
        </td>
    @endif
    <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.01" min="0" name="lines[{{ $i }}][amount]" value="{{ old("lines.$i.amount", $line?->amount ?? '') }}"></td>
</tr>
