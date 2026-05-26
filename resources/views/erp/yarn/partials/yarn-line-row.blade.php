@php
    $row = $row ?? [];
@endphp
<tr data-yarn-line-row>
    <td class="border border-slate-300 p-0.5">
        <select class="erp-input w-full" name="lines[{{ $i }}][item_id]" data-yarn-item-select>
            <option value=""></option>
            @foreach(($items ?? []) as $item)
                <option value="{{ $item->id }}" data-pack="{{ $item->pack_size_cones }}" @selected((string)($row['item_id'] ?? '') === (string)$item->id)>{{ $item->code }} — {{ $item->name }}</option>
            @endforeach
        </select>
    </td>
    <td class="border border-slate-300 p-0.5">
        <select class="erp-input w-full" name="lines[{{ $i }}][meta][yarn_type]">
            @foreach(['any' => 'Any', 'warp' => 'Warp', 'weft' => 'Weft'] as $val => $lbl)
                <option value="{{ $val }}" @selected(($row['yarn_type'] ?? 'any') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
    </td>
    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full bg-slate-50" name="lines[{{ $i }}][meta][packing_size]" data-yarn-packing-size value="{{ $row['packing_size'] ?? '' }}" readonly></td>
    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][qty]" data-yarn-bags value="{{ $row['qty'] ?? '' }}"></td>
    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][meta][no_of_cones]" data-yarn-cones value="{{ $row['no_of_cones'] ?? '' }}"></td>
    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right bg-slate-50" name="lines[{{ $i }}][weight_lbs]" data-yarn-weight-lbs value="{{ $row['weight_lbs'] ?? '' }}" readonly></td>
    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right bg-slate-50" name="lines[{{ $i }}][meta][total_kgs]" data-yarn-total-kgs value="{{ $row['total_kgs'] ?? '' }}" readonly></td>
    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][rate]" data-yarn-rate value="{{ $row['rate'] ?? '' }}"></td>
    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right bg-slate-50" name="lines[{{ $i }}][amount]" data-yarn-amount value="{{ $row['amount'] ?? '' }}" readonly></td>
</tr>
