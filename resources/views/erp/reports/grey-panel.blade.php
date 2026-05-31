@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @php
        $greyReports = [
            'GREY REPORTS' => [
                'Grey Stock Summary Report',
                'Grey Stock Details Report',
                'Grey Item Ledger Report',
                'Date Wise Grey In / Out',
                'Fabrics Receive (Party Wise Gross Profit)',
                'Fabrics Receive (Weaver Wise Gross Profit)',
            ],
            'CONVERSION REPORTS' => [
                'Party Wise Contract Inward Lot\'s',
                'Date Wise Party Contract Inward Lot\'s',
                'Party Wise Contract Inward Lot With Consumption',
            ],
        ];
        $filterRows = 12;
    @endphp
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            {{ $screen['code'] }} — REPORTS PANEL
        </div>
        <form class="grid gap-3 p-3 lg:grid-cols-[220px_minmax(0,1fr)]" action="{{ route('erp.reports.view', ['screen' => 'grey']) }}" method="get">
            <section class="border border-slate-400 bg-[#f5f5f5] p-2">
                <div class="mb-2 text-[11px] font-bold uppercase text-slate-600">Modules</div>
                <ul class="space-y-1 text-[11px]">
                    <li class="text-slate-500">ACCOUNTS &amp; FINANCE</li>
                    <li class="text-slate-500">YARN MANAGEMENT</li>
                    <li class="rounded bg-sky-200 px-1 py-0.5 font-semibold text-sky-950 ring-1 ring-sky-500">GREY MANAGEMENT</li>
                </ul>
                <div class="mt-3 max-h-[24rem] overflow-auto border border-slate-300 bg-white p-1 text-[11px]">
                    @foreach ($greyReports as $group => $items)
                        <div class="mb-2 font-bold text-slate-700">-{{ $group }}</div>
                        <ul class="mb-2 space-y-0.5 pl-2">
                            @foreach ($items as $item)
                                <li class="hover:bg-sky-50">{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </section>
            <section class="space-y-2 border border-slate-400 bg-[#f4f4f4] p-2">
                <div class="text-[11px] font-bold uppercase text-slate-700">Parameters</div>
                <div class="grid gap-2 md:grid-cols-3">
                    <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="dated" :value="request('dated')" :default-blank="true" picker /></label>
                    <label class="erp-field"><span class="erp-label">From Date</span><x-erp-date-input name="from_date" :value="request('from_date')" :default-blank="true" picker /></label>
                    <label class="erp-field"><span class="erp-label">To Date</span><x-erp-date-input name="to_date" :value="request('to_date')" :default-blank="true" picker /></label>
                </div>
                <table class="w-full max-w-xl border-collapse text-[11px]">
                    @for ($r = 0; $r < $filterRows; $r++)
                        <tr>
                            @for ($c = 0; $c < 3; $c++)
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="filters[{{ $r }}][{{ $c }}]" value="{{ request('filters.'.$r.'.'.$c) }}"></td>
                            @endfor
                        </tr>
                    @endfor
                </table>
                <div class="flex flex-wrap items-center gap-2 pt-2">
                    <button type="submit" class="min-w-[6rem] rounded border border-slate-600 bg-slate-200 px-6 py-2 text-[12px] font-semibold hover:bg-white">View</button>
                    <a href="{{ route('erp.reports.dashboard') }}" class="min-w-[6rem] rounded border border-slate-600 bg-slate-200 px-6 py-2 text-center text-[12px] font-semibold hover:bg-white">Exit</a>
                </div>
            </section>
        </form>
    </div>
@endsection
