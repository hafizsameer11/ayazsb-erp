@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @php
        $editingTransaction = $editingTransaction ?? null;
        $lines = $editingTransaction?->lines ?? collect();
        $rows = $lines->map(fn ($l) => [
            'id' => $l->id,
            'grey_quality_id' => $l->meta['grey_quality_id'] ?? '',
            'system_lot_no' => $l->meta['system_lot_no'] ?? '',
            'qty' => $l->qty,
            'rate' => $l->rate,
            'amount' => $l->amount,
        ])->values()->all();
        while (count($rows) < 8) {
            $rows[] = ['id' => '', 'grey_quality_id' => '', 'system_lot_no' => '', 'qty' => '', 'rate' => '', 'amount' => ''];
        }
        $cancelUrl = route('erp.grey.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
    @endphp

    <div class="flex min-h-[calc(100vh-8rem)] flex-col">
        <div class="erp-panel shrink-0 border border-slate-500 bg-white shadow-md">
            <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — GREY OPENING</div>
            <form class="space-y-2 p-3" data-erp-ajax-save @if($editingTransaction) data-erp-editing="1" @endif
                action="{{ $editingTransaction ? route('erp.grey.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.grey.screen.store', ['screen' => $screen['slug']]) }}" method="post">
                @csrf @if($editingTransaction) @method('PATCH') @endif
                <div data-erp-form-feedback class="hidden"></div>
                @if ($editingTransaction) @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingTransaction->trans_no, 'cancelUrl' => $cancelUrl]) @endif
                <div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 md:grid-cols-3">
                    <label class="erp-field"><span class="erp-label">Open Id</span><input class="erp-input" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
                    <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
                    <label class="erp-field"><span class="erp-label">Godown</span>
                        <select class="erp-input" name="from_godown_id">
                            <option value=""></option>
                            @foreach ($godowns as $g)<option value="{{ $g->id }}" @selected((string)old('from_godown_id',$editingTransaction?->from_godown_id)===(string)$g->id)>{{ $g->name }}</option>@endforeach
                        </select>
                    </label>
                </div>
                <table class="w-full border-collapse text-[11px]">
                    <thead><tr class="bg-[#d8d8d8]"><th class="border px-1 py-1">System Lot #</th><th class="border px-1 py-1">Quality</th><th class="border px-1 py-1">Qty</th><th class="border px-1 py-1">Rate</th><th class="border px-1 py-1">Amount</th></tr></thead>
                    <tbody>
                        @foreach ($rows as $i => $row)
                            <tr>
                                <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][meta][system_lot_no]" value="{{ $row['system_lot_no'] }}"></td>
                                <td class="border p-0.5"><select class="erp-input w-full" name="lines[{{ $i }}][meta][grey_quality_id]"><option value=""></option>@foreach($greyQualities as $q)<option value="{{ $q->id }}" @selected((string)$row['grey_quality_id']===(string)$q->id)>{{ $q->quality_no }} — {{ $q->quality_name }}</option>@endforeach</select></td>
                                <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][qty]" type="number" step="0.01" value="{{ $row['qty'] }}"></td>
                                <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][rate]" type="number" step="0.01" value="{{ $row['rate'] }}"></td>
                                <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][amount]" type="number" step="0.01" value="{{ $row['amount'] }}"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <input type="hidden" name="submit_action" value="save">
                <button type="submit" class="mt-2 rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold">Save opening</button>
            </form>
        </div>
        <div class="erp-panel flex min-h-0 flex-1 flex-col border border-t-0 border-slate-500 bg-white shadow-md">
            @include('erp.partials.records-history', ['historyType' => 'transaction', 'moduleKey' => 'grey', 'screen' => $screen, 'permissionPrefix' => $permissionPrefix])
        </div>
    </div>
@endsection
