@extends('layouts.erp')

@section('title', $voucherTitle)

@section('content')
    <div class="erp-panel erp-voucher-sheet flex min-h-[calc(100vh-9rem)] flex-col border border-slate-500 bg-white shadow-md">
        @include('erp.accounts.vouchers.partials.voucher-header', ['formId' => $formId, 'voucherTitle' => $voucherTitle, 'voucherCode' => $voucherCode])
        @include('erp.accounts.vouchers.partials.voucher-form-open')
            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher master</div>
            @include('erp.accounts.vouchers.partials.voucher-master-fields', ['showBank' => true])

            <p class="text-[11px] text-slate-600"><abbr title="Bank payment voucher" class="cursor-help font-semibold">BPV</abbr> — bank payment: <strong>Debit</strong> and <strong>Credit</strong> per line (same idea as CP).</p>

            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher details</div>
            @include('erp.accounts.vouchers.partials.voucher-lines-block', [
                'linePartial' => 'erp.accounts.vouchers.partials.voucher-line-bank-payment',
                'theadPartial' => 'erp.accounts.vouchers.partials.voucher-thead-bank-payment',
                'voucherCode' => $voucherCode,
                'tableClass' => 'w-full min-w-[960px] border-collapse text-left text-[12px]',
            ])
            <div class="flex flex-wrap items-end gap-3 border border-slate-300 bg-[#f0f0f0] p-2">
                <input class="erp-input w-16 font-mono" type="text" value="0" readonly>
                <label class="erp-field min-w-[120px]"><span class="erp-label">Cost center</span><input class="erp-input" type="text"></label>
                <label class="erp-field min-w-[200px] flex-1"><span class="erp-label">Account description</span><input class="erp-input w-full" type="text" readonly></label>
            </div>
            @include('erp.accounts.vouchers.partials.voucher-actions', ['actions' => ['Print voucher', 'Print slip'], 'permissionPrefix' => $permissionPrefix ?? null])
        </form>
        @include('erp.accounts.vouchers.partials.voucher-recent-saved')
        @include('erp.accounts.vouchers.partials.voucher-edit-scripts')
    </div>
@endsection
