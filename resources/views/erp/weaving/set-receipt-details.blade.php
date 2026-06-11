@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @php
        $editingSet = $editingSet ?? null;
        $meta = $editingSet?->meta ?? [];
        $beams = $editingSet?->beams ?? collect();
        $beamRows = $beams->map(fn ($b) => ['beam_no' => $b->beam_no, 'beam_length' => $b->beam_length])->all();
        while (count($beamRows) < 8) {
            $beamRows[] = ['beam_no' => '', 'beam_length' => ''];
        }
    @endphp
    <div class="flex min-h-[calc(100vh-8rem)] flex-col">
        <div class="erp-panel shrink-0 border border-slate-500 bg-white shadow-md">
            <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — SET RECEIPT DETAILS</div>
            <form class="space-y-2 p-3" method="post" action="{{ route('erp.weaving.screen.store', ['screen' => $screen['slug']]) }}" data-erp-ajax-save>
                @csrf
                @if ($editingSet)<input type="hidden" name="set_id" value="{{ $editingSet->id }}">@endif
                <div class="grid gap-2 md:grid-cols-4 lg:grid-cols-6">
                    <label class="erp-field"><span class="erp-label">Set #</span><input class="erp-input" name="set_no" value="{{ $editingSet?->set_no }}"></label>
                    <label class="erp-field"><span class="erp-label">Company Set #</span><input class="erp-input" name="company_set_no" value="{{ $editingSet?->company_set_no }}"></label>
                    <label class="erp-field"><span class="erp-label">Entry Date</span><input class="erp-input" type="date" name="entry_date" value="{{ optional($editingSet?->entry_date)->format('Y-m-d') ?? now()->format('Y-m-d') }}"></label>
                    <label class="erp-field"><span class="erp-label">Receipt Date</span><input class="erp-input" type="date" name="receipt_date" value="{{ optional($editingSet?->receipt_date)->format('Y-m-d') ?? now()->format('Y-m-d') }}"></label>
                    <label class="erp-field md:col-span-2"><span class="erp-label">Sizing Party</span>
                        @include('erp.grey.partials.code-name-pair', [
                            'selectName' => 'sizing_party_account_id',
                            'selectedId' => old('sizing_party_account_id', $editingSet?->sizing_party_account_id),
                            'options' => $accountParties,
                            'targetId' => 'weaving-sizing-party',
                        ])
                    </label>
                    <label class="erp-field md:col-span-2"><span class="erp-label">Contract</span>
                        <select class="erp-input" name="grey_conversion_contract_id">
                            <option value="">—</option>
                            @foreach ($conversionContracts as $c)
                                <option value="{{ $c->id }}" @selected($editingSet?->grey_conversion_contract_id == $c->id)>{{ $c->contract_no }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="erp-field md:col-span-2"><span class="erp-label">Quality</span>
                        <select class="erp-input" name="grey_quality_id">
                            <option value="">—</option>
                            @foreach ($greyQualities as $q)
                                <option value="{{ $q->id }}" @selected($editingSet?->grey_quality_id == $q->id)>{{ $q->quality_no }} — {{ $q->quality_name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="erp-field"><span class="erp-label">Width</span><input class="erp-input" name="width" value="{{ $editingSet?->width }}"></label>
                    <label class="erp-field"><span class="erp-label">Shrink %</span><input class="erp-input" name="shrink_percent" value="{{ $editingSet?->shrink_percent }}"></label>
                    <label class="erp-field"><span class="erp-label">Ends/Tareen</span><input class="erp-input" name="ends_tareen" value="{{ $editingSet?->ends_tareen }}"></label>
                    <label class="erp-field"><span class="erp-label">Meters</span><input class="erp-input" name="meters" value="{{ $editingSet?->meters }}"></label>
                </div>
                <div class="grid gap-2 border border-slate-400 bg-[#f0f0f0] p-2 md:grid-cols-4">
                    <label class="erp-field"><span class="erp-label">Yarn Used</span><input class="erp-input" name="meta[yarn_used]" value="{{ $meta['yarn_used'] ?? '' }}"></label>
                    <label class="erp-field"><span class="erp-label">Yarn Return</span><input class="erp-input" name="meta[yarn_return]" value="{{ $meta['yarn_return'] ?? '' }}"></label>
                    <label class="erp-field"><span class="erp-label">Waste</span><input class="erp-input" name="meta[waste]" value="{{ $meta['waste'] ?? '' }}"></label>
                    <label class="erp-field"><span class="erp-label">GP No</span><input class="erp-input" name="meta[gp_no]" value="{{ $meta['gp_no'] ?? '' }}"></label>
                </div>
                <fieldset class="border border-slate-400 bg-[#f7f7f7] p-2">
                    <legend class="px-1 text-[11px] font-semibold">Party Bill Info</legend>
                    <div class="grid gap-2 md:grid-cols-4 lg:grid-cols-6">
                        <label class="erp-field"><span class="erp-label">Party Bill #</span><input class="erp-input" name="meta[party_bill_no]" value="{{ $meta['party_bill_no'] ?? '' }}"></label>
                        <label class="erp-field"><span class="erp-label">Weight Kgs</span><input class="erp-input text-right" name="meta[weight_kgs]" value="{{ $meta['weight_kgs'] ?? '' }}"></label>
                        <label class="erp-field"><span class="erp-label">Weight LBS</span><input class="erp-input text-right" name="meta[weight_lbs]" value="{{ $meta['weight_lbs'] ?? '' }}"></label>
                        <label class="erp-field"><span class="erp-label">Rate in Kgs</span><input class="erp-input text-right" name="meta[rate_kgs]" value="{{ $meta['rate_kgs'] ?? '' }}"></label>
                        <label class="erp-field"><span class="erp-label">Bill Amount</span><input class="erp-input text-right" name="meta[bill_amount]" value="{{ $meta['bill_amount'] ?? '' }}"></label>
                        <label class="erp-field"><span class="erp-label">Voucher Gen Date</span><input class="erp-input" type="date" name="meta[voucher_gen_date]" value="{{ $meta['voucher_gen_date'] ?? now()->format('Y-m-d') }}"></label>
                    </div>
                </fieldset>
                @php $setVoucher = $editingSet?->voucher; @endphp
                @include('erp.grey.partials.voucher-strip', ['meta' => array_merge($meta, [
                    'voucher_id' => $setVoucher?->id ?? ($meta['voucher_id'] ?? ''),
                    'voucher_num' => $setVoucher?->voucher_number ?? ($meta['voucher_num'] ?? ''),
                    'voucher_date' => optional($setVoucher?->voucher_date)->format('Y-m-d') ?? ($meta['voucher_date'] ?? ''),
                    'voucher_type' => $setVoucher?->voucher_type ?? ($meta['voucher_type'] ?? 'BPV'),
                ])])
                @if ($editingSet)
                    <div class="flex flex-wrap gap-2">
                        @allowed($permissionPrefix . '.post')
                            @if (! $editingSet->voucher_id)
                                <form action="{{ route('erp.weaving.set.voucher', ['set' => $editingSet]) }}" method="post" class="inline">@csrf<button type="submit" class="rounded border border-blue-700 bg-blue-100 px-3 py-1 text-[11px] font-semibold">Generate Voucher</button></form>
                            @else
                                <form action="{{ route('erp.weaving.set.voucher.update', ['set' => $editingSet]) }}" method="post" class="inline">@csrf @method('PATCH')<button type="submit" class="rounded border border-amber-700 bg-amber-100 px-3 py-1 text-[11px] font-semibold">Update Voucher</button></form>
                            @endif
                        @endallowed
                    </div>
                @endif
                <div class="overflow-x-auto border border-slate-400">
                    <table class="w-full border-collapse text-[11px]" data-erp-detail-lines>
                        <thead class="bg-[#d8d8d8]"><tr><th class="border border-slate-400 px-1 py-1">Beam #</th><th class="border border-slate-400 px-1 py-1">Length</th></tr></thead>
                        <tbody>
                            @foreach ($beamRows as $i => $beam)
                                <tr data-erp-detail-line>
                                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="beams[{{ $i }}][beam_no]" value="{{ $beam['beam_no'] }}"></td>
                                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="beams[{{ $i }}][beam_length]" value="{{ $beam['beam_length'] }}"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold hover:bg-white">Save Set</button>
            </form>
        </div>
    </div>
@endsection
