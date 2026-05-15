@php
    $paginator = $recordsHistory ?? null;
@endphp
@if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $paginator->hasPages())
    <div class="mt-2 flex flex-wrap items-center justify-between gap-2 border border-slate-400 bg-[#ececec] px-2 py-1 text-[11px]">
        <span class="text-slate-600">
            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </span>
        <div class="flex flex-wrap items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="rounded border border-slate-300 px-2 py-0.5 text-slate-400">Previous</span>
            @else
                <a
                    class="rounded border border-slate-500 bg-white px-2 py-0.5 hover:bg-sky-50"
                    href="{{ $paginator->previousPageUrl() }}"
                >Previous</a>
            @endif
            <span class="px-2 py-0.5 font-medium text-slate-700">
                Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>
            @if ($paginator->hasMorePages())
                <a
                    class="rounded border border-slate-500 bg-white px-2 py-0.5 hover:bg-sky-50"
                    href="{{ $paginator->nextPageUrl() }}"
                >Next</a>
            @else
                <span class="rounded border border-slate-300 px-2 py-0.5 text-slate-400">Next</span>
            @endif
        </div>
    </div>
@endif
