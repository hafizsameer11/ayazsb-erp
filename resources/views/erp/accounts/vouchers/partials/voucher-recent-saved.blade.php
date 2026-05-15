@php
    $list = $recentVouchers ?? collect();
@endphp
<div class="erp-voucher-history flex min-h-0 flex-1 flex-col border-t border-slate-300 p-2">
    <div class="mb-2 text-[11px] font-semibold uppercase text-slate-600">Posted vouchers (this screen)</div>
    @if ($list->isEmpty())
        <p class="border border-slate-300 bg-[#f9f9f9] px-2 py-3 text-[12px] text-slate-600">
            No posted vouchers for this type yet. Use <strong>Post voucher</strong> above; the last 20 documents will list here.
        </p>
    @else
        <div class="min-h-[280px] flex-1 overflow-y-auto overflow-x-auto border border-slate-400">
            <table class="w-full min-w-[640px] border-collapse text-left text-[12px]">
                <thead>
                    <tr class="bg-[#d8d8d8]">
                        <th class="border border-slate-400 px-1 py-1 font-semibold">Number</th>
                        <th class="border border-slate-400 px-1 py-1 font-semibold">Date</th>
                        <th class="border border-slate-400 px-1 py-1 font-semibold">Status</th>
                        <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Debit</th>
                        <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Credit</th>
                        <th class="border border-slate-400 px-1 py-1 font-semibold">Difference</th>
                        <th class="border border-slate-400 px-1 py-1 font-semibold"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($list as $v)
                        @php
                            $dr = (float) $v->total_debit;
                            $cr = (float) $v->total_credit;
                            $diff = $dr - $cr;
                        @endphp
                        <tr>
                            <td class="border border-slate-300 px-1 py-1 font-mono">{{ $v->voucher_number }}</td>
                            <td class="border border-slate-300 px-1 py-1">{{ \App\Support\ErpDate::display($v->voucher_date) }}</td>
                            <td class="border border-slate-300 px-1 py-1">{{ strtoupper($v->status) }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($dr, 2) }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($cr, 2) }}</td>
                            <td class="border border-slate-300 px-1 py-1 font-mono @if (abs($diff) > 0.009) text-amber-800 @endif">
                                {{ number_format($diff, 2) }}
                            </td>
                            <td class="border border-slate-300 px-1 py-1">
                                @if (auth()->user()?->hasPermission(($permissionPrefix ?? 'accounts.vouchers.jv') . '.print'))
                                    <a
                                        class="rounded border border-slate-500 bg-white px-2 py-0.5 text-[11px] hover:bg-sky-50"
                                        href="{{ route('erp.accounts.vouchers.print', $v) }}"
                                        target="_blank"
                                        rel="noopener"
                                    >Print</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <p class="mt-2 text-[11px] text-slate-600">
        Vouchers are posted immediately. Debit and credit must be equal before the system accepts the document.
    </p>
</div>
