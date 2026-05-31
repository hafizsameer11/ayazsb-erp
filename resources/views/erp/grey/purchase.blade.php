@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @php
        $editingTransaction = $editingTransaction ?? null;
        $meta = $editingTransaction?->meta ?? [];
        $line0 = $editingTransaction?->lines->first();
        $cancelUrl = route('erp.grey.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
        $returnLines = collect($editingTransaction?->lines ?? [])->slice(1)->values();
    @endphp

    <div class="flex min-h-[calc(100vh-8rem)] flex-col">
        <div class="erp-panel shrink-0 border border-slate-500 bg-white shadow-md">
            <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — GREY PURCHASE</div>
            <form id="grey-purchase-form" class="space-y-2 p-3" data-erp-ajax-save data-grey-totals-form @if($editingTransaction) data-erp-editing="1" @endif
                action="{{ $editingTransaction ? route('erp.grey.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.grey.screen.store', ['screen' => $screen['slug']]) }}" method="post">
                @csrf @if($editingTransaction) @method('PATCH') @endif
                <div data-erp-form-feedback class="hidden" aria-live="polite"></div>
                @if ($editingTransaction)
                    @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingTransaction->trans_no, 'cancelUrl' => $cancelUrl])
                @endif
                @include('erp.grey.partials.purchase-sale-master', ['direction' => 'purchase', 'editingTransaction' => $editingTransaction, 'meta' => $meta, 'line0' => $line0])
                @include('erp.grey.partials.voucher-strip', ['meta' => $meta])
                @include('erp.grey.partials.purchase-return-grid', ['returnLines' => $returnLines, 'meta' => $meta])
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
            @include('erp.partials.records-history', ['historyType' => 'transaction', 'moduleKey' => 'grey', 'screen' => $screen, 'permissionPrefix' => $permissionPrefix])
        </div>
    </div>
@endsection
