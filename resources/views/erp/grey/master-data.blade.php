@extends('layouts.erp')

@section('title', 'Grey Master Data')

@section('content')
    @php
        $activeTab = $activeTab ?? 'master';
        $editingQuality = $editingQuality ?? null;
        $blankDetail = [
            'id' => '',
            'nature' => 'WARP',
            'yarn_count_id' => '',
            'yarn_thread_id' => '',
            'yarn_blend_id' => '',
            'line_name' => '',
            'ends' => '',
            'picks' => '',
            'calc_count' => '',
            'weight' => '',
        ];
        $detailRows = $editingQuality
            ? $editingQuality->details->map(fn ($d) => [
                'id' => $d->id,
                'nature' => $d->nature,
                'yarn_count_id' => $d->yarn_count_id,
                'yarn_thread_id' => $d->yarn_thread_id,
                'yarn_blend_id' => $d->yarn_blend_id,
                'line_name' => $d->line_name,
                'ends' => $d->ends,
                'picks' => $d->picks,
                'calc_count' => $d->calc_count,
                'weight' => $d->weight,
            ])->all()
            : [];
        while (count($detailRows) < 6) {
            $detailRows[] = $blankDetail;
        }
        $blankGodown = ['id' => '', 'name' => '', 'is_active' => true];
        $godownRows = $godowns->map(fn ($g) => ['id' => $g->id, 'name' => $g->name, 'is_active' => $g->is_active])->all();
        while (count($godownRows) < 10) {
            $godownRows[] = $blankGodown;
        }
    @endphp

    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            {{ $screen['code'] }} — GREY MASTER DATA
        </div>

        <nav class="flex gap-1 border-b border-slate-300 px-2 pt-2 text-[11px]" aria-label="Grey master tabs">
            @foreach (['master' => 'GREY MASTER', 'godowns' => 'GREY GODOWNS'] as $key => $label)
                <a
                    href="{{ route('erp.grey.master-data', ['tab' => $key]) }}"
                    class="-mb-px border border-b-0 border-slate-400 px-3 py-1.5 font-semibold {{ $activeTab === $key ? 'bg-white text-slate-900' : 'bg-[#d8d8d8] text-slate-600 hover:bg-[#ececec]' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        @if ($activeTab === 'master')
            <form class="p-3" method="post" action="{{ route('erp.grey.master-data.store') }}">
                @csrf
                <input type="hidden" name="tab" value="master">
                @if ($editingQuality)
                    <input type="hidden" name="quality_id" value="{{ $editingQuality->id }}">
                @endif

                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <label class="text-[11px] font-semibold text-slate-700">
                        Open quality
                        <select class="erp-input ml-1" onchange="if(this.value) window.location='{{ route('erp.grey.master-data', ['tab' => 'master']) }}&edit='+this.value">
                            <option value="">New quality</option>
                            @foreach ($qualities as $q)
                                <option value="{{ $q->id }}" @selected($editingQuality?->id === $q->id)>{{ $q->quality_no }} — {{ $q->quality_name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <fieldset class="border border-slate-400 bg-[#f7f7f7] p-2">
                    <legend class="px-1 text-[11px] font-semibold text-slate-700">Quality Master</legend>
                    <div class="grid gap-2 md:grid-cols-3 lg:grid-cols-4">
                        <label class="erp-field"><span class="erp-label">Quality Id</span><input class="erp-input" name="quality_no" value="{{ old('quality_no', $editingQuality?->quality_no) }}" placeholder="Auto"></label>
                        <label class="erp-field"><span class="erp-label">Tag</span>
                            <select class="erp-input" name="tag">
                                <option value=""></option>
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag }}" @selected(old('tag', $editingQuality?->tag) === $tag)>{{ $tag }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="erp-field"><span class="erp-label">Season</span>
                            <select class="erp-input" name="season">
                                <option value=""></option>
                                @foreach ($seasons as $season)
                                    <option value="{{ $season }}" @selected(old('season', $editingQuality?->season) === $season)>{{ $season }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="erp-field flex items-end gap-2 pb-1"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editingQuality?->is_active ?? true))><span class="text-[11px]">Active</span></label>
                        <label class="erp-field"><span class="erp-label">Reed</span><input class="erp-input" name="reed" type="number" step="0.01" value="{{ old('reed', $editingQuality?->reed) }}"></label>
                        <label class="erp-field"><span class="erp-label">Pick</span><input class="erp-input" name="pick" type="number" step="0.01" value="{{ old('pick', $editingQuality?->pick) }}"></label>
                        <label class="erp-field"><span class="erp-label">Width</span><input class="erp-input" name="width" type="number" step="0.01" value="{{ old('width', $editingQuality?->width) }}"></label>
                        <label class="erp-field"><span class="erp-label">Total Ends</span><input class="erp-input" name="total_ends" type="number" step="0.01" value="{{ old('total_ends', $editingQuality?->total_ends) }}"></label>
                        <label class="erp-field"><span class="erp-label">Blend</span>
                            <select class="erp-input" name="yarn_blend_id">
                                <option value=""></option>
                                @foreach ($yarnBlends as $blend)
                                    <option value="{{ $blend->id }}" @selected((string) old('yarn_blend_id', $editingQuality?->yarn_blend_id) === (string) $blend->id)>{{ $blend->blend }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="erp-field"><span class="erp-label">Color</span><input class="erp-input" name="color" value="{{ old('color', $editingQuality?->color) }}"></label>
                        <label class="erp-field md:col-span-2"><span class="erp-label">Quality Name (auto)</span><input class="erp-input bg-slate-100" readonly value="{{ $editingQuality?->quality_name }}"></label>
                        <label class="erp-field md:col-span-2"><span class="erp-label">Quality Name Manual</span><input class="erp-input" name="quality_name_manual" value="{{ old('quality_name_manual', $editingQuality?->quality_name_manual) }}"></label>
                        <label class="erp-field md:col-span-4"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingQuality?->remarks) }}"></label>
                    </div>
                </fieldset>

                <fieldset class="mt-3 border border-slate-400 bg-[#f7f7f7] p-2">
                    <legend class="px-1 text-[11px] font-semibold text-slate-700">Quality Details</legend>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1000px] border-collapse text-[11px]">
                            <thead>
                                <tr class="bg-[#d8d8d8]">
                                    <th class="border border-slate-400 px-1 py-1">Nature</th>
                                    <th class="border border-slate-400 px-1 py-1">Count</th>
                                    <th class="border border-slate-400 px-1 py-1">Thread</th>
                                    <th class="border border-slate-400 px-1 py-1">Blend</th>
                                    <th class="border border-slate-400 px-1 py-1">Name</th>
                                    <th class="border border-slate-400 px-1 py-1">Ends</th>
                                    <th class="border border-slate-400 px-1 py-1">Picks</th>
                                    <th class="border border-slate-400 px-1 py-1">Calc Count</th>
                                    <th class="border border-slate-400 px-1 py-1">Weight</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detailRows as $i => $row)
                                    <tr>
                                        <td class="border border-slate-300 p-0.5">
                                            <input type="hidden" name="details[{{ $i }}][id]" value="{{ $row['id'] }}">
                                            <select class="erp-input w-full" name="details[{{ $i }}][nature]">
                                                @foreach (['WARP', 'WEFT'] as $n)
                                                    <option value="{{ $n }}" @selected(($row['nature'] ?? '') === $n)>{{ $n }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="border border-slate-300 p-0.5">
                                            <select class="erp-input w-full" name="details[{{ $i }}][yarn_count_id]">
                                                <option value=""></option>
                                                @foreach ($yarnCounts as $c)
                                                    <option value="{{ $c->id }}" @selected((string) ($row['yarn_count_id'] ?? '') === (string) $c->id)>{{ $c->count }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="border border-slate-300 p-0.5">
                                            <select class="erp-input w-full" name="details[{{ $i }}][yarn_thread_id]">
                                                <option value=""></option>
                                                @foreach ($yarnThreads as $t)
                                                    <option value="{{ $t->id }}" @selected((string) ($row['yarn_thread_id'] ?? '') === (string) $t->id)>{{ $t->thread }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="border border-slate-300 p-0.5">
                                            <select class="erp-input w-full" name="details[{{ $i }}][yarn_blend_id]">
                                                <option value=""></option>
                                                @foreach ($yarnBlends as $b)
                                                    <option value="{{ $b->id }}" @selected((string) ($row['yarn_blend_id'] ?? '') === (string) $b->id)>{{ $b->blend }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="details[{{ $i }}][line_name]" value="{{ $row['line_name'] }}"></td>
                                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="details[{{ $i }}][ends]" type="number" step="0.01" value="{{ $row['ends'] }}"></td>
                                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="details[{{ $i }}][picks]" type="number" step="0.01" value="{{ $row['picks'] }}"></td>
                                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="details[{{ $i }}][calc_count]" type="number" step="0.01" value="{{ $row['calc_count'] }}"></td>
                                        <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="details[{{ $i }}][weight]" type="number" step="0.000001" value="{{ $row['weight'] }}"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </fieldset>

                @if(auth()->user()?->hasPermission('grey.master-data.create') || auth()->user()?->hasPermission('grey.master-grey.create'))
                    <div class="mt-3 flex gap-2">
                        <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold hover:bg-white">Save quality</button>
                        <a href="{{ route('erp.grey.master-data', ['tab' => 'master']) }}" class="rounded border border-slate-500 bg-white px-4 py-1 text-[12px] hover:bg-sky-50">New</a>
                    </div>
                @endif
            </form>
            @if ($editingQuality && auth()->user()?->hasPermission('grey.master-data.delete'))
                <form class="mt-2 px-3 pb-3" method="post" action="{{ route('erp.grey.master-data.quality.destroy', $editingQuality) }}" onsubmit="return confirm('Delete quality {{ $editingQuality->quality_no }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded border border-red-600 bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-800 hover:bg-red-100">Delete quality</button>
                </form>
            @endif
        @else
            <form class="p-3" method="post" action="{{ route('erp.grey.master-data.store') }}">
                @csrf
                <input type="hidden" name="tab" value="godowns">
                <fieldset class="border border-slate-400 bg-[#f7f7f7] p-2">
                    <legend class="px-1 text-[11px] font-semibold text-slate-700">Grey Godowns</legend>
                    <table class="w-full border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-[#d8d8d8]">
                                <th class="border border-slate-400 px-1 py-1">Godown Id</th>
                                <th class="border border-slate-400 px-1 py-1">Name</th>
                                <th class="border border-slate-400 px-1 py-1">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($godownRows as $i => $row)
                                <tr>
                                    <td class="border border-slate-300 px-1 py-1 font-mono">
                                        <input type="hidden" name="godowns[{{ $i }}][id]" value="{{ $row['id'] }}">
                                        {{ $row['id'] ?: '—' }}
                                    </td>
                                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="godowns[{{ $i }}][name]" value="{{ $row['name'] }}"></td>
                                    <td class="border border-slate-300 px-1 py-1 text-center"><input type="checkbox" name="godowns[{{ $i }}][is_active]" value="1" @checked($row['is_active'])></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </fieldset>
                @if(auth()->user()?->hasPermission('grey.master-data.create') || auth()->user()?->hasPermission('grey.master-godowns.create'))
                    <button type="submit" class="mt-3 rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold hover:bg-white">Save godowns</button>
                @endif
            </form>
        @endif
    </div>
@endsection
