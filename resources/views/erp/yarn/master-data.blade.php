@extends('layouts.erp')

@section('title', 'Yarn Master Data')

@section('content')
    @php
        $activeTab = $activeTab ?? 'master';
        $blankCount = ['id' => '', 'count' => '', 'is_active' => true];
        $blankThread = ['id' => '', 'thread' => '', 'is_active' => true];
        $blankBlend = ['id' => '', 'blend' => '', 'is_active' => true];
        $blankBrand = ['id' => '', 'brand' => '', 'is_active' => true];
        $blankRatio = ['id' => '', 'ratio' => '', 'is_active' => true];
        $blankItem = [
            'id' => '',
            'code' => '',
            'yarn_count_id' => '',
            'yarn_thread_id' => '',
            'yarn_blend_id' => '',
            'yarn_brand_id' => '',
            'yarn_ratio_id' => '',
            'item_type' => '',
            'pack_size_cones' => '',
            'packing_weight' => '100',
            'unit' => 'LBS',
            'name' => '',
            'yarn_code' => '',
            'is_active' => true,
        ];
        $blankGodown = ['id' => '', 'name' => '', 'is_active' => true];
        $padRows = static function (array $rows, array $blank, int $min = 8): array {
            $rows = array_values($rows);
            while (count($rows) < $min) {
                $rows[] = $blank;
            }

            return $rows;
        };
    @endphp

    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            {{ $screen['code'] }} — YARN MASTER DATA
        </div>

        <nav class="flex gap-1 border-b border-slate-300 px-2 pt-2 text-[11px]" aria-label="Yarn master tabs">
            @foreach (['master' => 'YARN MASTER', 'items' => 'YARN ITEMS', 'godowns' => 'GODOWNS'] as $key => $label)
                <a
                    href="{{ route('erp.yarn.master-data', ['tab' => $key]) }}"
                    class="-mb-px border border-b-0 border-slate-400 px-3 py-1.5 font-semibold {{ $activeTab === $key ? 'bg-white text-slate-900' : 'bg-[#d8d8d8] text-slate-600 hover:bg-[#ececec]' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <form
            id="yarn-master-form"
            class="p-3"
            method="post"
            action="{{ route('erp.yarn.master-data.store') }}"
        >
            @csrf
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            <div class="yarn-master-tab {{ $activeTab === 'master' ? '' : 'hidden' }}" data-tab-panel="master">
                <div class="grid gap-3 lg:grid-cols-3">
                    @foreach ([
                        ['title' => 'Yarn Count', 'name' => 'counts', 'valueKey' => 'count', 'rows' => $yarnCounts],
                        ['title' => 'Yarn Thread', 'name' => 'threads', 'valueKey' => 'thread', 'rows' => $yarnThreads],
                        ['title' => 'Yarn Blends', 'name' => 'blends', 'valueKey' => 'blend', 'rows' => $yarnBlends],
                    ] as $section)
                        <fieldset class="border border-slate-400 bg-[#f7f7f7] p-2">
                            <legend class="px-1 text-[11px] font-semibold text-slate-700">{{ $section['title'] }}</legend>
                            @include('erp.yarn.partials.master-lookup-grid', [
                                'fieldName' => $section['name'],
                                'valueKey' => $section['valueKey'],
                                'rows' => $padRows(
                                    $section['rows']->map(fn ($r) => [
                                        'id' => $r->id,
                                        $section['valueKey'] => $r->{$section['valueKey']},
                                        'is_active' => $r->is_active,
                                    ])->all(),
                                    ['id' => '', $section['valueKey'] => '', 'is_active' => true],
                                    10,
                                ),
                            ])
                        </fieldset>
                    @endforeach
                </div>
                <div class="mt-3 grid gap-3 md:grid-cols-2">
                    @foreach ([
                        ['title' => 'Yarn Brand', 'name' => 'brands', 'valueKey' => 'brand', 'rows' => $yarnBrands],
                        ['title' => 'Yarn Ratio', 'name' => 'ratios', 'valueKey' => 'ratio', 'rows' => $yarnRatios],
                    ] as $section)
                        <fieldset class="border border-slate-400 bg-[#f7f7f7] p-2">
                            <legend class="px-1 text-[11px] font-semibold text-slate-700">{{ $section['title'] }}</legend>
                            @include('erp.yarn.partials.master-lookup-grid', [
                                'fieldName' => $section['name'],
                                'valueKey' => $section['valueKey'],
                                'rows' => $padRows(
                                    $section['rows']->map(fn ($r) => [
                                        'id' => $r->id,
                                        $section['valueKey'] => $r->{$section['valueKey']},
                                        'is_active' => $r->is_active,
                                    ])->all(),
                                    ['id' => '', $section['valueKey'] => '', 'is_active' => true],
                                    8,
                                ),
                            ])
                        </fieldset>
                    @endforeach
                </div>
            </div>

            <div class="yarn-master-tab {{ $activeTab === 'items' ? '' : 'hidden' }}" data-tab-panel="items">
                <fieldset class="border border-slate-400 bg-[#f7f7f7] p-2">
                    <legend class="px-1 text-[11px] font-semibold text-slate-700">Yarn Items</legend>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1100px] border-collapse text-[11px]" id="yarn-items-table">
                            <thead>
                                <tr class="bg-[#e0e0e0] text-left">
                                    <th class="border border-slate-400 px-1 py-0.5 w-16">Yarn Id</th>
                                    <th class="border border-slate-400 px-1 py-0.5" colspan="2">Yarn Count</th>
                                    <th class="border border-slate-400 px-1 py-0.5" colspan="2">Thread</th>
                                    <th class="border border-slate-400 px-1 py-0.5" colspan="2">Blend</th>
                                    <th class="border border-slate-400 px-1 py-0.5" colspan="2">Brand</th>
                                    <th class="border border-slate-400 px-1 py-0.5" colspan="2">Ratio</th>
                                    <th class="border border-slate-400 px-1 py-0.5 w-20">Item Type</th>
                                    <th class="border border-slate-400 px-1 py-0.5 w-16">Pack Size</th>
                                    <th class="border border-slate-400 px-1 py-0.5 w-16">Packing Wt</th>
                                    <th class="border border-slate-400 px-1 py-0.5 w-14">Unit</th>
                                    <th class="border border-slate-400 px-1 py-0.5 min-w-[200px]">Name</th>
                                    <th class="border border-slate-400 px-1 py-0.5 w-20">Yarn Code</th>
                                    <th class="border border-slate-400 px-1 py-0.5 w-12 text-center">Active</th>
                                    <th class="border border-slate-400 px-1 py-0.5 w-8"></th>
                                </tr>
                            </thead>
                            <tbody id="yarn-items-body">
                                @foreach ($padRows(
                                    $yarnItems->map(fn ($item) => [
                                        'id' => $item->id,
                                        'code' => $item->code,
                                        'yarn_count_id' => $item->yarn_count_id,
                                        'yarn_thread_id' => $item->yarn_thread_id,
                                        'yarn_blend_id' => $item->yarn_blend_id,
                                        'yarn_brand_id' => $item->yarn_brand_id,
                                        'yarn_ratio_id' => $item->yarn_ratio_id,
                                        'item_type' => $item->item_type,
                                        'pack_size_cones' => $item->pack_size_cones,
                                        'packing_weight' => $item->packing_weight ?? 100,
                                        'unit' => $item->unit ?? 'LBS',
                                        'name' => $item->name,
                                        'yarn_code' => $item->yarn_code,
                                        'is_active' => $item->is_active,
                                    ])->all(),
                                    $blankItem,
                                    12,
                                ) as $index => $row)
                                    @include('erp.yarn.partials.master-item-row', [
                                        'index' => $index,
                                        'row' => $row,
                                        'yarnCounts' => $yarnCounts,
                                        'yarnThreads' => $yarnThreads,
                                        'yarnBlends' => $yarnBlends,
                                        'yarnBrands' => $yarnBrands,
                                        'yarnRatios' => $yarnRatios,
                                        'itemTypes' => $itemTypes,
                                        'weightUnits' => $weightUnits,
                                    ])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" id="yarn-items-add-row" class="mt-2 rounded border border-slate-500 bg-slate-200 px-2 py-0.5 text-[11px] font-semibold hover:bg-white">
                        Add row
                    </button>
                </fieldset>
            </div>

            <div class="yarn-master-tab {{ $activeTab === 'godowns' ? '' : 'hidden' }}" data-tab-panel="godowns">
                <fieldset class="border border-slate-400 bg-[#f7f7f7] p-2 max-w-xl">
                    <legend class="px-1 text-[11px] font-semibold text-slate-700">Yarn Godowns</legend>
                    <table class="w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-[#e0e0e0] text-left">
                                <th class="border border-slate-400 px-1 py-0.5 w-20">Godown Id</th>
                                <th class="border border-slate-400 px-1 py-0.5">Godown Name</th>
                                <th class="border border-slate-400 px-1 py-0.5 w-14 text-center">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($padRows(
                                $godowns->map(fn ($g) => ['id' => $g->id, 'name' => $g->name, 'is_active' => $g->is_active])->all(),
                                $blankGodown,
                                10,
                            ) as $index => $row)
                                <tr>
                                    <td class="border border-slate-400 p-0">
                                        <input type="hidden" name="godowns[{{ $index }}][id]" value="{{ $row['id'] }}">
                                        <input class="erp-input w-full border-0 bg-[#f0f0f0] text-center" type="text" value="{{ $row['id'] }}" readonly tabindex="-1">
                                    </td>
                                    <td class="border border-slate-400 p-0">
                                        <input class="erp-input w-full border-0" type="text" name="godowns[{{ $index }}][name]" value="{{ $row['name'] }}" autocomplete="off">
                                    </td>
                                    <td class="border border-slate-400 p-0 text-center">
                                        <input type="hidden" name="godowns[{{ $index }}][is_active]" value="0">
                                        <input type="checkbox" name="godowns[{{ $index }}][is_active]" value="1" class="h-3.5 w-3.5" @checked($row['is_active'])>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </fieldset>
            </div>

            <div class="mt-3 flex gap-2 border border-slate-300 bg-[#f0f0f0] p-2">
                @if(auth()->user()?->hasPermission('yarn.master-data.create') || auth()->user()?->hasPermission('yarn.master-yarn.create') || auth()->user()?->hasPermission('yarn.master-items.create') || auth()->user()?->hasPermission('yarn.master-godowns.create'))
                    <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-[12px] font-semibold hover:bg-white">
                        Save
                    </button>
                @endif
                <a href="{{ route('erp.yarn.dashboard') }}" class="rounded border border-slate-500 bg-white px-4 py-1.5 text-[12px] font-semibold hover:bg-slate-50">
                    Exit
                </a>
            </div>
        </form>
    </div>

    <template id="yarn-item-row-template">
        @include('erp.yarn.partials.master-item-row', [
            'index' => '__INDEX__',
            'row' => $blankItem,
            'yarnCounts' => $yarnCounts,
            'yarnThreads' => $yarnThreads,
            'yarnBlends' => $yarnBlends,
            'yarnBrands' => $yarnBrands,
            'yarnRatios' => $yarnRatios,
            'itemTypes' => $itemTypes,
            'weightUnits' => $weightUnits,
        ])
    </template>

    <script>
        (function () {
            const counts = @json($yarnCounts->map(fn ($r) => ['id' => $r->id, 'label' => $r->count])->values());
            const threads = @json($yarnThreads->map(fn ($r) => ['id' => $r->id, 'label' => $r->thread])->values());
            const blends = @json($yarnBlends->map(fn ($r) => ['id' => $r->id, 'label' => $r->blend])->values());
            const brands = @json($yarnBrands->map(fn ($r) => ['id' => $r->id, 'label' => $r->brand])->values());
            const ratios = @json($yarnRatios->map(fn ($r) => ['id' => $r->id, 'label' => $r->ratio])->values());

            function fillPairSelect(row, list, fkName, labelClass) {
                const fk = row.querySelector(`[name$="[${fkName}]"]`);
                const label = row.querySelector(`.${labelClass}`);
                if (!fk || !label) return;
                const opt = list.find((o) => String(o.id) === String(fk.value));
                label.value = opt ? opt.label : '';
            }

            function wireItemRow(row) {
                row.querySelectorAll('.yarn-fk-select').forEach((sel) => {
                    sel.addEventListener('change', () => {
                        const map = {
                            yarn_count_id: [counts, 'yarn-count-label'],
                            yarn_thread_id: [threads, 'yarn-thread-label'],
                            yarn_blend_id: [blends, 'yarn-blend-label'],
                            yarn_brand_id: [brands, 'yarn-brand-label'],
                            yarn_ratio_id: [ratios, 'yarn-ratio-label'],
                        };
                        const name = sel.name.match(/\[([^\]]+)\]$/)?.[1];
                        if (name && map[name]) {
                            fillPairSelect(row, map[name][0], name, map[name][1]);
                        }
                    });
                });
                row.querySelector('.yarn-item-remove')?.addEventListener('click', () => row.remove());
            }

            document.querySelectorAll('#yarn-items-body tr').forEach(wireItemRow);

            document.getElementById('yarn-items-add-row')?.addEventListener('click', () => {
                const body = document.getElementById('yarn-items-body');
                const index = body.querySelectorAll('tr').length;
                const html = document.getElementById('yarn-item-row-template').innerHTML.replaceAll('__INDEX__', String(index));
                body.insertAdjacentHTML('beforeend', html);
                wireItemRow(body.lastElementChild);
            });

        })();
    </script>
@endsection
