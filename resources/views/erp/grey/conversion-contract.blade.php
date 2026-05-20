@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @php
        $editingContract = $editingContract ?? null;
        $cancelUrl = route('erp.grey.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
        $warpRows = old('warp_details', $editingContract?->warp_details ?? []);
        $weftRows = old('weft_details', $editingContract?->weft_details ?? []);
        while (count($warpRows) < 3) { $warpRows[] = ['yarn_count_id' => '', 'ends' => '', 'picks' => '']; }
        while (count($weftRows) < 3) { $weftRows[] = ['yarn_count_id' => '', 'ends' => '', 'picks' => '']; }
    @endphp

    <div class="flex min-h-[calc(100vh-8rem)] flex-col">
        <div class="erp-panel shrink-0 border border-slate-500 bg-white shadow-md">
            <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — CONVERSION CONTRACT</div>
            <form class="space-y-2 p-3" data-erp-ajax-save @if($editingContract) data-erp-editing="1" @endif
                action="{{ $editingContract ? route('erp.grey.screen.contract.update', ['screen' => $screen['slug'], 'contract' => $editingContract]) : route('erp.grey.screen.store', ['screen' => $screen['slug']]) }}" method="post">
                @csrf @if($editingContract) @method('PATCH') @endif
                <div data-erp-form-feedback class="hidden"></div>
                @if ($editingContract) @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingContract->contract_no, 'cancelUrl' => $cancelUrl]) @endif
                <div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 lg:grid-cols-4">
                    <label class="erp-field"><span class="erp-label">Contract #</span><input class="erp-input" name="contract_no" value="{{ old('contract_no', $editingContract?->contract_no) }}" required @if($editingContract) readonly @endif></label>
                    <label class="erp-field"><span class="erp-label">Date</span><x-erp-date-input name="contract_date" :value="old('contract_date', $editingContract?->contract_date)" :required="true" /></label>
                    <label class="erp-field"><span class="erp-label">Status</span><select class="erp-input" name="status"><option value="running" @selected(old('status',$editingContract?->status??'running')==='running')>Running</option><option value="closed" @selected(old('status',$editingContract?->status)==='closed')>Closed</option></select></label>
                    <label class="erp-field lg:col-span-2"><span class="erp-label">Party</span>
                        <select class="erp-input" name="account_id" required><option value=""></option>@foreach($accountParties as $a)<option value="{{ $a->id }}" @selected((string)old('account_id',$editingContract?->account_id)===(string)$a->id)>{{ $a->code }} — {{ $a->name }}</option>@endforeach</select>
                    </label>
                    <label class="erp-field lg:col-span-2"><span class="erp-label">Quality</span>
                        <select class="erp-input" name="grey_quality_id"><option value=""></option>@foreach($greyQualities as $q)<option value="{{ $q->id }}" @selected((string)old('grey_quality_id',$editingContract?->grey_quality_id)===(string)$q->id)>{{ $q->quality_no }} — {{ $q->quality_name }}</option>@endforeach</select>
                    </label>
                    <label class="erp-field"><span class="erp-label">Qty (Mtr)</span><input class="erp-input" name="qty_mtr" type="number" step="0.01" value="{{ old('qty_mtr', $editingContract?->qty_mtr) }}"></label>
                    <label class="erp-field"><span class="erp-label">Per Mtr Rate</span><input class="erp-input" name="per_mtr_rate" type="number" step="0.01" value="{{ old('per_mtr_rate', $editingContract?->per_mtr_rate) }}"></label>
                    <label class="erp-field"><span class="erp-label">Loom Type</span><input class="erp-input" name="loom_type" value="{{ old('loom_type', $editingContract?->loom_type) }}"></label>
                    <label class="erp-field"><span class="erp-label">Loom Width</span><input class="erp-input" name="loom_width" type="number" step="0.01" value="{{ old('loom_width', $editingContract?->loom_width) }}"></label>
                    <label class="erp-field"><span class="erp-label">Brokery %</span><input class="erp-input" name="brokery_rate" type="number" step="0.01" value="{{ old('brokery_rate', $editingContract?->brokery_rate) }}"></label>
                    <label class="erp-field"><span class="erp-label">Checker %</span><input class="erp-input" name="checker_rate" type="number" step="0.01" value="{{ old('checker_rate', $editingContract?->checker_rate) }}"></label>
                    <label class="erp-field"><span class="erp-label">Munshiana</span><input class="erp-input" name="munshiana" type="number" step="0.01" value="{{ old('munshiana', $editingContract?->munshiana) }}"></label>
                    <label class="erp-field md:col-span-4"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingContract?->remarks) }}"></label>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    @foreach (['warp_details' => 'Warp details', 'weft_details' => 'Weft details'] as $key => $title)
                        <fieldset class="border border-slate-400 p-2">
                            <legend class="px-1 text-[11px] font-semibold">{{ $title }}</legend>
                            <table class="w-full border-collapse text-[11px]">
                                <thead><tr class="bg-[#d8d8d8]"><th class="border px-1">Count</th><th class="border px-1">Ends</th><th class="border px-1">Picks</th></tr></thead>
                                <tbody>
                                    @foreach (($key === 'warp_details' ? $warpRows : $weftRows) as $i => $row)
                                        <tr>
                                            <td class="border p-0.5"><select class="erp-input w-full" name="{{ $key }}[{{ $i }}][yarn_count_id]"><option value=""></option>@foreach($yarnCounts as $c)<option value="{{ $c->id }}" @selected((string)($row['yarn_count_id']??'')===(string)$c->id)>{{ $c->count }}</option>@endforeach</select></td>
                                            <td class="border p-0.5"><input class="erp-input w-full" name="{{ $key }}[{{ $i }}][ends]" value="{{ $row['ends'] ?? '' }}"></td>
                                            <td class="border p-0.5"><input class="erp-input w-full" name="{{ $key }}[{{ $i }}][picks]" value="{{ $row['picks'] ?? '' }}"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </fieldset>
                    @endforeach
                </div>
                <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold">Save contract</button>
            </form>
        </div>
        <div class="erp-panel flex min-h-0 flex-1 flex-col border border-t-0 border-slate-500 bg-white shadow-md">
            @include('erp.partials.records-history', ['historyType' => 'contract', 'moduleKey' => 'grey', 'screen' => $screen, 'permissionPrefix' => $permissionPrefix, 'recordsForDay' => $recordsForDay ?? collect()])
        </div>
    </div>
@endsection
