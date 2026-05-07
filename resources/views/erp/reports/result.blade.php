@extends('layouts.erp')

@section('title', $title)

@section('content')
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-sm font-semibold text-slate-800">{{ $title }}</div>
        <div class="p-3">
            <div class="mb-2 flex gap-2">
                <a class="rounded border border-slate-500 bg-slate-200 px-2 py-1 text-xs" href="{{ route('erp.reports.export', ['screen' => $screen] + $filters) }}">Export CSV</a>
                <a class="rounded border border-slate-500 bg-slate-200 px-2 py-1 text-xs" href="{{ route('erp.reports.print', ['screen' => $screen] + $filters) }}" target="_blank">Print</a>
            </div>
            <div class="overflow-x-auto border border-slate-400">
                <table class="w-full border-collapse text-[12px]">
                    <thead>
                        <tr class="bg-[#d8d8d8]">
                            <th class="border border-slate-400 px-1 py-1">Date</th>
                            <th class="border border-slate-400 px-1 py-1">Reference</th>
                            <th class="border border-slate-400 px-1 py-1">Party</th>
                            <th class="border border-slate-400 px-1 py-1">Status</th>
                            <th class="border border-slate-400 px-1 py-1 text-right">Qty</th>
                            <th class="border border-slate-400 px-1 py-1 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td class="border border-slate-300 px-1 py-1">{{ $row['date'] }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ $row['reference'] }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ $row['party'] }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ $row['status'] }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float)$row['qty'], 2) }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float)$row['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="border border-slate-300 px-2 py-2 text-slate-500">No report data.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-[#f2f2f2]">
                            <td colspan="4" class="border border-slate-300 px-1 py-1 font-semibold">Totals</td>
                            <td class="border border-slate-300 px-1 py-1 text-right font-semibold">{{ number_format((float)$totals['qty'], 2) }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-right font-semibold">{{ number_format((float)$totals['amount'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

