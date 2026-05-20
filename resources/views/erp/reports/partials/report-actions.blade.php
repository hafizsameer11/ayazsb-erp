@php
    $filters = $filters ?? [];
@endphp
<div class="mb-3 flex flex-wrap gap-2">
    <a class="rounded border border-slate-500 bg-slate-200 px-2 py-1 text-xs hover:bg-white" href="{{ route('erp.reports.screen', ['screen' => $screen]) }}">Back to filters</a>
    <a class="rounded border border-slate-500 bg-slate-200 px-2 py-1 text-xs hover:bg-white" href="{{ route('erp.reports.export', ['screen' => $screen] + $filters) }}">Export CSV</a>
    <a class="rounded border border-slate-500 bg-slate-200 px-2 py-1 text-xs hover:bg-white" href="{{ route('erp.reports.print', ['screen' => $screen] + $filters) }}" target="_blank" rel="noopener">Print</a>
</div>
