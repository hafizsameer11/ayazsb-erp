@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @php
        $editingContract = $editingContract ?? null;
        $cancelUrl = route('erp.grey.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
    @endphp

    <div class="flex min-h-[calc(100vh-8rem)] flex-col">
        <div class="erp-panel shrink-0 border border-slate-500 bg-white shadow-md">
            <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — CONVERSION CONTRACT</div>
            <form class="space-y-2 p-3" data-erp-ajax-save data-grey-contract-form @if($editingContract) data-erp-editing="1" @endif
                action="{{ $editingContract ? route('erp.grey.screen.contract.update', ['screen' => $screen['slug'], 'contract' => $editingContract]) : route('erp.grey.screen.store', ['screen' => $screen['slug']]) }}" method="post">
                @csrf @if($editingContract) @method('PATCH') @endif
                <div data-erp-form-feedback class="hidden"></div>
                @if ($editingContract)
                    @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingContract->contract_no, 'cancelUrl' => $cancelUrl])
                @endif
                @include('erp.grey.partials.conversion-contract-form-body', ['editingContract' => $editingContract])
                <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold hover:bg-white">Save contract</button>
            </form>
        </div>
        <div class="erp-panel flex min-h-0 flex-1 flex-col border border-t-0 border-slate-500 bg-white shadow-md">
            @include('erp.partials.records-history', ['historyType' => 'contract', 'moduleKey' => 'grey', 'screen' => $screen, 'permissionPrefix' => $permissionPrefix, 'recordsForDay' => $recordsForDay ?? collect()])
        </div>
    </div>
@endsection
