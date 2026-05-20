@php
    $account = $account ?? null;
    $from_display = $from_display ?? '';
    $to_display = $to_display ?? '';
    $rows = $rows ?? [];
    $totals = $totals ?? [];
@endphp
<div class="mb-3 border border-slate-300 bg-[#f9f9f9] p-3 text-[12px]">
    <div class="font-semibold uppercase text-slate-600">General Ledger</div>
    <div>Period: {{ $from_display }} to {{ $to_display }}</div>
    @if ($account)
        <div>Account: <span class="font-mono">{{ $account->code }}</span> — {{ $account->name }}</div>
    @else
        <div>All sub-ledger accounts</div>
    @endif
</div>
<table class="w-full min-w-[980px] border-collapse text-[12px]">
    <thead>
        <tr class="bg-[#d8d8d8]">
            <th class="border border-slate-400 px-1 py-1">Date</th>
            <th class="border border-slate-400 px-1 py-1">Account</th>
            <th class="border border-slate-400 px-1 py-1">Type</th>
            <th class="border border-slate-400 px-1 py-1">Voucher #</th>
            <th class="border border-slate-400 px-1 py-1">Narration</th>
            <th class="border border-slate-400 px-1 py-1">Cost Center</th>
            <th class="border border-slate-400 px-1 py-1 text-right">Debit</th>
            <th class="border border-slate-400 px-1 py-1 text-right">Credit</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td class="border border-slate-300 px-1 py-1">{{ $row['date'] }}</td>
                <td class="border border-slate-300 px-1 py-1 font-mono">{{ $row['account_code'] }} — {{ $row['account_name'] }}</td>
                <td class="border border-slate-300 px-1 py-1">{{ $row['type'] }}</td>
                <td class="border border-slate-300 px-1 py-1 font-mono">{{ $row['voucher_no'] }}</td>
                <td class="border border-slate-300 px-1 py-1">{{ $row['narration'] }}</td>
                <td class="border border-slate-300 px-1 py-1">{{ $row['contract_no'] }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ $row['debit'] !== null ? number_format((float) $row['debit'], 2) : '' }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ $row['credit'] !== null ? number_format((float) $row['credit'], 2) : '' }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="border border-slate-300 px-2 py-2 text-slate-500">No ledger entries for the selected filters.</td></tr>
        @endforelse
    </tbody>
    @if ($rows !== [])
        <tfoot>
            <tr class="bg-[#f2f2f2] font-semibold">
                <td colspan="6" class="border border-slate-300 px-1 py-1">Total ({{ $totals['count'] }} lines)</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($totals['debit'], 2) }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($totals['credit'], 2) }}</td>
            </tr>
        </tfoot>
    @endif
</table>
