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
        @php
            $editingTransaction = $editingTransaction ?? null;
            if ($isReports) {
                $formAction = route('erp.reports.view', ['screen' => $screen['slug']]);
                $formMethod = 'get';
            } else {
                $formAction = $editingTransaction
                    ? route('erp.' . $moduleKey . '.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction])
                    : route('erp.' . $moduleKey . '.screen.store', ['screen' => $screen['slug']]);
                $formMethod = 'post';
            }
        @endphp
        <form
            class="space-y-2 p-3"
            action="{{ $formAction }}"
            method="{{ $formMethod }}"
            @if($moduleKey !== 'reports') data-erp-ajax-save @endif
            @if($editingTransaction) data-erp-editing="1" @endif
        >
            @if($moduleKey !== 'reports')
                @csrf
                <div data-erp-form-feedback class="hidden" aria-live="polite"></div>
                @if ($editingTransaction)
                    @method('PATCH')
                    @include('erp.partials.erp-editing-banner', [
                        'editingLabel' => $editingTransaction->trans_no,
                        'cancelUrl' => route('erp.' . $moduleKey . '.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request()))),
                    ])
                @endif
            @endif
            @if ($isReports)
                @php
                    $accountsReportTypes = $accountsReportTypes ?? \App\Support\ReportFilters::ACCOUNTS_REPORTS;
                    $selectedReport = request('report', $screen['slug'] === 'accounts' ? 'account-statement' : 'summary');
                    $sidebarQuery = array_filter(
                        request()->only(['from_date', 'to_date', 'account_id', 'account_query', 'status']),
                        static fn ($value) => $value !== null && $value !== '',
                    );
                @endphp
                <div class="grid gap-3 lg:grid-cols-[220px_minmax(0,1fr)]">
                    <section class="border border-slate-400 bg-[#f5f5f5] p-2">
                        @if ($screen['slug'] === 'accounts')
                            <div class="mb-2 text-[11px] font-bold uppercase text-slate-600">Report type</div>
                            <ul class="space-y-0.5 text-[12px]">
                                @foreach ($accountsReportTypes as $reportKey => $reportLabel)
                                    <li>
                                        <a
                                            href="{{ route('erp.reports.screen', array_merge(['screen' => 'accounts', 'report' => $reportKey], $sidebarQuery)) }}"
                                            class="erp-tree-link block rounded px-1 py-0.5 {{ $selectedReport === $reportKey ? 'bg-sky-200 font-semibold text-sky-950 ring-1 ring-sky-500' : 'hover:bg-white' }}"
                                        >{{ $reportLabel }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="mb-2 text-[11px] font-bold uppercase text-slate-600">{{ $moduleLabel }}</div>
                            <p class="text-[11px] text-slate-600">Set filters, then use View report below.</p>
                        @endif
                    </section>
                    <section class="space-y-2">
                        @include('erp.partials.reports-filter-panel', [
                            'screen' => $screen,
                            'postableAccounts' => $postableAccounts ?? collect(),
                            'inventoryScreenOptions' => $inventoryScreenOptions ?? [],
                        ])
                    </section>
                </div>
            @else
                @include('erp.partials.screen-master-fields', [
                    'fieldMap' => $fieldMap,
                    'editingTransaction' => $editingTransaction,
                    'accountParties' => $accountParties ?? collect(),
                ])

                <div class="text-[11px] font-semibold uppercase text-slate-600">Detail lines</div>
                @include('erp.partials.screen-detail-grid', [
                    'columns' => $columns,
                    'namePrefix' => 'lines',
                    'editingLines' => $editingTransaction?->lines ?? collect(),
                ])

                @if ($isYarn || $isGrey)
                    <div class="grid gap-2 border border-slate-300 bg-[#f6f6f6] p-2 md:grid-cols-3">
                        <label class="erp-field"><span class="erp-label">{{ $isYarn ? 'Godown' : 'Loom type' }}</span><input class="erp-input" type="text"></label>
                        <label class="erp-field"><span class="erp-label">{{ $isYarn ? 'Yarn tag' : 'Grey tag' }}</span><input class="erp-input" type="text"></label>
                        <label class="erp-field"><span class="erp-label">Voucher type</span><input class="erp-input" type="text" value="{{ $isYarn ? 'YRV' : 'GCV' }}" readonly></label>
                    </div>
                @endif
            @endif

            @include('erp.partials.screen-action-footer', [
                'actions' => $actions,
                'permissionPrefix' => $permissionPrefix ?? null,
                'showSave' => ! $isReports,
                'isReports' => $isReports,
                'reportScreen' => $screen['slug'] ?? 'accounts',
            ])
        </form>
        @if (!$isReports)
            @include('erp.partials.records-history', [
                'historyType' => 'transaction_simple',
                'historyTitle' => 'Saved transactions',
                'recordsForDay' => $recordsForDay ?? collect(),
                'historyDate' => $historyDate ?? null,
                'historyNav' => $historyNav ?? [],
                'moduleKey' => $moduleKey,
                'screen' => $screen,
                'permissionPrefix' => $permissionPrefix ?? null,
            ])
        @endif
    </div>
@endsection

