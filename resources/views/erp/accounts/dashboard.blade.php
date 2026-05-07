@extends('layouts.erp')

@section('title', 'Accounts & finance')

@section('content')
    <div class="erp-panel flex min-h-[420px] flex-col border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            Pulse ERP — Accounts &amp; finance
        </div>
        <div class="flex flex-1 gap-4 p-4">
            <div class="min-w-[220px] flex-1">
                <div class="mb-2 text-[11px] font-bold uppercase text-slate-600">Transactions</div>
                <ul class="space-y-0.5 border border-slate-400 bg-[#f5f5f5] p-2 text-[12px]">
                    @allowed('accounts.vouchers.jv.view')<li><a class="erp-tree-link" href="{{ route('erp.accounts.vouchers.jv') }}">Journal voucher — JV</a></li>@endallowed
                    @allowed('accounts.vouchers.cp.view')<li><a class="erp-tree-link" href="{{ route('erp.accounts.vouchers.cp') }}">Cash payment voucher — CP</a></li>@endallowed
                    @allowed('accounts.vouchers.cr.view')<li><a class="erp-tree-link" href="{{ route('erp.accounts.vouchers.cr') }}">Cash receipt voucher — CR</a></li>@endallowed
                    @allowed('accounts.vouchers.bpv.view')<li><a class="erp-tree-link" href="{{ route('erp.accounts.vouchers.bpv') }}">Bank payment voucher — BPV</a></li>@endallowed
                    @allowed('accounts.vouchers.brv.view')<li><a class="erp-tree-link" href="{{ route('erp.accounts.vouchers.brv') }}">Bank receipt voucher — BRV</a></li>@endallowed
                    @allowed('accounts.vouchers.cv.view')<li><a class="erp-tree-link" href="{{ route('erp.accounts.vouchers.cv') }}">Cash voucher — CV</a></li>@endallowed
                </ul>
                <div class="mb-2 mt-4 text-[11px] font-bold uppercase text-slate-600">Setup</div>
                <ul class="space-y-0.5 border border-slate-400 bg-[#f5f5f5] p-2 text-[12px]">
                    @allowed('accounts.coa.view')<li><a class="erp-tree-link" href="{{ route('erp.accounts.coa') }}">Chart of accounts (COA)</a></li>@endallowed
                    @allowed('accounts.opening.view')<li><a class="erp-tree-link" href="{{ route('erp.accounts.opening') }}">Accounts opening</a></li>@endallowed
                    @allowed('accounts.financial-year.view')<li><a class="erp-tree-link" href="{{ route('erp.accounts.financial-year') }}">Financial year</a></li>@endallowed
                </ul>
            </div>
            <div class="hidden max-w-sm flex-1 text-[11px] text-slate-600 md:block">
                <p class="mb-2">Select a transaction or setup item from the lists. Voucher screens use the same master/detail pattern as the legacy desktop forms.</p>
                <p>Next steps: wire models, validation, and posting to the general ledger.</p>
            </div>
        </div>
    </div>
@endsection
