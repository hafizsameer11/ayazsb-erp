@php
    $statement = $statement ?? null;
@endphp
@if ($statement)
    <div class="mb-3 grid gap-2 border border-slate-300 bg-[#f9f9f9] p-3 text-[12px] md:grid-cols-2">
        <div>
            <div class="font-semibold uppercase text-slate-600">Account Statement</div>
            <div><strong>{{ $statement['account']->name }}</strong></div>
            <div>Account Number: <span class="font-mono">{{ $statement['account']->code }}</span></div>
        </div>
        <div class="md:text-right">
            <div>Statement Period: {{ $statement['from_display'] }} to {{ $statement['to_display'] }}</div>
            <div>Opening Balance: {{ number_format($statement['opening_balance'], 2) }} {{ $statement['opening_side'] }}</div>
            <div>Closing Balance: {{ number_format($statement['closing_balance'], 2) }} {{ $statement['closing_side'] }}</div>
            <div class="text-slate-500">Print: {{ now()->format('d-m-Y h:i:s A') }}</div>
        </div>
    </div>
    <table class="w-full min-w-[920px] border-collapse text-[12px]">
        <thead>
            <tr class="bg-[#d8d8d8]">
                <th class="border border-slate-400 px-1 py-1">Date</th>
                <th class="border border-slate-400 px-1 py-1">Type</th>
                <th class="border border-slate-400 px-1 py-1">V.#</th>
                <th class="border border-slate-400 px-1 py-1">Narration</th>
                <th class="border border-slate-400 px-1 py-1 text-right">Debit</th>
                <th class="border border-slate-400 px-1 py-1 text-right">Credit</th>
                <th class="border border-slate-400 px-1 py-1 text-right">Balance</th>
                <th class="border border-slate-400 px-1 py-1">Dr/Cr</th>
                <th class="border border-slate-400 px-1 py-1">Cost Center</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($statement['rows'] as $row)
                <tr>
                    <td class="border border-slate-300 px-1 py-1">{{ $row['date'] }}</td>
                    <td class="border border-slate-300 px-1 py-1">{{ $row['type'] }}</td>
                    <td class="border border-slate-300 px-1 py-1 font-mono">{{ $row['voucher_no'] }}</td>
                    <td class="border border-slate-300 px-1 py-1">{{ $row['narration'] }}</td>
                    <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ $row['debit'] !== null ? number_format((float) $row['debit'], 2) : '' }}</td>
                    <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ $row['credit'] !== null ? number_format((float) $row['credit'], 2) : '' }}</td>
                    <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format((float) $row['balance'], 2) }}</td>
                    <td class="border border-slate-300 px-1 py-1">{{ $row['balance_side'] }}</td>
                    <td class="border border-slate-300 px-1 py-1">{{ $row['contract_no'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-[#f2f2f2] font-semibold">
                <td colspan="4" class="border border-slate-300 px-1 py-1">Total ({{ $statement['totals']['count'] }} transaction(s))</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($statement['totals']['debit'], 2) }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($statement['totals']['credit'], 2) }}</td>
                <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($statement['closing_balance'], 2) }}</td>
                <td class="border border-slate-300 px-1 py-1">{{ $statement['closing_side'] }}</td>
                <td class="border border-slate-300 px-1 py-1"></td>
            </tr>
        </tfoot>
    </table>
    <p class="mt-2 text-[11px] text-slate-600">System generated statement and does not require signature. End of Statement.</p>
@endif
