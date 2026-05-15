<tr>
    <td class="border border-slate-300 p-1">
        <select class="erp-input" name="lines[{{ $i }}][item_id]">
            <option value="">Select yarn</option>
            @foreach (($items ?? []) as $item)
                <option value="{{ $item->id }}">{{ $item->code }} — {{ $item->name }}</option>
            @endforeach
        </select>
    </td>
    <td class="border border-slate-300 p-1">
        <select class="erp-input" name="lines[{{ $i }}][meta][yarn_type]">
            <option value="">Type</option>
            <option value="WARP">WARP</option>
            <option value="WEFT">WEFT</option>
        </select>
    </td>
    <td class="border border-slate-300 p-1"><input class="erp-input" name="lines[{{ $i }}][description]"></td>
    <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][qty]"></td>
    <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][meta][cones]"></td>
    <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][weight_lbs]"></td>
    <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][rate]"></td>
    <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][meta][transfer_rate]"></td>
    @if ($showAdjustment ?? false)
        <td class="border border-slate-300 p-1">
            <select class="erp-input" name="lines[{{ $i }}][meta][adjustment_type]">
                <option value="gain">Gain</option>
                <option value="shortage">Shortage</option>
            </select>
        </td>
    @endif
    <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.01" min="0" name="lines[{{ $i }}][amount]"></td>
</tr>
