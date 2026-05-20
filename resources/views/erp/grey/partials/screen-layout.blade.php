@php
    $editingTransaction = $editingTransaction ?? null;
    $editingContract = $editingContract ?? null;
    $moduleKey = $moduleKey ?? 'grey';
    $cancelUrl = route('erp.' . $moduleKey . '.screen', array_merge(
        ['screen' => $screen['slug']],
        \App\Support\RecordHistory::historyQuery(request()),
    ));
    $meta = $editingTransaction?->meta ?? [];
    $line0 = $editingTransaction?->lines->first();
    $lineMeta = $line0?->meta ?? [];
@endphp

<div class="flex min-h-[calc(100vh-8rem)] flex-col gap-0">
    <div class="erp-panel shrink-0 border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            {{ $screen['code'] }} — {{ strtoupper($screen['label']) }}
        </div>
        {{ $formSlot ?? '' }}
    </div>
    <div class="erp-panel mt-0 flex min-h-0 flex-1 flex-col border border-t-0 border-slate-500 bg-white shadow-md">
        @include('erp.partials.records-history', [
            'historyType' => $historyType ?? 'transaction',
            'historyTitle' => $historyTitle ?? 'Record history',
            'recordsForDay' => $recordsForDay ?? collect(),
            'historyDate' => $historyDate ?? null,
            'historyNav' => $historyNav ?? [],
            'moduleKey' => $moduleKey,
            'screen' => $screen,
            'permissionPrefix' => $permissionPrefix ?? ('grey.' . $screen['slug']),
        ])
    </div>
</div>
