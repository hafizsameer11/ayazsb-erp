@extends('layouts.erp')

@section('title', 'Weaving Items')

@section('content')
    @php
        $blankRow = ['id' => '', 'code' => '', 'name' => '', 'unit' => 'PCS', 'is_active' => true];
        $rows = $weavingItems->map(fn ($item) => [
            'id' => $item->id,
            'code' => $item->code,
            'name' => $item->name,
            'unit' => $item->unit,
            'is_active' => $item->is_active,
        ])->all();
        while (count($rows) < 12) {
            $rows[] = $blankRow;
        }
    @endphp
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            {{ $screen['code'] }} — WEAVING ITEMS
        </div>
        <div class="border-b border-slate-300 bg-[#f5f5f5] px-3 py-2 text-[11px] text-slate-600">
            Item Id is auto-generated on save. Item name and measure (UOM) are used on Store Issue, Purchase Order, Purchase Return, and all store grids.
        </div>
        <form class="p-3" method="post" action="{{ route('erp.weaving.items.store') }}">
            @csrf
            <div class="overflow-x-auto border border-slate-400">
                <table class="w-full min-w-[720px] border-collapse text-[11px]" data-erp-detail-lines>
                    <thead class="bg-[#d8d8d8]">
                        <tr>
                            <th class="border border-slate-400 px-2 py-1 text-left">Item Id</th>
                            <th class="border border-slate-400 px-2 py-1 text-left">Item Name</th>
                            <th class="border border-slate-400 px-2 py-1 text-left">Measure (UOM)</th>
                            <th class="border border-slate-400 px-2 py-1 text-center">Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $row)
                            <tr data-erp-detail-line>
                                @if ($row['id'])
                                    <input type="hidden" name="items[{{ $i }}][id]" value="{{ $row['id'] }}">
                                @endif
                                <td class="border border-slate-300 p-0.5">
                                    <input
                                        class="erp-input w-full bg-[#f8f8f8] font-mono"
                                        value="{{ $row['code'] ?: 'Auto' }}"
                                        readonly
                                        tabindex="-1"
                                    >
                                </td>
                                <td class="border border-slate-300 p-0.5">
                                    <input class="erp-input w-full" name="items[{{ $i }}][name]" value="{{ $row['name'] }}" placeholder="Item name">
                                </td>
                                <td class="border border-slate-300 p-0.5">
                                    <select class="erp-input w-full" name="items[{{ $i }}][unit]">
                                        @foreach ($measureUnits as $unit)
                                            <option value="{{ $unit }}" @selected(($row['unit'] ?? 'PCS') === $unit)>{{ $unit }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="border border-slate-300 px-2 text-center">
                                    <input type="checkbox" name="items[{{ $i }}][is_active]" value="1" @checked($row['is_active'])>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @allowed($permissionPrefix . '.create')
                <button type="submit" class="mt-3 rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold hover:bg-white">Save Items</button>
            @endallowed
        </form>
    </div>
@endsection
