@php
    $fieldName = $fieldName ?? 'rows';
    $valueKey = $valueKey ?? 'value';
    $rows = $rows ?? [];
@endphp
<table class="w-full border-collapse text-[11px]">
    <thead>
        <tr class="bg-[#e0e0e0] text-left">
            <th class="border border-slate-400 px-1 py-0.5 w-12">id</th>
            <th class="border border-slate-400 px-1 py-0.5">{{ ucfirst($valueKey) }}</th>
            <th class="border border-slate-400 px-1 py-0.5 w-14 text-center">Active</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $index => $row)
            <tr>
                <td class="border border-slate-400 p-0">
                    <input type="hidden" name="{{ $fieldName }}[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
                    <input class="erp-input w-full border-0 bg-[#f0f0f0] text-center" type="text" value="{{ $row['id'] ?? '' }}" readonly tabindex="-1">
                </td>
                <td class="border border-slate-400 p-0">
                    <input
                        class="erp-input w-full border-0"
                        type="text"
                        name="{{ $fieldName }}[{{ $index }}][{{ $valueKey }}]"
                        value="{{ $row[$valueKey] ?? '' }}"
                        autocomplete="off"
                    >
                </td>
                <td class="border border-slate-400 p-0 text-center">
                    <input type="hidden" name="{{ $fieldName }}[{{ $index }}][is_active]" value="0">
                    <input
                        type="checkbox"
                        name="{{ $fieldName }}[{{ $index }}][is_active]"
                        value="1"
                        class="h-3.5 w-3.5"
                        @checked($row['is_active'] ?? true)
                    >
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
