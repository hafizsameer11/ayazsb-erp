@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @php
        $editingProduction = $editingProduction ?? null;
        $lines = $editingProduction?->lines ?? collect();
        $rowCount = max(8, $lines->count() + 2);
        $rows = $lines->map(fn ($l) => [
            'sr' => $l->sr,
            'loom_id' => $l->loom_id,
            'beam_id' => $l->beam_id,
            'weaving_set_id' => $l->weaving_set_id,
            'grey_conversion_contract_id' => $l->grey_conversion_contract_id,
            'grey_quality_id' => $l->grey_quality_id,
            'width' => $l->width,
            'beam_balance' => $l->beam_balance,
            'sides' => $l->sides ?? [],
            'beam_status' => $l->beam_status,
        ])->all();
        while (count($rows) < $rowCount) {
            $rows[] = ['sides' => []];
        }
    @endphp
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — PRODUCTION DATA ENTRY</div>
        <form class="space-y-2 p-3" method="post" action="{{ route('erp.weaving.screen.store', ['screen' => $screen['slug']]) }}" data-erp-ajax-save>
            @csrf
            @if ($editingProduction)<input type="hidden" name="entry_id" value="{{ $editingProduction->id }}">@endif
            <div class="grid gap-2 md:grid-cols-4 lg:grid-cols-6">
                <label class="erp-field"><span class="erp-label">DOC #</span><input class="erp-input bg-[#f8f8f8]" value="{{ $editingProduction?->doc_no }}" readonly></label>
                <label class="erp-field"><span class="erp-label">DOC Date</span><input class="erp-input" type="date" name="doc_date" value="{{ optional($editingProduction?->doc_date)->format('Y-m-d') ?? now()->format('Y-m-d') }}" required></label>
                <label class="erp-field md:col-span-2"><span class="erp-label">Contract Quality</span>
                    <select class="erp-input" name="contract_grey_quality_id">
                        <option value="">—</option>
                        @foreach ($greyQualities as $q)
                            <option value="{{ $q->id }}" @selected($editingProduction?->contract_grey_quality_id == $q->id)>{{ $q->quality_no }} — {{ $q->quality_name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field md:col-span-2"><span class="erp-label">Production OF Quality</span>
                    <select class="erp-input" name="production_grey_quality_id">
                        <option value="">—</option>
                        @foreach ($greyQualities as $q)
                            <option value="{{ $q->id }}" @selected($editingProduction?->production_grey_quality_id == $q->id)>{{ $q->quality_no }} — {{ $q->quality_name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="overflow-x-auto border border-slate-400">
                <table class="w-full min-w-[1400px] border-collapse text-[10px]" data-erp-detail-lines>
                    <thead class="bg-[#d8d8d8]">
                        <tr>
                            <th class="border border-slate-400 px-1">SR</th>
                            <th class="border border-slate-400 px-1">Loom</th>
                            <th class="border border-slate-400 px-1">Beam</th>
                            <th class="border border-slate-400 px-1">Set</th>
                            <th class="border border-slate-400 px-1">Cont#</th>
                            <th class="border border-slate-400 px-1">Quality</th>
                            <th class="border border-slate-400 px-1">Width</th>
                            <th class="border border-slate-400 px-1">Beam Bal</th>
                            <th class="border border-slate-400 px-1">Pick Fresh</th>
                            <th class="border border-slate-400 px-1">Pick B.G</th>
                            <th class="border border-slate-400 px-1">Pick C.P</th>
                            <th class="border border-slate-400 px-1">Recv Fresh</th>
                            <th class="border border-slate-400 px-1">Recv B.G</th>
                            <th class="border border-slate-400 px-1">Recv C.P</th>
                            <th class="border border-slate-400 px-1">Beam Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $row)
                            @php $sides = $row['sides'] ?? []; @endphp
                            <tr data-erp-detail-line>
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-12" name="lines[{{ $i }}][sr]" value="{{ $row['sr'] ?? ($i + 1) }}"></td>
                                <td class="border border-slate-300 p-0.5">
                                    <select class="erp-input w-full" name="lines[{{ $i }}][loom_id]">
                                        <option value="">—</option>
                                        @foreach ($looms as $loom)
                                            <option value="{{ $loom->id }}" @selected(($row['loom_id'] ?? '') == $loom->id)>{{ $loom->loom_no }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][beam_id]" value="{{ $row['beam_id'] ?? '' }}"></td>
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][weaving_set_id]" value="{{ $row['weaving_set_id'] ?? '' }}"></td>
                                <td class="border border-slate-300 p-0.5">
                                    <select class="erp-input w-full" name="lines[{{ $i }}][grey_conversion_contract_id]">
                                        <option value="">—</option>
                                        @foreach ($conversionContracts as $c)
                                            <option value="{{ $c->id }}" @selected(($row['grey_conversion_contract_id'] ?? '') == $c->id)>{{ $c->contract_no }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="border border-slate-300 p-0.5">
                                    <select class="erp-input w-full" name="lines[{{ $i }}][grey_quality_id]">
                                        <option value="">—</option>
                                        @foreach ($greyQualities as $q)
                                            <option value="{{ $q->id }}" @selected(($row['grey_quality_id'] ?? '') == $q->id)>{{ $q->quality_no }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][width]" value="{{ $row['width'] ?? '' }}"></td>
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][beam_balance]" value="{{ $row['beam_balance'] ?? '' }}"></td>
                                @foreach (['picking_fresh', 'picking_bg', 'picking_cp', 'receiving_fresh', 'receiving_bg', 'receiving_cp'] as $sideKey)
                                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][sides][{{ $sideKey }}]" value="{{ $sides[$sideKey] ?? '' }}"></td>
                                @endforeach
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][beam_status]" value="{{ $row['beam_status'] ?? '' }}"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold hover:bg-white">Save Production</button>
        </form>
    </div>
@endsection
