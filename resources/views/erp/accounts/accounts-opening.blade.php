@extends('layouts.erp')

@section('title', 'Accounts opening')

@section('content')
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            ACCNTS_0004 — Accounts opening
        </div>
        @include('erp.partials.erp-form-toolbar')

        <div class="p-3">
            <form class="mb-3 grid gap-2 border border-slate-400 bg-[#f7f7f7] p-2 md:grid-cols-6" method="post" action="{{ route('erp.accounts.opening.store') }}">
                @csrf
                <label class="erp-field"><span class="erp-label">Voucher date</span><input class="erp-input" type="date" name="voucher_date" value="{{ now()->format('Y-m-d') }}" required></label>
                <label class="erp-field"><span class="erp-label">Financial year</span><select class="erp-input" name="financial_year_id">@foreach(($financialYears ?? []) as $fy)<option value="{{ $fy->id }}">{{ $fy->year_code }}</option>@endforeach</select></label>
                <label class="erp-field"><span class="erp-label">Account</span><select class="erp-input" name="account_id">@foreach(($accounts ?? []) as $acc)<option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>@endforeach</select></label>
                <label class="erp-field"><span class="erp-label">Debit</span><input class="erp-input" type="number" step="0.01" name="debit" value="0"></label>
                <label class="erp-field"><span class="erp-label">Credit</span><input class="erp-input" type="number" step="0.01" name="credit" value="0"></label>
                <div class="flex items-end"><button class="rounded border border-slate-600 bg-slate-200 px-3 py-1 text-xs font-semibold hover:bg-white">Add Opening</button></div>
                <label class="erp-field md:col-span-6"><span class="erp-label">Narration</span><input class="erp-input" type="text" name="narration" value="Opening balance"></label>
            </form>
            <div class="overflow-x-auto border border-slate-400">
                <table class="w-full min-w-[720px] border-collapse text-left text-[12px]">
                    <thead>
                        <tr class="bg-[#d8d8d8]">
                            <th class="border border-slate-400 px-1 py-1 font-semibold">V.ID</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">V date</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Fin year</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Account code</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Narration</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Debit</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($openings ?? []) as $opening)
                            <tr>
                                <td class="border border-slate-300 px-1 py-1">{{ $opening->id }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ $opening->voucher_date }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ $opening->financialYear?->year_code }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ $opening->account?->code }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ $opening->narration }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float)$opening->debit, 2) }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float)$opening->credit, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="border border-slate-300 px-2 py-2 text-slate-500">No opening entries.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 grid gap-2 border border-slate-400 bg-[#f0f0f0] p-2 md:grid-cols-2">
                <label class="flex flex-col gap-0.5 text-[11px] font-medium text-slate-700">
                    Account name
                    <input class="erp-input" type="text" readonly placeholder="From account code">
                </label>
                <label class="flex flex-col gap-0.5 text-[11px] font-medium text-slate-700">
                    Amount difference
                    <input class="erp-input text-right font-mono" type="text" readonly value="0.00">
                </label>
            </div>
            <div class="mt-2 flex justify-end gap-4 border-t border-slate-300 pt-2 text-[12px] font-mono">
                <span>Debit total: <input class="erp-input ml-1 w-28 text-right" type="text" readonly value="0.00"></span>
                <span>Credit total: <input class="erp-input ml-1 w-28 text-right" type="text" readonly value="0.00"></span>
            </div>
        </div>
    </div>
@endsection
