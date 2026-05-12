@extends('layouts.erp')

@section('title', 'Print Transaction')

@section('content')
    <div class="erp-panel border border-slate-500 bg-white p-4 shadow-md">
        <h1 class="mb-2 text-lg font-semibold">{{ strtoupper($transaction->module) }} {{ $transaction->screen_slug }} / {{ $transaction->trans_no }}</h1>
        <div class="mb-3 text-sm text-slate-700">
            Date: {{ $transaction->trans_date }} | Party: {{ $transaction->party?->name ?? '-' }} | Status: {{ strtoupper($transaction->status) }}
        </div>
        @if($transaction->module === 'yarn')
            <div class="mb-3 grid gap-2 text-sm text-slate-700 md:grid-cols-2">
                <div>Contract: {{ $transaction->yarnContract?->contract_no ?? '-' }}</div>
                <div>Transfer: {{ $transaction->fromYarnContract?->contract_no ?? '-' }} -> {{ $transaction->toYarnContract?->contract_no ?? '-' }}</div>
                <div>From godown: {{ $transaction->fromGodown?->name ?? '-' }}</div>
                <div>To godown: {{ $transaction->toGodown?->name ?? '-' }}</div>
            </div>
        @endif
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-[#e8e8e8]">
                    <th class="border border-slate-300 px-2 py-1 text-left">Item</th>
                    <th class="border border-slate-300 px-2 py-1 text-left">Description</th>
                    <th class="border border-slate-300 px-2 py-1 text-right">Qty</th>
                    <th class="border border-slate-300 px-2 py-1 text-right">Weight</th>
                    <th class="border border-slate-300 px-2 py-1 text-right">Rate</th>
                    <th class="border border-slate-300 px-2 py-1 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->lines as $line)
                    <tr>
                        <td class="border border-slate-300 px-2 py-1">{{ $line->item?->code }}</td>
                        <td class="border border-slate-300 px-2 py-1">{{ $line->description }}</td>
                        <td class="border border-slate-300 px-2 py-1 text-right">{{ number_format((float)$line->qty, 2) }}</td>
                        <td class="border border-slate-300 px-2 py-1 text-right">{{ number_format((float)$line->weight_lbs, 2) }}</td>
                        <td class="border border-slate-300 px-2 py-1 text-right">{{ number_format((float)$line->rate, 2) }}</td>
                        <td class="border border-slate-300 px-2 py-1 text-right">{{ number_format((float)$line->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-[#f2f2f2]">
                    <td colspan="2" class="border border-slate-300 px-2 py-1 font-semibold">Total</td>
                    <td class="border border-slate-300 px-2 py-1 text-right font-semibold">{{ number_format((float)$transaction->total_qty, 2) }}</td>
                    <td class="border border-slate-300 px-2 py-1"></td>
                    <td class="border border-slate-300 px-2 py-1"></td>
                    <td class="border border-slate-300 px-2 py-1 text-right font-semibold">{{ number_format((float)$transaction->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection

