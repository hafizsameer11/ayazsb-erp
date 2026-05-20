@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @php
        $editingTransaction = $editingTransaction ?? null;
        $meta = $editingTransaction?->meta ?? [];
        $line0 = $editingTransaction?->lines->first();
        $cancelUrl = route('erp.grey.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
        $returnLines = collect($editingTransaction?->lines ?? [])->slice(1)->values();
        while ($returnLines->count() < 3) {
            $returnLines->push(null);
        }
    @endphp

    <div class="flex min-h-[calc(100vh-8rem)] flex-col">
        <div class="erp-panel shrink-0 border border-slate-500 bg-white shadow-md">
            <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — GREY PURCHASE</div>
            <form
                id="grey-purchase-form"
                class="space-y-2 p-3"
                data-erp-ajax-save
                data-grey-totals-form
                @if($editingTransaction) data-erp-editing="1" @endif
                action="{{ $editingTransaction ? route('erp.grey.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.grey.screen.store', ['screen' => $screen['slug']]) }}"
                method="post"
            >
                @csrf
                @if($editingTransaction) @method('PATCH') @endif
                <div data-erp-form-feedback class="hidden" aria-live="polite"></div>
                @if ($editingTransaction)
                    @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingTransaction->trans_no, 'cancelUrl' => $cancelUrl])
                @endif

                <div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 lg:grid-cols-4">
                    <label class="erp-field"><span class="erp-label">Purchase Id</span><input class="erp-input" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
                    <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
                    <label class="erp-field"><span class="erp-label">Parchi No</span><input class="erp-input" name="meta[parchi_no]" value="{{ old('meta.parchi_no', $meta['parchi_no'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Bill No</span><input class="erp-input" name="meta[bill_no]" value="{{ old('meta.bill_no', $meta['bill_no'] ?? '') }}"></label>
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
                            <option value="">Select quality</option>
                            @foreach ($greyQualities as $q)
                                <option value="{{ $q->id }}" @selected((string) old('lines.0.meta.grey_quality_id', $line0?->meta['grey_quality_id'] ?? '') === (string) $q->id)>{{ $q->quality_no }} — {{ $q->quality_name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="erp-field"><span class="erp-label">System Lot #</span><input class="erp-input" name="meta[system_lot_no]" value="{{ old('meta.system_lot_no', $meta['system_lot_no'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Manual Lot #</span><input class="erp-input" name="meta[manual_lot_no]" value="{{ old('meta.manual_lot_no', $meta['manual_lot_no'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Broker</span>
                        <select class="erp-input" name="meta[broker_account_id]">
                            <option value=""></option>
                            @foreach ($accountParties as $account)
                                <option value="{{ $account->id }}" @selected((string) old('meta.broker_account_id', $meta['broker_account_id'] ?? '') === (string) $account->id)>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="erp-field"><span class="erp-label">Checker</span>
                        <select class="erp-input" name="meta[checker_account_id]">
                            <option value=""></option>
                            @foreach ($accountParties as $account)
                                <option value="{{ $account->id }}" @selected((string) old('meta.checker_account_id', $meta['checker_account_id'] ?? '') === (string) $account->id)>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="erp-field"><span class="erp-label">Than Qty</span><input class="erp-input" data-grey-than-qty name="meta[than_qty]" type="number" step="0.01" value="{{ old('meta.than_qty', $meta['than_qty'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Long</span><input class="erp-input" data-grey-long-qty name="meta[long_qty]" type="number" step="0.01" value="{{ old('meta.long_qty', $meta['long_qty'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Short</span><input class="erp-input" data-grey-short-qty name="meta[short_qty]" type="number" step="0.01" value="{{ old('meta.short_qty', $meta['short_qty'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Net Qty (Mtr)</span><input class="erp-input bg-slate-100" data-grey-net-qty name="meta[net_qty]" type="number" step="0.01" value="{{ old('meta.net_qty', $meta['net_qty'] ?? '') }}" readonly></label>
                    <label class="erp-field"><span class="erp-label">Godown</span>
                        <select class="erp-input" name="from_godown_id">
                            <option value=""></option>
                            @foreach ($godowns as $godown)
                                <option value="{{ $godown->id }}" @selected((string) old('from_godown_id', $editingTransaction?->from_godown_id) === (string) $godown->id)>{{ $godown->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="erp-field"><span class="erp-label">Loom Type</span><input class="erp-input" name="meta[loom_type]" value="{{ old('meta.loom_type', $meta['loom_type'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Loom Width</span><input class="erp-input" name="meta[loom_width]" value="{{ old('meta.loom_width', $meta['loom_width'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Loom Panna</span><input class="erp-input" name="meta[loom_panna]" value="{{ old('meta.loom_panna', $meta['loom_panna'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Brokery Type</span><input class="erp-input" name="meta[brokery_type]" value="{{ old('meta.brokery_type', $meta['brokery_type'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Brokery Rate</span><input class="erp-input" data-grey-brokery-rate name="meta[brokery_rate]" type="number" step="0.01" value="{{ old('meta.brokery_rate', $meta['brokery_rate'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Checker Rate / Mtr</span><input class="erp-input" data-grey-checker-rate name="meta[checker_rate_mtr]" type="number" step="0.01" value="{{ old('meta.checker_rate_mtr', $meta['checker_rate_mtr'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Munshiana</span><input class="erp-input" data-grey-munshiana name="meta[munshiana]" type="number" step="0.01" value="{{ old('meta.munshiana', $meta['munshiana'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Comm %</span><input class="erp-input" data-grey-commission name="meta[commission_percent]" type="number" step="0.01" value="{{ old('meta.commission_percent', $meta['commission_percent'] ?? '') }}"></label>
                    <label class="erp-field"><span class="erp-label">Grey Rate / Mtr</span><input class="erp-input" data-grey-rate name="meta[grey_rate_mtr]" type="number" step="0.01" value="{{ old('meta.grey_rate_mtr', $meta['grey_rate_mtr'] ?? '') }}"></label>
                    <input type="hidden" name="lines[0][qty]" data-grey-line-qty value="{{ old('lines.0.qty', $line0?->qty) }}">
                    <input type="hidden" name="lines[0][rate]" data-grey-line-rate value="{{ old('lines.0.rate', $line0?->rate) }}">
                    <input type="hidden" name="lines[0][amount]" data-grey-line-amount value="{{ old('lines.0.amount', $line0?->amount) }}">
                    <input type="hidden" name="meta[voucher_type]" value="GPV">
                    <input type="hidden" name="lines[0][description]" value="Grey purchase">
                </div>

                <fieldset class="border border-slate-400 bg-[#eef3ff] p-2">
                    <legend class="px-1 text-[11px] font-semibold">Totals</legend>
                    <div class="grid gap-2 md:grid-cols-3 lg:grid-cols-6">
                        <label class="erp-field"><span class="erp-label">Total Gross</span><input class="erp-input bg-white" data-grey-total-gross name="meta[total_gross_amount]" readonly value="{{ $meta['total_gross_amount'] ?? '' }}"></label>
                        <label class="erp-field"><span class="erp-label">Commission</span><input class="erp-input bg-white" data-grey-total-commission name="meta[total_commission]" readonly value="{{ $meta['total_commission'] ?? '' }}"></label>
                        <label class="erp-field"><span class="erp-label">Brokery</span><input class="erp-input bg-white" data-grey-total-brokery name="meta[total_brokery]" readonly value="{{ $meta['total_brokery'] ?? '' }}"></label>
                        <label class="erp-field"><span class="erp-label">Checkary</span><input class="erp-input bg-white" data-grey-total-checkary name="meta[total_checkary]" readonly value="{{ $meta['total_checkary'] ?? '' }}"></label>
                        <label class="erp-field"><span class="erp-label">Munshiana</span><input class="erp-input bg-white" data-grey-total-munshiana name="meta[total_munshiana]" readonly value="{{ $meta['total_munshiana'] ?? '' }}"></label>
                        <label class="erp-field"><span class="erp-label">Net Amount</span><input class="erp-input bg-white font-semibold" data-grey-total-net name="meta[total_net_amount]" readonly value="{{ $meta['total_net_amount'] ?? '' }}"></label>
                    </div>
                </fieldset>

                <fieldset class="mt-2 border border-slate-400 bg-[#f7f7f7] p-2">
                    <legend class="px-1 text-[11px] font-semibold">Purchase return lines (GPV)</legend>
                    <table class="w-full border-collapse text-[11px]">
                        <thead><tr class="bg-[#d8d8d8]"><th class="border border-slate-400 px-1 py-1">Quality</th><th class="border border-slate-400 px-1 py-1">Qty</th><th class="border border-slate-400 px-1 py-1">Rate</th><th class="border border-slate-400 px-1 py-1">Amount</th></tr></thead>
                        <tbody>
                            @foreach ($returnLines as $i => $retLine)
                                @php $idx = $i + 1; @endphp
                                <tr>
                                    <td class="border border-slate-300 p-0.5">
                                        <select class="erp-input w-full" name="lines[{{ $idx }}][meta][grey_quality_id]">
                                            <option value=""></option>
                                            @foreach ($greyQualities as $q)
                                                <option value="{{ $q->id }}" @selected((string) old("lines.{$idx}.meta.grey_quality_id", $retLine?->meta['grey_quality_id'] ?? '') === (string) $q->id)>{{ $q->quality_no }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="lines[{{ $idx }}][qty]" type="number" step="0.01" value="{{ old("lines.{$idx}.qty", $retLine?->qty) }}"></td>
                                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="lines[{{ $idx }}][rate]" type="number" step="0.01" value="{{ old("lines.{$idx}.rate", $retLine?->rate) }}"></td>
                                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="lines[{{ $idx }}][amount]" type="number" step="0.01" value="{{ old("lines.{$idx}.amount", $retLine?->amount) }}"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </fieldset>

                <label class="erp-field block"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
                <input type="hidden" name="submit_action" value="save">
                <div class="flex gap-2">
                    <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold hover:bg-white">Save</button>
                    @allowed($permissionPrefix . '.post')
                        <button type="submit" name="submit_action" value="post" class="rounded border border-emerald-700 bg-emerald-100 px-4 py-1 text-[12px] font-semibold hover:bg-emerald-50">Save &amp; Post</button>
                    @endallowed
                </div>
            </form>
        </div>
        <div class="erp-panel flex min-h-0 flex-1 flex-col border border-t-0 border-slate-500 bg-white shadow-md">
            @include('erp.partials.records-history', array_merge(compact('screen', 'permissionPrefix'), [
                'historyType' => 'transaction',
                'moduleKey' => 'grey',
            ]))
        </div>
    </div>
@endsection
