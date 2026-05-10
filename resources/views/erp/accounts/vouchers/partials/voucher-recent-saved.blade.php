@php
    $prefix = $permissionPrefix ?? 'accounts.vouchers.jv';
    $list = $recentVouchers ?? collect();
@endphp
<div class="border-t border-slate-300 p-3">
    <div class="mb-2 text-[11px] font-semibold uppercase text-slate-600">Saved vouchers (this screen)</div>
    @if ($list->isEmpty())
        <p class="border border-slate-300 bg-[#f9f9f9] px-2 py-3 text-[12px] text-slate-600">
            No vouchers for this type yet. Use <strong>Save voucher</strong> above; the last 20 saved documents will list here.
        </p>
    @else
        <div class="overflow-x-auto border border-slate-400">
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
                            <td class="border border-slate-300 px-1 py-1">{{ $v->voucher_date }}</td>
                            <td class="border border-slate-300 px-1 py-1">{{ strtoupper($v->status) }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($dr, 2) }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($cr, 2) }}</td>
                            <td class="border border-slate-300 px-1 py-1 font-mono @if (abs($diff) > 0.009) text-amber-800 @endif">
                                {{ number_format($diff, 2) }}
                            </td>
                            <td class="border border-slate-300 px-1 py-1">
                                <div class="flex flex-wrap gap-1">
                                    @allowed($prefix . '.print')
                                        <a
                                            class="rounded border border-slate-500 bg-white px-2 py-0.5 text-[11px] hover:bg-sky-50"
                                            href="{{ route('erp.accounts.vouchers.print', $v) }}"
                                            target="_blank"
                                            rel="noopener"
                                        >Print</a>
                                    @endallowed
                                    @if ($v->status === 'draft' && auth()->user()?->hasPermission($prefix . '.post'))
                                        <form class="inline" method="post" action="{{ route('erp.accounts.vouchers.post', $v) }}" onsubmit="return confirm('Post this voucher? Debit and credit must match.');">
                                            @csrf
                                            <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-2 py-0.5 text-[11px] font-semibold hover:bg-white">Post</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <p class="mt-2 text-[11px] text-slate-600">
        <strong>Draft (Save):</strong> debit and credit do not need to match. <strong>Post:</strong> system requires debit = credit before posting to the ledger.
    </p>
</div>
