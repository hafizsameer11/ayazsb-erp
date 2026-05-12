@php($rows = $recentTransactions ?? collect())

<div class="border-t border-slate-300 p-3">
    <div class="mb-2 text-[11px] font-semibold uppercase text-slate-600">Recent transactions</div>
    <div class="overflow-x-auto border border-slate-400">
        <table class="w-full min-w-[820px] border-collapse text-left text-[12px]">
            <thead>
                <tr class="bg-[#d8d8d8]">
                    <th class="border border-slate-400 px-1 py-1">No</th>
                    <th class="border border-slate-400 px-1 py-1">Date</th>
                    <th class="border border-slate-400 px-1 py-1">Party</th>
                    <th class="border border-slate-400 px-1 py-1">Contract</th>
                    <th class="border border-slate-400 px-1 py-1">Status</th>
                    <th class="border border-slate-400 px-1 py-1 text-right">Qty</th>
                    <th class="border border-slate-400 px-1 py-1 text-right">Amount</th>
                    <th class="border border-slate-400 px-1 py-1"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $transaction)
                    <tr>
                        <td class="border border-slate-300 px-1 py-1 font-mono">{{ $transaction->trans_no }}</td>
                        <td class="border border-slate-300 px-1 py-1">{{ $transaction->trans_date }}</td>
                        <td class="border border-slate-300 px-1 py-1">{{ $transaction->party?->name ?? '-' }}</td>
                        <td class="border border-slate-300 px-1 py-1">
                            {{ $transaction->yarnContract?->contract_no
                                ?? trim(($transaction->fromYarnContract?->contract_no ?? '') . ' -> ' . ($transaction->toYarnContract?->contract_no ?? ''), ' ->')
                                ?: '-' }}
                        </td>
                        <td class="border border-slate-300 px-1 py-1">{{ strtoupper($transaction->status) }}</td>
                        <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $transaction->total_qty, 2) }}</td>
                        <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $transaction->total_amount, 2) }}</td>
                        <td class="border border-slate-300 px-1 py-1 text-right">
                            @if(($transaction->status ?? '') !== 'posted')
                                <form method="post" action="{{ route('erp.' . $moduleKey . '.screen.post', ['screen' => $screen['slug'], 'transaction' => $transaction->id]) }}" class="inline">
                                    @csrf
                                    <button class="rounded border border-slate-500 bg-slate-100 px-2 py-0.5 text-[11px]">Post</button>
                                </form>
                            @endif
                            <a href="{{ route('erp.' . $moduleKey . '.screen.print', ['screen' => $screen['slug'], 'transaction' => $transaction->id]) }}" target="_blank" class="ml-1 rounded border border-slate-500 bg-slate-100 px-2 py-0.5 text-[11px]">Print</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="border border-slate-300 px-2 py-2 text-slate-500">No records yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
