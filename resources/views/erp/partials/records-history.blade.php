@php
    use App\Support\ErpDate;
    use App\Support\RecordHistory;

    $historyType = $historyType ?? 'voucher';
    $historyTitle = $historyTitle ?? 'Saved records';
    $recordsForDay = $recordsForDay ?? collect();
    $historyDate = $historyDate ?? RecordHistory::selectedDateDisplay(request());
    $historyNav = $historyNav ?? ['prev' => '#', 'next' => '#', 'today' => request()->url()];
    $historyScrollMin = $historyScrollMin ?? 'min-h-[240px]';
    $historyFooter = $historyFooter ?? null;
    $isEmpty = $recordsForDay->isEmpty();
@endphp

<div class="erp-records-history flex min-h-0 flex-1 flex-col border-t border-slate-300 p-2">
    <div class="mb-2 flex flex-wrap items-end justify-between gap-2">
        <div>
            <div class="text-[11px] font-semibold uppercase text-slate-600">{{ $historyTitle }}</div>
            <div class="text-[12px] font-semibold text-slate-800">Date: {{ $historyDate }}</div>
        </div>
        <form method="get" action="{{ request()->url() }}" class="flex flex-wrap items-end gap-2 text-[11px]">
            @foreach (request()->except(['history_date', 'page']) as $key => $value)
                @if (is_scalar($value))
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
            <label class="flex flex-col gap-0.5 font-medium text-slate-700">
                View date
                <input
                    class="erp-input erp-date-input w-[7.5rem]"
                    type="text"
                    name="history_date"
                    value="{{ request('history_date', $historyDate) }}"
                    placeholder="DD-MM-YYYY"
                    autocomplete="off"
                    inputmode="numeric"
                    maxlength="10"
                >
            </label>
            <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-3 py-1 text-[11px] font-semibold hover:bg-white">Load</button>
        </form>
        <div class="flex flex-wrap items-center gap-1 text-[11px]">
            <a href="{{ $historyNav['prev'] }}" class="rounded border border-slate-500 bg-white px-2 py-1 hover:bg-sky-50">◀ Previous day</a>
            <a href="{{ $historyNav['today'] }}" class="rounded border border-slate-500 bg-slate-100 px-2 py-1 font-semibold hover:bg-white">Today</a>
            <a href="{{ $historyNav['next'] }}" class="rounded border border-slate-500 bg-white px-2 py-1 hover:bg-sky-50">Next day ▶</a>
        </div>
    </div>

    @if ($isEmpty)
        <p class="border border-slate-300 bg-[#f9f9f9] px-2 py-3 text-[12px] text-slate-600">
            No records for {{ $historyDate }}. Use Save above to add entries for this date, or choose another day.
        </p>
    @else
        <div class="{{ $historyScrollMin }} flex-1 overflow-y-auto overflow-x-auto border border-slate-400">
            <table class="w-full border-collapse text-left text-[12px] @if ($historyType === 'voucher') min-w-[820px] @elseif ($historyType === 'transaction') min-w-[820px] @elseif ($historyType === 'opening') min-w-[720px] @elseif ($historyType === 'contract') min-w-[900px] @else min-w-[640px] @endif">
                <thead>
                    <tr class="bg-[#d8d8d8]">
                        @if ($historyType === 'voucher')
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Voucher</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Account</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Description</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Debit</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Credit</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold"></th>
                        @elseif ($historyType === 'transaction')
                            <th class="border border-slate-400 px-1 py-1 font-semibold">No</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Account</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Contract</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Status</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Qty</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Amount</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold"></th>
                        @elseif ($historyType === 'transaction_simple')
                            <th class="border border-slate-400 px-1 py-1 font-semibold">No</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Status</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Amount</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold"></th>
                        @elseif ($historyType === 'opening')
                            <th class="border border-slate-400 px-1 py-1 font-semibold">V.ID</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Fin year</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Account</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Narration</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Debit</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Credit</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold"></th>
                        @elseif ($historyType === 'contract')
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Contract</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Type</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Account</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Yarn</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Weight</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold text-right">Rate</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold">Status</th>
                            <th class="border border-slate-400 px-1 py-1 font-semibold"></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if ($historyType === 'voucher')
                        @foreach ($recordsForDay as $voucher)
                            @php
                                $lines = $voucher->lines ?? collect();
                                $printPermission = ($permissionPrefix ?? 'accounts.vouchers.jv') . '.print';
                                $editPermission = ($permissionPrefix ?? 'accounts.vouchers.jv') . '.edit';
                                $editUrl = RecordHistory::editUrl(request(), 'erp.accounts.vouchers.' . ($voucherSlug ?? 'jv'), [], $voucher->id);
                            @endphp
                            @forelse ($lines as $lineIndex => $line)
                                <tr class="@if($lineIndex > 0) bg-[#fafafa] @endif">
                                    <td class="border border-slate-300 px-1 py-1 font-mono">
                                        @if ($lineIndex === 0)
                                            {{ $voucher->voucher_number }}
                                            <span class="block text-[10px] font-normal text-slate-500">{{ strtoupper($voucher->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="border border-slate-300 px-1 py-1">{{ $line->account?->code }} — {{ $line->account?->name }}</td>
                                    <td class="border border-slate-300 px-1 py-1">{{ $line->description }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ (float) $line->debit > 0 ? number_format((float) $line->debit, 2) : '' }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ (float) $line->credit > 0 ? number_format((float) $line->credit, 2) : '' }}</td>
                                    <td class="border border-slate-300 px-1 py-1 whitespace-nowrap">
                                        @if ($lineIndex === 0)
                                            @include('erp.partials.records-history-edit-link', ['editUrl' => $editUrl, 'editPermission' => $editPermission])
                                            @include('erp.partials.records-history-delete-btn', [
                                                'deleteUrl' => route('erp.accounts.vouchers.destroy', $voucher),
                                                'deleteConfirm' => 'Delete voucher ' . $voucher->voucher_number . '?',
                                            ])
                                            @if (auth()->user()?->hasPermission($printPermission))
                                                <a class="ml-1 rounded border border-slate-500 bg-white px-2 py-0.5 text-[11px] hover:bg-sky-50" href="{{ route('erp.accounts.vouchers.print', $voucher) }}" target="_blank" rel="noopener">Print</a>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="border border-slate-300 px-1 py-1 text-slate-500">No lines</td>
                                </tr>
                            @endforelse
                        @endforeach
                    @else
                        @foreach ($recordsForDay as $record)
                            @if ($historyType === 'transaction')
                                <tr>
                                    <td class="border border-slate-300 px-1 py-1 font-mono">{{ $record->trans_no }}</td>
                                    <td class="border border-slate-300 px-1 py-1">{{ $record->account?->name ?? $record->yarnContract?->account?->name ?? '-' }}</td>
                                    <td class="border border-slate-300 px-1 py-1">{{ $record->yarnContract?->contract_no ?? '-' }}</td>
                                    <td class="border border-slate-300 px-1 py-1">{{ strtoupper($record->status) }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $record->total_qty, 2) }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $record->total_amount, 2) }}</td>
                                    <td class="border border-slate-300 px-1 py-1 whitespace-nowrap">
                                        @include('erp.partials.records-history-edit-link', [
                                            'editUrl' => RecordHistory::editUrl(request(), 'erp.' . ($moduleKey ?? 'yarn') . '.screen', ['screen' => $screen['slug'] ?? ''], $record->id),
                                            'editPermission' => ($permissionPrefix ?? 'yarn.screen') . '.edit',
                                        ])
                                        @include('erp.partials.records-history-delete-btn', [
                                            'deleteUrl' => route('erp.' . ($moduleKey ?? 'yarn') . '.screen.destroy', ['screen' => $screen['slug'] ?? '', 'transaction' => $record->id]),
                                            'deleteConfirm' => 'Delete transaction ' . $record->trans_no . '?',
                                        ])
                                        <a href="{{ route('erp.' . $moduleKey . '.screen.print', ['screen' => $screen['slug'], 'transaction' => $record->id]) }}" target="_blank" class="ml-1 rounded border border-slate-500 bg-slate-100 px-2 py-0.5 text-[11px]">Print</a>
                                    </td>
                                </tr>
                            @elseif ($historyType === 'transaction_simple')
                                <tr>
                                    <td class="border border-slate-300 px-1 py-1 font-mono">{{ $record->trans_no }}</td>
                                    <td class="border border-slate-300 px-1 py-1">{{ strtoupper($record->status) }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $record->total_amount, 2) }}</td>
                                    <td class="border border-slate-300 px-1 py-1 whitespace-nowrap">
                                        @include('erp.partials.records-history-edit-link', [
                                            'editUrl' => RecordHistory::editUrl(request(), 'erp.' . ($moduleKey ?? 'yarn') . '.screen', ['screen' => $screen['slug'] ?? ''], $record->id),
                                            'editPermission' => ($permissionPrefix ?? 'yarn.screen') . '.edit',
                                        ])
                                        @include('erp.partials.records-history-delete-btn', [
                                            'deleteUrl' => route('erp.' . ($moduleKey ?? 'yarn') . '.screen.destroy', ['screen' => $screen['slug'] ?? '', 'transaction' => $record->id]),
                                            'deleteConfirm' => 'Delete transaction ' . $record->trans_no . '?',
                                        ])
                                        <a href="{{ route('erp.' . $moduleKey . '.screen.print', ['screen' => $screen['slug'], 'transaction' => $record->id]) }}" target="_blank" class="ml-1 rounded border border-slate-500 bg-slate-100 px-2 py-0.5 text-[11px]">Print</a>
                                    </td>
                                </tr>
                            @elseif ($historyType === 'opening')
                                <tr>
                                    <td class="border border-slate-300 px-1 py-1">{{ $record->id }}</td>
                                    <td class="border border-slate-300 px-1 py-1">{{ $record->financialYear?->year_code }}</td>
                                    <td class="border border-slate-300 px-1 py-1">{{ $record->account?->code }} — {{ $record->account?->name }}</td>
                                    <td class="border border-slate-300 px-1 py-1">{{ $record->narration }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ (float) $record->debit > 0 ? number_format((float) $record->debit, 2) : '' }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-right font-mono">{{ (float) $record->credit > 0 ? number_format((float) $record->credit, 2) : '' }}</td>
                                    <td class="border border-slate-300 px-1 py-1 whitespace-nowrap">
                                        @include('erp.partials.records-history-edit-link', [
                                            'editUrl' => RecordHistory::editUrl(request(), 'erp.accounts.opening', [], $record->id),
                                            'editPermission' => 'accounts.opening.edit',
                                        ])
                                        @include('erp.partials.records-history-delete-btn', [
                                            'deleteUrl' => route('erp.accounts.opening.destroy', $record),
                                            'deleteConfirm' => 'Delete this opening entry?',
                                        ])
                                    </td>
                                </tr>
                            @elseif ($historyType === 'contract')
                                <tr>
                                    <td class="border border-slate-300 px-1 py-1 font-mono">{{ $record->contract_no }}</td>
                                    <td class="border border-slate-300 px-1 py-1">{{ $record->contract_type }}</td>
                                    <td class="border border-slate-300 px-1 py-1">{{ $record->account?->code }} — {{ $record->account?->name }}</td>
                                    <td class="border border-slate-300 px-1 py-1">{{ $record->item?->code }} — {{ $record->item?->name }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $record->weight_lbs, 2) }}</td>
                                    <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $record->rate, 2) }}</td>
                                    <td class="border border-slate-300 px-1 py-1">{{ strtoupper($record->status) }}</td>
                                    <td class="border border-slate-300 px-1 py-1 whitespace-nowrap">
                                        @include('erp.partials.records-history-edit-link', [
                                            'editUrl' => RecordHistory::editUrl(request(), 'erp.' . ($moduleKey ?? 'yarn') . '.screen', ['screen' => $screen['slug'] ?? 'purchase-contract'], $record->id),
                                            'editPermission' => ($permissionPrefix ?? 'yarn.purchase-contract') . '.edit',
                                        ])
                                        @include('erp.partials.records-history-delete-btn', [
                                            'deleteUrl' => route('erp.yarn.screen.contract.destroy', ['screen' => $screen['slug'] ?? 'purchase-contract', 'contract' => $record->id]),
                                            'deleteConfirm' => 'Delete contract ' . $record->contract_no . '?',
                                        ])
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        <p class="mt-1 text-[11px] text-slate-600">{{ $recordsForDay->count() }} record(s) on {{ $historyDate }}.</p>
    @endif

    @if ($historyFooter)
        <p class="mt-2 text-[11px] text-slate-600">{{ $historyFooter }}</p>
    @endif
</div>
