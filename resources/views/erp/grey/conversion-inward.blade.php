@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @php
        $editingTransaction = $editingTransaction ?? null;
        $meta = $editingTransaction?->meta ?? [];
        $cancelUrl = route('erp.grey.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
    @endphp

    <div class="flex min-h-[calc(100vh-8rem)] flex-col">
        <div class="erp-panel shrink-0 border border-slate-500 bg-white shadow-md">
            <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — GREY CONVERSION INWARD</div>
            <form class="space-y-2 p-3" data-erp-ajax-save @if($editingTransaction) data-erp-editing="1" @endif
                action="{{ $editingTransaction ? route('erp.grey.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.grey.screen.store', ['screen' => $screen['slug']]) }}" method="post">
                @csrf @if($editingTransaction) @method('PATCH') @endif
                <div data-erp-form-feedback class="hidden"></div>
                @if ($editingTransaction) @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingTransaction->trans_no, 'cancelUrl' => $cancelUrl]) @endif
                <div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 lg:grid-cols-4">
                    <label class="erp-field"><span class="erp-label">Inward Id</span><input class="erp-input" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
                    <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
                    <label class="erp-field lg:col-span-2"><span class="erp-label">Conversion Contract</span>
                        <select class="erp-input" name="meta[grey_conversion_contract_id]">
                            <option value=""></option>
                            @foreach ($conversionContracts as $c)
                                <option value="{{ $c->id }}" @selected((string)old('meta.grey_conversion_contract_id',$meta['grey_conversion_contract_id']??'')===(string)$c->id)>{{ $c->contract_no }} — {{ $c->account?->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="erp-field lg:col-span-2"><span class="erp-label">Party</span>
                        <select class="erp-input" name="account_id"><option value=""></option>@foreach($accountParties as $a)<option value="{{ $a->id }}" @selected((string)old('account_id',$editingTransaction?->account_id)===(string)$a->id)>{{ $a->name }}</option>@endforeach</select>
                    </label>
                    <label class="erp-field"><span class="erp-label">Qty (Mtr)</span><input class="erp-input" name="lines[0][qty]" type="number" step="0.01" value="{{ old('lines.0.qty', $editingTransaction?->lines->first()?->qty) }}"></label>
                    <label class="erp-field"><span class="erp-label">Rate</span><input class="erp-input" name="lines[0][rate]" type="number" step="0.01" value="{{ old('lines.0.rate', $editingTransaction?->lines->first()?->rate) }}"></label>
                    <input type="hidden" name="meta[voucher_type]" value="GCV">
                    <input type="hidden" name="lines[0][description]" value="Grey conversion inward">
                    <input type="hidden" name="lines[0][amount]" value="{{ old('lines.0.amount', $editingTransaction?->lines->first()?->amount) }}">
                </div>
                <input type="hidden" name="submit_action" value="save">
                <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold">Save inward</button>
            </form>
        </div>
        <div class="erp-panel flex min-h-0 flex-1 flex-col border border-t-0 border-slate-500 bg-white shadow-md">
            @include('erp.partials.records-history', ['historyType' => 'transaction', 'moduleKey' => 'grey', 'screen' => $screen, 'permissionPrefix' => $permissionPrefix])
        </div>
    </div>
@endsection
