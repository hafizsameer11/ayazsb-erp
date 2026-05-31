@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @php
        $cancelUrl = route('erp.grey.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
    @endphp

    <div class="flex min-h-[calc(100vh-8rem)] flex-col">
        <div class="erp-panel shrink-0 border border-slate-500 bg-white shadow-md">
            <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — GREY OPENING</div>
            <form class="space-y-2 p-3" data-erp-ajax-save data-grey-opening-form @if($editingTransaction ?? null) data-erp-editing="1" @endif
                action="{{ ($editingTransaction ?? null) ? route('erp.grey.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.grey.screen.store', ['screen' => $screen['slug']]) }}" method="post">
                @csrf @if($editingTransaction ?? null) @method('PATCH') @endif
                <div data-erp-form-feedback class="hidden"></div>
                @if ($editingTransaction ?? null)
                    @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingTransaction->trans_no, 'cancelUrl' => $cancelUrl])
                @endif
                @include('erp.grey.partials.opening-form-body', ['editingTransaction' => $editingTransaction ?? null])
                <input type="hidden" name="submit_action" value="save">
                <button type="submit" class="mt-2 rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold hover:bg-white">Save opening</button>
            </form>
        </div>
        <div class="erp-panel flex min-h-0 flex-1 flex-col border border-t-0 border-slate-500 bg-white shadow-md">
            @include('erp.partials.records-history', ['historyType' => 'transaction', 'moduleKey' => 'grey', 'screen' => $screen, 'permissionPrefix' => $permissionPrefix])
        </div>
    </div>
@endsection
