@php
    $columns = $columns ?? ['Item code', 'Description', 'Qty', 'Unit', 'Rate', 'Amount', 'Notes'];
    $rows = $rows ?? 8;
    $namePrefix = $namePrefix ?? null;
@endphp
<div class="overflow-x-auto border border-slate-400">
    <table class="w-full min-w-[900px] border-collapse text-left text-[12px]">
        <thead>
            <tr class="bg-[#d8d8d8]">
                @foreach ($columns as $column)
                    <th class="border border-slate-400 px-1 py-1 font-semibold">{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < $rows; $i++)
                <tr>
                    @foreach ($columns as $column)
                        @php
                            $isNumeric = str_contains(strtolower($column), 'qty') || str_contains(strtolower($column), 'rate') || str_contains(strtolower($column), 'amount') || str_contains(strtolower($column), 'weight');
                            $fieldName = match (strtolower($column)) {
                                'item code', 'yarn id', 'quality code', 'system lot #' => 'item_id',
                                'description', 'yarn description', 'remarks' => 'description',
                                'qty', 'qty / cones', 'qty (mtr)' => 'qty',
                                'unit', 'than' => 'unit',
                                'total weight (lbs)', 'weight' => 'weight_lbs',
                                'rate', 'rate / mtr' => 'rate',
                                'gross amount', 'amount' => 'amount',
                                default => 'description',
                            };
                        @endphp
                        <td class="border border-slate-300 p-0">
                            <input
                                class="erp-input w-full {{ $isNumeric ? 'text-right font-mono' : '' }}"
                                type="text"
                                name="{{ $namePrefix ? $namePrefix . '[' . $i . '][' . $fieldName . ']' : null }}"
                                placeholder="{{ $isNumeric ? '0.00' : '' }}"
                            >
                        </td>
                    @endforeach
                </tr>
            @endfor
        </tbody>
    </table>
</div>

