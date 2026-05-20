@extends('layouts.erp')

@section('title', $title)

@section('content')
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-sm font-semibold text-slate-800">{{ $title }}</div>
        <div class="p-3">
            @include('erp.reports.partials.report-actions')
            @if ($notice)
                <p class="rounded border border-amber-400 bg-amber-50 px-3 py-2 text-[12px] text-amber-900">{{ $notice }}</p>
            @else
                <div class="overflow-x-auto border border-slate-400">
                    @include('erp.reports.partials.accounts-statement-table', ['statement' => $statement])
                </div>
            @endif
        </div>
    </div>
@endsection
