@extends('layouts.erp')

@section('title', $voucherTitle)

@section('content')
    <div class="erp-panel erp-voucher-sheet flex min-h-[calc(100vh-9rem)] flex-col border border-slate-500 bg-white shadow-md">
        @include('erp.accounts.vouchers.partials.voucher-header', ['formId' => $formId, 'voucherTitle' => $voucherTitle, 'voucherCode' => $voucherCode])
        <form class="erp-voucher-entry shrink-0 space-y-1 p-2" action="{{ route('erp.accounts.vouchers.store', ['voucherType' => strtolower($voucherCode)]) }}" method="post">
            @csrf
            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher master</div>
            @include('erp.accounts.vouchers.partials.voucher-master-fields', ['showCashSummary' => true])

            <p class="text-[11px] text-slate-600"><abbr title="Cash payment voucher" class="cursor-help font-semibold">CP</abbr> — cash payment: use <strong>Debit</strong> and <strong>Credit</strong> like a journal (e.g. expense debit, cash/bank credit).</p>

            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher details</div>
            <div class="overflow-x-auto border border-slate-400">
                <table class="w-full min-w-[640px] border-collapse text-left text-[12px]">
                    <thead>
                        <tr class="bg-[#d8d8d8]">
                            <th class="min-w-[200px] border border-slate-400 px-1 py-1 font-semibold">Account</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Narration</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Debit</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 6; $i++)
                            <tr>
                                @include('erp.accounts.vouchers.partials.line-account-select', ['i' => $i, 'voucherCode' => $voucherCode])
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" name="lines[{{ $i }}][description]"></td>
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full text-right font-mono" type="text" name="lines[{{ $i }}][debit]" placeholder="0.00" inputmode="decimal"></td>
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full text-right font-mono" type="text" name="lines[{{ $i }}][credit]" placeholder="0.00" inputmode="decimal"></td>
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
        @include('erp.accounts.vouchers.partials.voucher-recent-saved')
    </div>
@endsection
