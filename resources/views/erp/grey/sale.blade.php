@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @php
        $editingTransaction = $editingTransaction ?? null;
        $meta = $editingTransaction?->meta ?? [];
        $line0 = $editingTransaction?->lines->first();
        $cancelUrl = route('erp.grey.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
        $detailLines = collect($editingTransaction?->lines ?? [])->slice(1)->values();
        while ($detailLines->count() < 4) { $detailLines->push(null); }
    @endphp

    <div class="flex min-h-[calc(100vh-8rem)] flex-col">
        <div class="erp-panel shrink-0 border border-slate-500 bg-white shadow-md">
            <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — GREY SALE</div>
            <form class="space-y-2 p-3" data-erp-ajax-save data-grey-totals-form @if($editingTransaction) data-erp-editing="1" @endif
                action="{{ $editingTransaction ? route('erp.grey.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.grey.screen.store', ['screen' => $screen['slug']]) }}" method="post">
                @csrf @if($editingTransaction) @method('PATCH') @endif
                <div data-erp-form-feedback class="hidden" aria-live="polite"></div>
                @if ($editingTransaction) @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingTransaction->trans_no, 'cancelUrl' => $cancelUrl]) @endif
                <div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 lg:grid-cols-4">
                    <label class="erp-field"><span class="erp-label">Sale Id</span><input class="erp-input" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
                    <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
                    <label class="erp-field lg:col-span-2"><span class="erp-label">Party</span>
                        <select class="erp-input" name="account_id" required>
                            <option value="">Select party</option>
                            @foreach ($accountParties as $account)
                                <option value="{{ $account->id }}" @selected((string) old('account_id', $editingTransaction?->account_id) === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="erp-field lg:col-span-2"><span class="erp-label">Quality</span>
                        <select class="erp-input" name="lines[0][meta][grey_quality_id]">
                            <option value=""></option>
                            @foreach ($greyQualities as $q)
                                <option value="{{ $q->id }}" @selected((string) old('lines.0.meta.grey_quality_id', $line0?->meta['grey_quality_id'] ?? '') === (string) $q->id)>{{ $q->quality_no }} — {{ $q->quality_name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="erp-field"><span class="erp-label">Than Qty</span><input class="erp-input" data-grey-than-qty name="meta[than_qty]" type="number" step="0.01" value="{{ old('meta.than_qty', $meta['than_qty'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Long</span><input class="erp-input" data-grey-long-qty name="meta[long_qty]" type="number" step="0.01" value="{{ old('meta.long_qty', $meta['long_qty'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Short</span><input class="erp-input" data-grey-short-qty name="meta[short_qty]" type="number" step="0.01" value="{{ old('meta.short_qty', $meta['short_qty'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Net Qty</span><input class="erp-input bg-slate-100" data-grey-net-qty name="meta[net_qty]" readonly value="{{ old('meta.net_qty', $meta['net_qty'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Grey Rate / Mtr</span><input class="erp-input" data-grey-rate name="meta[grey_rate_mtr]" type="number" step="0.01" value="{{ old('meta.grey_rate_mtr', $meta['grey_rate_mtr'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Comm %</span><input class="erp-input" data-grey-commission name="meta[commission_percent]" type="number" step="0.01" value="{{ old('meta.commission_percent', $meta['commission_percent'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Brokery Rate</span><input class="erp-input" data-grey-brokery-rate name="meta[brokery_rate]" type="number" step="0.01" value="{{ old('meta.brokery_rate', $meta['brokery_rate'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Checker / Mtr</span><input class="erp-input" data-grey-checker-rate name="meta[checker_rate_mtr]" type="number" step="0.01" value="{{ old('meta.checker_rate_mtr', $meta['checker_rate_mtr'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Munshiana</span><input class="erp-input" data-grey-munshiana name="meta[munshiana]" type="number" step="0.01" value="{{ old('meta.munshiana', $meta['munshiana'] ?? '') }}"></label>
                    <input type="hidden" name="meta[voucher_type]" value="GSV">
                    <input type="hidden" name="lines[0][qty]" data-grey-line-qty value="{{ $line0?->qty }}">
                    <input type="hidden" name="lines[0][rate]" data-grey-line-rate value="{{ $line0?->rate }}">
                    <input type="hidden" name="lines[0][amount]" data-grey-line-amount value="{{ $line0?->amount }}">
                    <input type="hidden" name="lines[0][description]" value="Grey sale">
                </div>
                <fieldset class="border border-slate-400 bg-[#eef3ff] p-2">
                    <legend class="px-1 text-[11px] font-semibold">Totals</legend>
                    <div class="grid gap-2 md:grid-cols-3 lg:grid-cols-6">
                        <label class="erp-field"><span class="erp-label">Gross</span><input class="erp-input" data-grey-total-gross name="meta[total_gross_amount]" readonly value="{{ $meta['total_gross_amount'] ?? '' }}"></label>
                        <label class="erp-field"><span class="erp-label">Net Amount</span><input class="erp-input font-semibold" data-grey-total-net name="meta[total_net_amount]" readonly value="{{ $meta['total_net_amount'] ?? '' }}"></label>
                    </div>
                </fieldset>
                <fieldset class="mt-2 border border-slate-400 bg-[#f7f7f7] p-2">
                    <legend class="px-1 text-[11px] font-semibold">Sale detail lines</legend>
                    <table class="w-full border-collapse text-[11px]">
                        <thead><tr class="bg-[#d8d8d8]"><th class="border px-1 py-1">Quality</th><th class="border px-1 py-1">Qty</th><th class="border px-1 py-1">Rate</th><th class="border px-1 py-1">Amount</th></tr></thead>
                        <tbody>
                            @foreach ($detailLines as $i => $line)
                                @php $idx = $i + 1; @endphp
                                <tr>
                                    <td class="border p-0.5"><select class="erp-input w-full" name="lines[{{ $idx }}][meta][grey_quality_id]"><option value=""></option>@foreach($greyQualities as $q)<option value="{{ $q->id }}" @selected((string)old("lines.{$idx}.meta.grey_quality_id",$line?->meta['grey_quality_id']??'')===(string)$q->id)>{{ $q->quality_no }}</option>@endforeach</select></td>
                                    <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $idx }}][qty]" type="number" step="0.01" value="{{ $line?->qty }}"></td>
                                    <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $idx }}][rate]" type="number" step="0.01" value="{{ $line?->rate }}"></td>
                                    <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $idx }}][amount]" type="number" step="0.01" value="{{ $line?->amount }}"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </fieldset>
                <input type="hidden" name="submit_action" value="save">
                <button type="submit" class="mt-2 rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold">Save</button>
            </form>
        </div>
        <div class="erp-panel flex min-h-0 flex-1 flex-col border border-t-0 border-slate-500 bg-white shadow-md">
            @include('erp.partials.records-history', ['historyType' => 'transaction', 'moduleKey' => 'grey', 'screen' => $screen, 'permissionPrefix' => $permissionPrefix])
        </div>
    </div>
@endsection
