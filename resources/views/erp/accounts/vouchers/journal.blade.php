@extends('layouts.erp')

@section('title', $voucherTitle)

@section('content')
    <div class="erp-panel erp-voucher-sheet flex min-h-[calc(100vh-9rem)] flex-col border border-slate-500 bg-white shadow-md">
        @include('erp.accounts.vouchers.partials.voucher-header', ['formId' => $formId, 'voucherTitle' => $voucherTitle, 'voucherCode' => $voucherCode])

        <form class="erp-voucher-entry shrink-0 space-y-1 p-2" action="{{ route('erp.accounts.vouchers.store', ['voucherType' => strtolower($voucherCode)]) }}" method="post">
            @csrf
            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher master</div>
            @include('erp.accounts.vouchers.partials.voucher-master-fields')

            <p class="text-[11px] text-slate-600"><abbr title="Journal voucher" class="cursor-help font-semibold">JV</abbr> — general journal: balanced <strong>Debit</strong> / <strong>Credit</strong> lines.</p>

            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher details</div>
            @include('erp.accounts.vouchers.partials.voucher-lines-block', [
                'linePartial' => 'erp.accounts.vouchers.partials.voucher-line-journal',
                'theadPartial' => 'erp.accounts.vouchers.partials.voucher-thead-journal',
                'voucherCode' => $voucherCode,
            ])
            <div class="flex flex-wrap items-end gap-3 border border-slate-300 bg-[#f0f0f0] p-2">
                <input class="erp-input w-16 font-mono" type="text" value="0" readonly>
                <label class="erp-field flex-1 min-w-[120px]"><span class="erp-label">Cost center</span><input class="erp-input" type="text" name="cost_center"></label>
            </div>
            @include('erp.accounts.vouchers.partials.voucher-actions', ['actions' => ['Voucher print'], 'permissionPrefix' => $permissionPrefix ?? null])
        </form>
        @include('erp.accounts.vouchers.partials.voucher-recent-saved')
    </div>
@endsection
