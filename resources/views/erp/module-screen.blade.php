@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @php
        $isReports = $moduleKey === 'reports';
        $isYarn = $moduleKey === 'yarn';
        $isGrey = $moduleKey === 'grey';
        $fieldMap = match ($moduleKey) {
            'yarn' => ['primaryLabel' => 'Trans #', 'secondaryLabel' => 'Yarn id', 'partyLabel' => 'Supplier / Party'],
            'grey' => ['primaryLabel' => 'Lot #', 'secondaryLabel' => 'Quality code', 'partyLabel' => 'Party / Weaver'],
            default => [],
        };
        $columns = match ($moduleKey) {
            'yarn' => ['Yarn id', 'Yarn description', 'Qty / cones', 'Unit', 'Total weight (Lbs)', 'Rate', 'Amount'],
            'grey' => ['System lot #', 'Quality code', 'Qty (Mtr)', 'Than', 'Rate / Mtr', 'Gross amount', 'Remarks'],
            default => ['Item code', 'Description', 'Qty', 'Unit', 'Rate', 'Amount', 'Notes'],
        };
        $actions = $isReports ? ['View report', 'Export', 'Exit'] : ['Print'];
    @endphp
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            {{ $screen['code'] }} — {{ $screen['label'] }} — {{ $moduleLabel }}
        </div>
        <form class="space-y-2 p-3" action="{{ $moduleKey === 'reports' ? route('erp.reports.view', ['screen' => $screen['slug']]) : route('erp.' . $moduleKey . '.screen.store', ['screen' => $screen['slug']]) }}" method="{{ $moduleKey === 'reports' ? 'get' : 'post' }}">
            @if($moduleKey !== 'reports')
                @csrf
            @endif
            @if ($isReports)
                <div class="grid gap-3 lg:grid-cols-[300px_minmax(0,1fr)]">
                    <section class="border border-slate-400 bg-[#f5f5f5] p-2">
                        <div class="mb-2 text-[11px] font-bold uppercase text-slate-600">Reports list</div>
                        <ul class="space-y-0.5 text-[12px]">
                            <li><a class="erp-tree-link" href="{{ route('erp.reports.view', ['screen' => $screen['slug']]) }}">View report</a></li>
                            <li><a class="erp-tree-link" href="{{ route('erp.reports.export', ['screen' => $screen['slug']]) }}">Export CSV</a></li>
                            <li><a class="erp-tree-link" href="{{ route('erp.reports.print', ['screen' => $screen['slug']]) }}" target="_blank">Print layout</a></li>
                        </ul>
                    </section>
                    <section class="space-y-2">
                        <div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 md:grid-cols-3">
                            <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="dated" /></label>
                            <label class="erp-field"><span class="erp-label">From date</span><x-erp-date-input name="from_date" /></label>
                            <label class="erp-field"><span class="erp-label">To date</span><x-erp-date-input name="to_date" /></label>
                        </div>
                        <div class="grid gap-2 border border-slate-400 bg-[#fdfdfd] p-2 md:grid-cols-2">
                            @for ($i = 0; $i < 12; $i++)
                                <input class="erp-input" type="text" name="p{{ $i + 1 }}" placeholder="Parameter {{ $i + 1 }}">
                            @endfor
                        </div>
                    </section>
                </div>
            @else
                @include('erp.partials.screen-master-fields', ['fieldMap' => $fieldMap])

                <div class="text-[11px] font-semibold uppercase text-slate-600">Detail lines</div>
                @include('erp.partials.screen-detail-grid', ['columns' => $columns, 'namePrefix' => 'lines'])

                @if ($isYarn || $isGrey)
                    <div class="grid gap-2 border border-slate-300 bg-[#f6f6f6] p-2 md:grid-cols-3">
                        <label class="erp-field"><span class="erp-label">{{ $isYarn ? 'Godown' : 'Loom type' }}</span><input class="erp-input" type="text"></label>
                        <label class="erp-field"><span class="erp-label">{{ $isYarn ? 'Yarn tag' : 'Grey tag' }}</span><input class="erp-input" type="text"></label>
                        <label class="erp-field"><span class="erp-label">Voucher type</span><input class="erp-input" type="text" value="{{ $isYarn ? 'YRV' : 'GCV' }}" readonly></label>
                    </div>
                @endif
            @endif

            @include('erp.partials.screen-action-footer', ['actions' => $actions, 'permissionPrefix' => $permissionPrefix ?? null, 'showSave' => ! $isReports])
        </form>
        @if (!$isReports)
            @include('erp.partials.records-history', [
                'historyType' => 'transaction_simple',
                'historyTitle' => 'Saved transactions',
                'recordsHistory' => $recordsHistory ?? null,
                'recordsHistoryGrouped' => $recordsHistoryGrouped ?? collect(),
                'moduleKey' => $moduleKey,
                'screen' => $screen,
                'permissionPrefix' => $permissionPrefix ?? null,
            ])
        @endif
    </div>
@endsection

