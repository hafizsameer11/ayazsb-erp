@extends('layouts.erp')

@section('title', 'Financial year')

@section('content')
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            ACCNTS_0005 — Financial year
        </div>
        <div class="p-3">
            <form class="mb-3 grid gap-2 border border-slate-400 bg-[#f7f7f7] p-2 md:grid-cols-5" method="post" action="{{ route('erp.accounts.financial-year.store') }}">
                @csrf
                <label class="erp-field"><span class="erp-label">Year code</span><input class="erp-input" type="text" name="year_code" required></label>
                <label class="erp-field"><span class="erp-label">Start date</span><x-erp-date-input name="start_date" :required="true" /></label>
                <label class="erp-field"><span class="erp-label">End date</span><x-erp-date-input name="end_date" :required="true" /></label>
                <label class="erp-field"><span class="erp-label">Description</span><input class="erp-input" type="text" name="description"></label>
                <div class="flex items-end"><button class="rounded border border-slate-600 bg-slate-200 px-3 py-1 text-xs font-semibold hover:bg-white">Add Year</button></div>
            </form>
            <div class="overflow-x-auto border border-slate-400">
                <table class="w-full min-w-[640px] border-collapse text-left text-[12px]">
                    <thead>
                        <tr class="bg-[#d8d8d8]">
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Trans id</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Year</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Year start</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Year end</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Year desc.</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold text-center">Year close</th>
                            <th class="w-16 border border-slate-400 px-1 py-1 font-semibold"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($financialYears ?? []) as $fy)
                            <tr>
                                <td class="border border-slate-300 px-1 py-1">{{ $fy->id }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ $fy->year_code }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ \App\Support\ErpDate::display($fy->start_date) }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ \App\Support\ErpDate::display($fy->end_date) }}</td>
                                <td class="border border-slate-300 px-1 py-1">{{ $fy->description }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-center">{{ $fy->is_closed ? 'Yes' : 'No' }}</td>
                                <td class="border border-slate-300 px-1 py-1 text-center">Saved</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="border border-slate-300 px-2 py-2 text-slate-500">No financial years yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
