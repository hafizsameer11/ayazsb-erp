@extends('layouts.erp')

@section('title', $voucherTitle)

@section('content')
    <div class="erp-panel erp-voucher-sheet flex min-h-[calc(100vh-9rem)] flex-col border border-slate-500 bg-white shadow-md">
        @include('erp.accounts.vouchers.partials.voucher-header', ['formId' => $formId, 'voucherTitle' => $voucherTitle, 'voucherCode' => $voucherCode])
        <form class="erp-voucher-entry shrink-0 space-y-1 p-2" action="{{ route('erp.accounts.vouchers.store', ['voucherType' => strtolower($voucherCode)]) }}" method="post">
            @csrf
            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher master</div>
            @include('erp.accounts.vouchers.partials.voucher-master-fields', ['showBank' => true])

            <p class="text-[11px] text-slate-600"><abbr title="Bank receipt voucher" class="cursor-help font-semibold">BRV</abbr> — bank receipt: <strong>Debit</strong> and <strong>Credit</strong> per line (same idea as CR).</p>

            <div class="text-[11px] font-semibold uppercase text-slate-600">Voucher details</div>
            <div class="overflow-x-auto border border-slate-400">
                <table class="w-full min-w-[960px] border-collapse text-left text-[12px]">
                    <thead>
                        <tr class="bg-[#d8d8d8]">
                            <th class="min-w-[200px] border border-slate-400 px-1 py-1 font-semibold">Account</th>
                            <th class="w-28 border border-slate-400 px-1 py-1 font-semibold">Instrument type</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Instrument #</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Instrument date</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Title</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Narration</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Debit</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < 6; $i++)
                            <tr>
                                @include('erp.accounts.vouchers.partials.line-account-select', ['i' => $i, 'voucherCode' => $voucherCode])
                                <td class="border border-slate-300 p-0"><select class="erp-input w-full" name="lines[{{ $i }}][tag]"><option></option></select></td>
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text"></td>
                                <td class="border border-slate-300 p-0"><input class="erp-input erp-date-input w-full" type="text" placeholder="DD-MM-YYYY" autocomplete="off"></td>
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text"></td>
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" name="lines[{{ $i }}][description]"></td>
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full text-right font-mono" type="text" name="lines[{{ $i }}][debit]" placeholder="0.00" inputmode="decimal"></td>
                                <td class="border border-slate-300 p-0"><input class="erp-input w-full text-right font-mono" type="text" name="lines[{{ $i }}][credit]" placeholder="0.00" inputmode="decimal"></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-end gap-3 border border-slate-300 bg-[#f0f0f0] p-2">
                <input class="erp-input w-16 font-mono" type="text" value="0" readonly>
                <label class="erp-field min-w-[120px]"><span class="erp-label">Cost center</span><input class="erp-input" type="text"></label>
                <label class="erp-field min-w-[200px] flex-1"><span class="erp-label">Account description</span><input class="erp-input w-full" type="text" readonly></label>
            </div>
            @include('erp.accounts.vouchers.partials.voucher-actions', ['actions' => ['Print voucher', 'Print slip'], 'permissionPrefix' => $permissionPrefix ?? null])
        </form>
        @include('erp.accounts.vouchers.partials.voucher-recent-saved')
    </div>
@endsection
