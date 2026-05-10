@extends('layouts.erp')

@section('title', 'Print Voucher')

@section('content')
    <div class="erp-panel border border-slate-500 bg-white p-4 shadow-md">
        <h1 class="mb-2 text-lg font-semibold">Voucher {{ $voucher->voucher_type }} / {{ $voucher->voucher_number }}</h1>
        <div class="mb-3 text-sm text-slate-700">
            Date: {{ $voucher->voucher_date }} | Status: {{ strtoupper($voucher->status) }}
        </div>
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-[#e8e8e8]">
                    <th class="border border-slate-300 px-2 py-1 text-left">Account</th>
                    <th class="border border-slate-300 px-2 py-1 text-left">Description</th>
                    <th class="border border-slate-300 px-2 py-1 text-right">Debit</th>
                    <th class="border border-slate-300 px-2 py-1 text-right">Credit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($voucher->lines as $line)
                    <tr>
                        <td class="border border-slate-300 px-2 py-1 font-mono text-[12px]">{{ $line->account?->code }}@if($line->account) <span class="font-sans text-slate-700">— {{ $line->account->name }}</span>@endif</td>
                        <td class="border border-slate-300 px-2 py-1">{{ $line->description }}</td>
                        <td class="border border-slate-300 px-2 py-1 text-right">{{ number_format((float)$line->debit, 2) }}</td>
                        <td class="border border-slate-300 px-2 py-1 text-right">{{ number_format((float)$line->credit, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-[#f2f2f2]">
                    <td colspan="2" class="border border-slate-300 px-2 py-1 font-semibold">Total</td>
                    <td class="border border-slate-300 px-2 py-1 text-right font-semibold">{{ number_format((float)$voucher->total_debit, 2) }}</td>
                    <td class="border border-slate-300 px-2 py-1 text-right font-semibold">{{ number_format((float)$voucher->total_credit, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection

