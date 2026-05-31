@php
    $editingTransaction = $editingTransaction ?? null;
    $lines = $editingTransaction?->lines ?? collect();
    $rows = [];
    foreach ($lines as $line) {
        $rows[] = [
            'id' => $line->id,
            'open_line_id' => $line->meta['open_line_id'] ?? '',
            'grey_quality_id' => $line->meta['grey_quality_id'] ?? '',
            'system_lot_no' => $line->meta['system_lot_no'] ?? '',
            'loom_type' => $line->meta['loom_type'] ?? 'AUTO',
            'grey_tag' => $line->meta['grey_tag'] ?? 'FRESH',
            'qty' => $line->qty,
            'rate' => $line->rate,
            'amount' => $line->amount,
            'remarks' => $line->meta['line_remarks'] ?? '',
        ];
    }
    while (count($rows) < 15) {
        $rows[] = ['id' => '', 'open_line_id' => '', 'grey_quality_id' => '', 'system_lot_no' => '', 'loom_type' => 'AUTO', 'grey_tag' => 'FRESH', 'qty' => '', 'rate' => '', 'amount' => '', 'remarks' => ''];
    }
    $greyTags = ['FRESH', 'SECOND', 'REJECTION'];
    $loomTypes = ['AUTO', 'SHUTTLE LESS', 'SHUTTLE LESS DOBBY', 'POWER'];
@endphp
<fieldset class="border border-slate-400 bg-[#f4f4f4] p-2">
    <legend class="px-1 text-[11px] font-semibold">Grey Opening</legend>
    <div class="grid gap-2 md:grid-cols-2 lg:grid-cols-4">
        <label class="erp-field"><span class="erp-label">Open #</span><input class="erp-input bg-[#f0f0f0]" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
        <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
        <label class="erp-field lg:col-span-2"><span class="erp-label">Godown</span>@include('erp.grey.partials.godown-pair', ['selectedId' => old('from_godown_id', $editingTransaction?->from_godown_id), 'targetId' => 'grey-opening-godown'])</label>
        <label class="erp-field lg:col-span-4"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
    </div>
</fieldset>

<fieldset class="border border-slate-400 p-2">
    <div class="max-h-[28rem] overflow-auto">
        <table class="w-full min-w-[1050px] border-collapse text-[11px]" data-grey-opening-grid>
            <thead>
                <tr class="bg-[#d8d8d8]">
                    <th class="border px-1 py-1">Open Id</th>
                    <th class="border px-1 py-1">System Lot #</th>
                    <th class="border px-1 py-1">Quality Code</th>
                    <th class="border px-1 py-1">Quality Name</th>
                    <th class="border px-1 py-1">Loom Type</th>
                    <th class="border px-1 py-1">Grey Tag</th>
                    <th class="border px-1 py-1">Qty (Mtr)</th>
                    <th class="border px-1 py-1">Rate /Mtr</th>
                    <th class="border px-1 py-1">Amount</th>
                    <th class="border px-1 py-1">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $i => $row)
                    @php
                        $quality = ($greyQualities ?? collect())->first(fn ($q) => (string) $q->id === (string) ($row['grey_quality_id'] ?? ''));
                    @endphp
                    <tr data-grey-opening-row>
                        <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][meta][open_line_id]" value="{{ $row['open_line_id'] }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full font-mono" name="lines[{{ $i }}][meta][system_lot_no]" value="{{ $row['system_lot_no'] }}"></td>
                        <td class="border p-0.5">
                            <select class="erp-input w-full font-mono" name="lines[{{ $i }}][meta][grey_quality_id]" data-grey-quality-lookup data-grey-line-quality-name="#opening-quality-name-{{ $i }}">
                                <option value=""></option>
                                @foreach ($greyQualities as $q)
                                    <option value="{{ $q->id }}" data-code="{{ $q->quality_no }}" data-name="{{ $q->quality_name }}" @selected((string) $row['grey_quality_id'] === (string) $q->id)>{{ $q->quality_no }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="border p-0.5"><input class="erp-input w-full bg-[#f8f8f8]" id="opening-quality-name-{{ $i }}" readonly value="{{ $quality?->quality_name ?? '' }}"></td>
                        <td class="border p-0.5">
                            <select class="erp-input w-full" name="lines[{{ $i }}][meta][loom_type]">
                                @foreach ($loomTypes as $lt)
                                    <option value="{{ $lt }}" @selected(($row['loom_type'] ?? '') === $lt)>{{ $lt }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="border p-0.5">
                            <select class="erp-input w-full" name="lines[{{ $i }}][meta][grey_tag]">
                                @foreach ($greyTags as $tag)
                                    <option value="{{ $tag }}" @selected(($row['grey_tag'] ?? '') === $tag)>{{ $tag }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][qty]" type="number" step="0.01" data-grey-line-qty value="{{ $row['qty'] }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="lines[{{ $i }}][rate]" type="number" step="0.0001" data-grey-line-rate value="{{ $row['rate'] }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right bg-[#f8f8f8]" name="lines[{{ $i }}][amount]" type="number" step="0.01" data-grey-line-amount value="{{ $row['amount'] }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full" name="lines[{{ $i }}][meta][line_remarks]" value="{{ $row['remarks'] }}"></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-[#ececec] font-semibold">
                    <td class="border px-1 py-1"><input class="erp-input w-full bg-white text-right" data-grey-opening-count readonly placeholder="Count"></td>
                    <td class="border px-1 py-1" colspan="5"></td>
                    <td class="border px-1 py-1"><input class="erp-input w-full bg-white text-right" data-grey-opening-total-qty readonly placeholder="Total Qty"></td>
                    <td class="border px-1 py-1"></td>
                    <td class="border px-1 py-1"><input class="erp-input w-full bg-white text-right" data-grey-opening-total-amount readonly placeholder="Total Amount"></td>
                    <td class="border px-1 py-1"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</fieldset>
