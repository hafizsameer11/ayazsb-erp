@php
    $index = $index ?? 0;
    $row = $row ?? [];
@endphp
<tr class="yarn-item-row">
    <td class="border border-slate-400 p-0">
        <input type="hidden" name="items[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
        <input class="erp-input w-full border-0 text-center" type="text" name="items[{{ $index }}][code]" value="{{ $row['code'] ?? '' }}" autocomplete="off">
    </td>
    <td class="border border-slate-400 p-0 w-12">
        <select class="erp-input w-full border-0 yarn-fk-select" name="items[{{ $index }}][yarn_count_id]">
            <option value=""></option>
            @foreach ($yarnCounts as $opt)
                <option value="{{ $opt->id }}" @selected((string) ($row['yarn_count_id'] ?? '') === (string) $opt->id)>{{ $opt->id }}</option>
            @endforeach
        </select>
    </td>
    <td class="border border-slate-400 p-0 w-14">
        <input class="erp-input yarn-count-label w-full border-0 bg-[#f0f0f0]" type="text" readonly tabindex="-1" value="{{ $yarnCounts->firstWhere('id', (int) ($row['yarn_count_id'] ?? 0))?->count }}">
    </td>
    <td class="border border-slate-400 p-0 w-12">
        <select class="erp-input w-full border-0 yarn-fk-select" name="items[{{ $index }}][yarn_thread_id]">
            <option value=""></option>
            @foreach ($yarnThreads as $opt)
                <option value="{{ $opt->id }}" @selected((string) ($row['yarn_thread_id'] ?? '') === (string) $opt->id)>{{ $opt->id }}</option>
            @endforeach
        </select>
    </td>
    <td class="border border-slate-400 p-0 w-14">
        <input class="erp-input yarn-thread-label w-full border-0 bg-[#f0f0f0]" type="text" readonly tabindex="-1" value="{{ $yarnThreads->firstWhere('id', (int) ($row['yarn_thread_id'] ?? 0))?->thread }}">
    </td>
    <td class="border border-slate-400 p-0 w-12">
        <select class="erp-input w-full border-0 yarn-fk-select" name="items[{{ $index }}][yarn_blend_id]">
            <option value=""></option>
            @foreach ($yarnBlends as $opt)
                <option value="{{ $opt->id }}" @selected((string) ($row['yarn_blend_id'] ?? '') === (string) $opt->id)>{{ $opt->id }}</option>
            @endforeach
        </select>
    </td>
    <td class="border border-slate-400 p-0 w-20">
        <input class="erp-input yarn-blend-label w-full border-0 bg-[#f0f0f0]" type="text" readonly tabindex="-1" value="{{ $yarnBlends->firstWhere('id', (int) ($row['yarn_blend_id'] ?? 0))?->blend }}">
    </td>
    <td class="border border-slate-400 p-0 w-12">
        <select class="erp-input w-full border-0 yarn-fk-select" name="items[{{ $index }}][yarn_brand_id]">
            <option value=""></option>
            @foreach ($yarnBrands as $opt)
                <option value="{{ $opt->id }}" @selected((string) ($row['yarn_brand_id'] ?? '') === (string) $opt->id)>{{ $opt->id }}</option>
            @endforeach
        </select>
    </td>
    <td class="border border-slate-400 p-0 w-20">
        <input class="erp-input yarn-brand-label w-full border-0 bg-[#f0f0f0]" type="text" readonly tabindex="-1" value="{{ $yarnBrands->firstWhere('id', (int) ($row['yarn_brand_id'] ?? 0))?->brand }}">
    </td>
    <td class="border border-slate-400 p-0 w-12">
        <select class="erp-input w-full border-0 yarn-fk-select" name="items[{{ $index }}][yarn_ratio_id]">
            <option value=""></option>
            @foreach ($yarnRatios as $opt)
                <option value="{{ $opt->id }}" @selected((string) ($row['yarn_ratio_id'] ?? '') === (string) $opt->id)>{{ $opt->id }}</option>
            @endforeach
        </select>
    </td>
    <td class="border border-slate-400 p-0 w-14">
        <input class="erp-input yarn-ratio-label w-full border-0 bg-[#f0f0f0]" type="text" readonly tabindex="-1" value="{{ $yarnRatios->firstWhere('id', (int) ($row['yarn_ratio_id'] ?? 0))?->ratio }}">
    </td>
    <td class="border border-slate-400 p-0">
        <select class="erp-input w-full border-0" name="items[{{ $index }}][item_type]">
            <option value=""></option>
            @foreach ($itemTypes as $type)
                <option value="{{ $type }}" @selected(($row['item_type'] ?? '') === $type)>{{ $type }}</option>
            @endforeach
        </select>
    </td>
    <td class="border border-slate-400 p-0">
        <input class="erp-input w-full border-0 text-right" type="number" min="0" step="1" name="items[{{ $index }}][pack_size_cones]" value="{{ $row['pack_size_cones'] ?? '' }}">
    </td>
    <td class="border border-slate-400 p-0">
        <input class="erp-input w-full border-0 text-right" type="number" min="0" step="0.01" name="items[{{ $index }}][packing_weight]" value="{{ $row['packing_weight'] ?? '' }}">
    </td>
    <td class="border border-slate-400 p-0">
        <select class="erp-input w-full border-0" name="items[{{ $index }}][unit]">
            @foreach ($weightUnits as $unit)
                <option value="{{ $unit }}" @selected(($row['unit'] ?? 'LBS') === $unit)>{{ $unit }}</option>
            @endforeach
        </select>
    </td>
    <td class="border border-slate-400 p-0">
        <input class="erp-input w-full border-0" type="text" name="items[{{ $index }}][name]" value="{{ $row['name'] ?? '' }}">
    </td>
    <td class="border border-slate-400 p-0">
        <input class="erp-input w-full border-0" type="text" name="items[{{ $index }}][yarn_code]" value="{{ $row['yarn_code'] ?? '' }}">
    </td>
    <td class="border border-slate-400 p-0 text-center">
        <input type="hidden" name="items[{{ $index }}][is_active]" value="0">
        <input type="checkbox" name="items[{{ $index }}][is_active]" value="1" class="h-3.5 w-3.5" @checked($row['is_active'] ?? true)>
    </td>
    <td class="border border-slate-400 p-0 text-center">
        <button type="button" class="yarn-item-remove text-red-700 hover:underline" title="Remove row">×</button>
    </td>
</tr>
