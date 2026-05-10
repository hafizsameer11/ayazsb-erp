@extends('layouts.erp')

@section('title', $voucherTitle)

@section('content')
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        @include('erp.accounts.vouchers.partials.voucher-header', ['formId' => $formId, 'voucherTitle' => $voucherTitle, 'voucherCode' => $voucherCode])
        <form class="space-y-2 p-3" action="{{ route('erp.accounts.vouchers.store', ['voucherType' => strtolower($voucherCode)]) }}" method="post">
            @csrf
            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher master</div>
            @include('erp.accounts.vouchers.partials.voucher-master-fields', ['showCashSummary' => true])

            <p class="text-[11px] text-slate-600"><abbr title="Cash voucher" class="cursor-help font-semibold">CV</abbr> — combined cash entry: <strong>Debit</strong> and <strong>Credit</strong> columns (same as journal).</p>

            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher details</div>
            <div class="overflow-x-auto border border-slate-400">
                <table class="w-full min-w-[520px] border-collapse text-left text-[12px]">
                    <thead>
                        <tr class="bg-[#d8d8d8]">
                            <th class="min-w-[200px] border border-slate-400 px-1 py-1 font-semibold">Account</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Narration</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Debit</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 10; $i++)
                            <tr>
                                @include('erp.accounts.vouchers.partials.line-account-select', ['i' => $i, 'voucherCode' => $voucherCode])
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" name="lines[{{ $i }}][description]"></td>
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full text-right font-mono" type="number" step="0.01" name="lines[{{ $i }}][debit]" placeholder="0.00"></td>
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full text-right font-mono" type="number" step="0.01" name="lines[{{ $i }}][credit]" placeholder="0.00"></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            @include('erp.accounts.vouchers.partials.voucher-actions', ['actions' => ['Voucher print'], 'permissionPrefix' => $permissionPrefix ?? null])
        </form>
        @include('erp.accounts.vouchers.partials.voucher-recent-saved')
    </div>
@endsection
