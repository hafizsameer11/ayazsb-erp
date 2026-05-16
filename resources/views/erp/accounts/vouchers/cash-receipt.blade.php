@extends('layouts.erp')

@section('title', $voucherTitle)

@section('content')
    <div class="erp-panel erp-voucher-sheet flex min-h-[calc(100vh-9rem)] flex-col border border-slate-500 bg-white shadow-md">
        @include('erp.accounts.vouchers.partials.voucher-header', ['formId' => $formId, 'voucherTitle' => $voucherTitle, 'voucherCode' => $voucherCode])
        @include('erp.accounts.vouchers.partials.voucher-form-open')
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="flex-1 space-y-2">
                    <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher master</div>
                    @include('erp.accounts.vouchers.partials.voucher-master-fields', ['showCashSummary' => true])
                </div>
            </div>

            <p class="text-[11px] text-slate-600"><abbr title="Cash receipt voucher" class="cursor-help font-semibold">CR</abbr> — cash receipt: <strong>Debit</strong> (e.g. cash) and <strong>Credit</strong> (e.g. income / debtor).</p>

            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher details</div>
            @include('erp.accounts.vouchers.partials.voucher-lines-block', [
                'linePartial' => 'erp.accounts.vouchers.partials.voucher-line-journal',
                'theadPartial' => 'erp.accounts.vouchers.partials.voucher-thead-journal',
                'voucherCode' => $voucherCode,
                'tableClass' => 'w-full min-w-[640px] border-collapse text-left text-[12px]',
            ])
            <div class="grid gap-2 border border-slate-300 bg-[#f0f0f0] p-2 md:grid-cols-2">
                <label class="erp-field"><span class="erp-label">Cost center</span><input class="erp-input" type="text"></label>
                <label class="erp-field"><span class="erp-label">In English</span><input class="erp-input" type="text" readonly></label>
            </div>
            @include('erp.accounts.vouchers.partials.voucher-actions', ['actions' => ['Voucher print', 'Print slip'], 'permissionPrefix' => $permissionPrefix ?? null])
        </form>
        @include('erp.accounts.vouchers.partials.voucher-recent-saved')
        @include('erp.accounts.vouchers.partials.voucher-edit-scripts')
    </div>
@endsection
