@extends('layouts.erp')

@section('title', $voucherTitle)

@section('content')
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        @include('erp.accounts.vouchers.partials.voucher-header', ['formId' => $formId, 'voucherTitle' => $voucherTitle, 'voucherCode' => $voucherCode])
        <form class="space-y-2 p-3" action="{{ route('erp.accounts.vouchers.store', ['voucherType' => strtolower($voucherCode)]) }}" method="post">
            @csrf
            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher master</div>
            @include('erp.accounts.vouchers.partials.voucher-master-fields', ['showCashSummary' => true])

            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher details</div>
            <div class="overflow-x-auto border border-slate-400">
                <table class="w-full min-w-[560px] border-collapse text-left text-[12px]">
                    <thead>
                        <tr class="bg-[#d8d8d8]">
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Account code</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Account description</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Narration</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 10; $i++)
                            <tr>
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full font-mono" type="number" name="lines[{{ $i }}][account_id]"></td>
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" readonly></td>
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" name="lines[{{ $i }}][description]"></td>
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full text-right font-mono" type="number" step="0.01" name="lines[{{ $i }}][amount]" placeholder="0.00"></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <div class="grid gap-2 border border-slate-300 bg-[#f0f0f0] p-2 md:grid-cols-2">
                <label class="erp-field"><span class="erp-label">Cost center</span><input class="erp-input" type="text"></label>
                <label class="erp-field"><span class="erp-label">In English</span><input class="erp-input" type="text" readonly placeholder="Amount in words"></label>
            </div>
            @include('erp.accounts.vouchers.partials.voucher-actions', ['actions' => ['Voucher print', 'Print slip'], 'permissionPrefix' => $permissionPrefix ?? null])
        </form>
    </div>
@endsection
