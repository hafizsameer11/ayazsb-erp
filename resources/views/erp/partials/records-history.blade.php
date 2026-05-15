@php
    use App\Support\ErpDate;

    $historyType = $historyType ?? 'voucher';
    $historyTitle = $historyTitle ?? 'Saved records';
    $recordsHistory = $recordsHistory ?? null;
    $recordsHistoryGrouped = $recordsHistoryGrouped ?? collect();
    $historyScrollMin = $historyScrollMin ?? 'min-h-[240px]';
    $historyFooter = $historyFooter ?? null;
    $isEmpty = ! $recordsHistory || $recordsHistory->isEmpty();
@endphp

<div class="erp-records-history flex min-h-0 flex-1 flex-col border-t border-slate-300 p-2">
    <div class="mb-2 flex flex-wrap items-end justify-between gap-2">
        <div class="text-[11px] font-semibold uppercase text-slate-600">{{ $historyTitle }}</div>
        <form method="get" action="{{ request()->url() }}" class="flex flex-wrap items-end gap-2 text-[11px]">
            <label class="flex flex-col gap-0.5 font-medium text-slate-700">
                From
                <input
                    class="erp-input erp-date-input w-[7.5rem]"
                    type="text"
                    name="history_from"
                    value="{{ request('history_from', '') }}"
                    placeholder="DD-MM-YYYY"
                    autocomplete="off"
                    inputmode="numeric"
                    maxlength="10"
                >
            </label>
            <label class="flex flex-col gap-0.5 font-medium text-slate-700">
                To
                <input
                    class="erp-input erp-date-input w-[7.5rem]"
                    type="text"
                    name="history_to"
                    value="{{ request('history_to', '') }}"
                    placeholder="DD-MM-YYYY"
                    autocomplete="off"
                    inputmode="numeric"
                    maxlength="10"
                >
            </label>
            <button
                type="submit"
                class="rounded border border-slate-600 bg-slate-200 px-3 py-1 text-[11px] font-semibold hover:bg-white"
            >Load</button>
            @if (request()->filled('history_from') || request()->filled('history_to'))
                <a
                    class="rounded border border-slate-500 bg-white px-3 py-1 text-[11px] hover:bg-sky-50"
                    href="{{ request()->url() }}"
                >Clear</a>
            @endif
        </form>
    </div>

    @if ($isEmpty)
        <p class="border border-slate-300 bg-[#f9f9f9] px-2 py-3 text-[12px] text-slate-600">
            {{ $historyEmpty ?? 'No saved records yet. Use Save above; posted documents will appear here grouped by date.' }}
        </p>
    @else
        <div class="{{ $historyScrollMin }} flex-1 overflow-y-auto overflow-x-auto space-y-3">
            @foreach ($recordsHistoryGrouped as $dateLabel => $groupRecords)
                <section>
                    <div class="sticky top-0 z-[1] border border-slate-500 bg-[#c8c8c8] px-2 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-800">
                        {{ $dateLabel }}
                    </div>
                    <div class="overflow-x-auto border border-t-0 border-slate-400">
                        <table class="w-full border-collapse text-left text-[12px] @if ($historyType === 'transaction') min-w-[820px] @elseif ($historyType === 'opening') min-w-[720px] @elseif ($historyType === 'contract') min-w-[900px] @else min-w-[640px] @endif">
                            <thead>
                                    <tr class="bg-[#d8d8d8]">
                                        @if ($historyType === 'voucher')
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Number</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Date</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Status</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Debit</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Credit</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Difference</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold"></th>
                                        @elseif ($historyType === 'transaction')
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">No</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Date</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Account</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Contract</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Status</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Qty</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Amount</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold"></th>
                                        @elseif ($historyType === 'transaction_simple')
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">No</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Date</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Status</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Amount</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold"></th>
                                        @elseif ($historyType === 'opening')
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">V.ID</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">V date</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Fin year</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Account code</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Narration</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Debit</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Credit</th>
                                        @elseif ($historyType === 'contract')
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Contract</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Date</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Type</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Account</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Yarn</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Weight</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Rate</th>
                                            <th class="border border-slate-400 px-1 py-1 font-semibold">Status</th>
                                        @endif
                                    </tr>
                            </thead>
                            <tbody>
                                @foreach ($groupRecords as $record)
                                    @if ($historyType === 'voucher')
                                        @php
                                            $dr = (float) $record->total_debit;
                                            $cr = (float) $record->total_credit;
                                            $diff = $dr - $cr;
                                            $printPermission = ($permissionPrefix ?? 'accounts.vouchers.jv') . '.print';
                                        @endphp
                                        <tr>
                                            <td class="border border-slate-300 px-1 py-1 font-mono">{{ $record->voucher_number }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ ErpDate::display($record->voucher_date) }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ strtoupper($record->status) }}</td>
                                            <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($dr, 2) }}</td>
                                            <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ number_format($cr, 2) }}</td>
                                            <td class="border border-slate-300 px-1 py-1 font-mono @if (abs($diff) > 0.009) text-amber-800 @endif">{{ number_format($diff, 2) }}</td>
                                            <td class="border border-slate-300 px-1 py-1">
                                                @if (auth()->user()?->hasPermission($printPermission))
                                                    <a
                                                        class="rounded border border-slate-500 bg-white px-2 py-0.5 text-[11px] hover:bg-sky-50"
                                                        href="{{ route('erp.accounts.vouchers.print', $record) }}"
                                                        target="_blank"
                                                        rel="noopener"
                                                    >Print</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @elseif ($historyType === 'transaction')
                                        <tr>
                                            <td class="border border-slate-300 px-1 py-1 font-mono">{{ $record->trans_no }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ ErpDate::display($record->trans_date) }}</td>
                                            <td class="border border-slate-300 px-1 py-1">
                                                {{ $record->account?->name
                                                    ?? $record->yarnContract?->account?->name
                                                    ?? $record->fromYarnContract?->account?->name
                                                    ?? '-' }}
                                            </td>
                                            <td class="border border-slate-300 px-1 py-1">
                                                {{ $record->yarnContract?->contract_no
                                                    ?? trim(($record->fromYarnContract?->contract_no ?? '') . ' -> ' . ($record->toYarnContract?->contract_no ?? ''), ' ->')
                                                    ?: '-' }}
                                            </td>
                                            <td class="border border-slate-300 px-1 py-1">{{ strtoupper($record->status) }}</td>
                                            <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $record->total_qty, 2) }}</td>
                                            <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $record->total_amount, 2) }}</td>
                                            <td class="border border-slate-300 px-1 py-1 text-right">
                                                @if (($record->status ?? '') !== 'posted')
                                                    <form method="post" action="{{ route('erp.' . $moduleKey . '.screen.post', ['screen' => $screen['slug'], 'transaction' => $record->id]) }}" class="inline">
                                                        @csrf
                                                        <button class="rounded border border-slate-500 bg-slate-100 px-2 py-0.5 text-[11px]">Save</button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('erp.' . $moduleKey . '.screen.print', ['screen' => $screen['slug'], 'transaction' => $record->id]) }}" target="_blank" class="ml-1 rounded border border-slate-500 bg-slate-100 px-2 py-0.5 text-[11px]">Print</a>
                                            </td>
                                        </tr>
                                    @elseif ($historyType === 'transaction_simple')
                                        <tr>
                                            <td class="border border-slate-300 px-1 py-1 font-mono">{{ $record->trans_no }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ ErpDate::display($record->trans_date) }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ strtoupper($record->status) }}</td>
                                            <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $record->total_amount, 2) }}</td>
                                            <td class="border border-slate-300 px-1 py-1 text-right">
                                                @if (($record->status ?? '') !== 'posted')
                                                    <form method="post" action="{{ route('erp.' . $moduleKey . '.screen.post', ['screen' => $screen['slug'], 'transaction' => $record->id]) }}" class="inline">
                                                        @csrf
                                                        <button class="rounded border border-slate-500 bg-slate-100 px-2 py-0.5 text-[11px]">Save</button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('erp.' . $moduleKey . '.screen.print', ['screen' => $screen['slug'], 'transaction' => $record->id]) }}" target="_blank" class="ml-1 rounded border border-slate-500 bg-slate-100 px-2 py-0.5 text-[11px]">Print</a>
                                            </td>
                                        </tr>
                                    @elseif ($historyType === 'opening')
                                        <tr>
                                            <td class="border border-slate-300 px-1 py-1">{{ $record->id }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ ErpDate::display($record->voucher_date) }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ $record->financialYear?->year_code }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ $record->account?->code }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ $record->narration }}</td>
                                            <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $record->debit, 2) }}</td>
                                            <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $record->credit, 2) }}</td>
                                        </tr>
                                    @elseif ($historyType === 'contract')
                                        <tr>
                                            <td class="border border-slate-300 px-1 py-1 font-mono">{{ $record->contract_no }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ ErpDate::display($record->contract_date) }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ $record->contract_type }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ $record->account?->code }} — {{ $record->account?->name }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ $record->item?->code }} — {{ $record->item?->name }}</td>
                                            <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $record->weight_lbs, 2) }}</td>
                                            <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $record->rate, 2) }}</td>
                                            <td class="border border-slate-300 px-1 py-1">{{ strtoupper($record->status) }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach
        </div>

        @include('erp.partials.records-history-pagination')
    @endif

    @if ($historyFooter)
        <p class="mt-2 text-[11px] text-slate-600">{{ $historyFooter }}</p>
    @endif
</div>
