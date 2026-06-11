@php
    $lines = collect($editingTransaction?->lines ?? []);
    $rowCount = max(6, $lines->count() + 2);
    $blankRow = ['description' => '', 'qty' => '', 'rate' => '', 'amount' => '', 'meta' => []];
    $rows = $lines->map(fn ($l) => [
        'description' => $l->description,
        'qty' => $l->qty,
        'rate' => $l->rate,
        'amount' => $l->amount,
        'meta' => $l->meta ?? [],
        'grey_quality_id' => $l->meta['grey_quality_id'] ?? $editingTransaction?->grey_quality_id,
        'grey_conversion_contract_id' => $l->meta['grey_conversion_contract_id'] ?? null,
    ])->all();
    while (count($rows) < $rowCount) {
        $rows[] = $blankRow;
    }
    $isMending = $screen['slug'] === 'mending-form';
    $parchiType = match ($screen['slug']) {
        'fabric-issue-conversion-kachi', 'fabric-issue-sale-kachi' => 'kachi',
        'fabric-issue-conversion-pachi', 'rejection-receipt-packi-parchi' => 'packi',
        default => 'grey',
    };
@endphp
<div class="grid gap-2 md:grid-cols-4 lg:grid-cols-6">
    <label class="erp-field md:col-span-2"><span class="erp-label">Party Code</span>
        @include('erp.grey.partials.code-name-pair', [
            'selectName' => 'account_id',
            'selectedId' => old('account_id', $editingTransaction?->account_id),
            'options' => $accountParties,
            'required' => (bool) data_get(config('weaving_vouchers.screens.' . $screen['slug']), 'requires_party'),
            'targetId' => 'weaving-fabric-party-' . $screen['slug'],
        ])
    </label>
    <label class="erp-field md:col-span-2"><span class="erp-label">Contract</span>
        <select class="erp-input" name="grey_conversion_contract_id">
            <option value="">—</option>
            @foreach ($conversionContracts as $c)
                <option value="{{ $c->id }}" @selected(old('grey_conversion_contract_id', $editingTransaction?->grey_conversion_contract_id) == $c->id)>{{ $c->contract_no }}</option>
            @endforeach
        </select>
    </label>
    <input type="hidden" name="meta[parchi_type]" value="{{ $meta['parchi_type'] ?? $parchiType }}">
    @if ($isMending)
        <label class="erp-field"><span class="erp-label">RR Ref</span><input class="erp-input" name="meta[rr_ref]" value="{{ $meta['rr_ref'] ?? '' }}"></label>
    @endif
</div>
<div class="overflow-x-auto border border-slate-400">
    <table class="w-full min-w-[960px] border-collapse text-[11px]" data-erp-detail-lines data-weaving-fabric-grid>
        <thead class="bg-[#d8d8d8]">
            <tr>
                <th class="border border-slate-400 px-1 py-1">Quality</th>
                <th class="border border-slate-400 px-1 py-1">Than</th>
                <th class="border border-slate-400 px-1 py-1">Mtr</th>
                <th class="border border-slate-400 px-1 py-1">Rate</th>
                <th class="border border-slate-400 px-1 py-1">Amount</th>
                @if ($isMending)
                    <th class="border border-slate-400 px-1 py-1">Fresh</th>
                    <th class="border border-slate-400 px-1 py-1">1-UP</th>
                    <th class="border border-slate-400 px-1 py-1">10-UP</th>
                    <th class="border border-slate-400 px-1 py-1">37-UP</th>
                    <th class="border border-slate-400 px-1 py-1">EL-Kami</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $i => $row)
                <tr data-erp-detail-line>
                    <td class="border border-slate-300 p-0.5">
                        <select class="erp-input w-full" name="lines[{{ $i }}][meta][grey_quality_id]">
                            <option value="">—</option>
                            @foreach ($greyQualities as $q)
                                <option value="{{ $q->id }}" @selected(($row['grey_quality_id'] ?? '') == $q->id)>{{ $q->quality_no }} — {{ $q->quality_name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][meta][than]" value="{{ $row['meta']['than'] ?? '' }}"></td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][qty]" value="{{ $row['qty'] }}" data-weaving-qty></td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][rate]" value="{{ $row['rate'] }}" data-weaving-rate></td>
                    <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right bg-[#f8f8f8]" name="lines[{{ $i }}][amount]" value="{{ $row['amount'] }}" data-weaving-amount readonly></td>
                    @if ($isMending)
                        @foreach (['fresh', 'one_up', 'ten_up', 'thirty_seven_up', 'el_kami'] as $grade)
                            <td class="border border-slate-300 p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][meta][{{ $grade }}]" value="{{ $row['meta'][$grade] ?? '' }}"></td>
                        @endforeach
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-[#ececec]">
            <tr>
                <td colspan="2" class="border border-slate-400 px-2 py-1 text-right font-semibold">Totals</td>
                <td class="border border-slate-400 px-1 text-right font-semibold" data-weaving-total-qty>0</td>
                <td class="border border-slate-400"></td>
                <td class="border border-slate-400 px-1 text-right font-semibold" data-weaving-total-amount>0</td>
                @if ($isMending)<td colspan="5" class="border border-slate-400"></td>@endif
            </tr>
        </tfoot>
    </table>
</div>
