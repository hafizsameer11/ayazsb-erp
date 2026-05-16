@extends('layouts.erp')

@section('title', $voucherTitle)

@section('content')
    <div class="erp-panel erp-voucher-sheet flex min-h-[calc(100vh-9rem)] flex-col border border-slate-500 bg-white shadow-md">
        @include('erp.accounts.vouchers.partials.voucher-header', ['formId' => $formId, 'voucherTitle' => $voucherTitle, 'voucherCode' => $voucherCode])
        @include('erp.accounts.vouchers.partials.voucher-form-open')
            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher master</div>
            @include('erp.accounts.vouchers.partials.voucher-master-fields', ['showCashSummary' => true])

            <p class="text-[11px] text-slate-600"><abbr title="Cash voucher" class="cursor-help font-semibold">CV</abbr> — combined cash entry: <strong>Debit</strong> and <strong>Credit</strong> columns (same as journal).</p>

            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher details</div>
            @include('erp.accounts.vouchers.partials.voucher-lines-block', [
                'linePartial' => 'erp.accounts.vouchers.partials.voucher-line-journal',
                'theadPartial' => 'erp.accounts.vouchers.partials.voucher-thead-journal',
                'voucherCode' => $voucherCode,
                'tableClass' => 'w-full min-w-[520px] border-collapse text-left text-[12px]',
            ])
            @include('erp.accounts.vouchers.partials.voucher-actions', ['actions' => ['Voucher print'], 'permissionPrefix' => $permissionPrefix ?? null])
        </form>
        @include('erp.accounts.vouchers.partials.voucher-recent-saved')
        @include('erp.accounts.vouchers.partials.voucher-edit-scripts')
    </div>
@endsection
