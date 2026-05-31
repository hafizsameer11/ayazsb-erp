@php
    $reportRows = $reportRows ?? [];
    while (count($reportRows) < 3) {
        $reportRows[] = [];
    }
@endphp
<fieldset class="border border-slate-400 bg-[#f7f7f7] p-2">
    <legend class="px-1 text-[11px] font-semibold">Grey Sale Report</legend>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1200px] border-collapse text-[11px]">
            <thead>
                <tr class="bg-[#d8d8d8]">
                    <th class="border px-1 py-1">Report Date</th>
                    <th class="border px-1 py-1">Godown</th>
                    <th class="border px-1 py-1">Rejection Qty</th>
                    <th class="border px-1 py-1">Return Qty</th>
                    <th class="border px-1 py-1">Kami</th>
                    <th class="border px-1 py-1">In Kami Stock</th>
                    <th class="border px-1 py-1">Qty</th>
                    <th class="border px-1 py-1">Rate</th>
                    <th class="border px-1 py-1">Total Gross Amnt</th>
                    <th class="border px-1 py-1">Comm</th>
                    <th class="border px-1 py-1">Brokery</th>
                    <th class="border px-1 py-1">Checkary</th>
                    <th class="border px-1 py-1">Munshiana</th>
                    <th class="border px-1 py-1">Total Net Amnt</th>
                    <th class="border px-1 py-1">Report Ind</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reportRows as $i => $row)
                    <tr>
                        <td class="border p-0.5"><x-erp-date-input class="erp-input w-full" :name="'meta[sale_reports]['.$i.'][report_date]'" :value="old('meta.sale_reports.'.$i.'.report_date', $row['report_date'] ?? '')" :default-blank="true" /></td>
                        <td class="border p-0.5"><input class="erp-input w-full" name="meta[sale_reports][{{ $i }}][godown_id]" value="{{ old('meta.sale_reports.'.$i.'.godown_id', $row['godown_id'] ?? '') }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[sale_reports][{{ $i }}][rejection_qty]" value="{{ old('meta.sale_reports.'.$i.'.rejection_qty', $row['rejection_qty'] ?? '') }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[sale_reports][{{ $i }}][return_qty]" value="{{ old('meta.sale_reports.'.$i.'.return_qty', $row['return_qty'] ?? '') }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[sale_reports][{{ $i }}][kami]" value="{{ old('meta.sale_reports.'.$i.'.kami', $row['kami'] ?? '') }}"></td>
                        <td class="border p-0.5 text-center"><input type="checkbox" name="meta[sale_reports][{{ $i }}][in_kami_stock]" value="1" @checked(old('meta.sale_reports.'.$i.'.in_kami_stock', $row['in_kami_stock'] ?? false))></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[sale_reports][{{ $i }}][qty]" value="{{ old('meta.sale_reports.'.$i.'.qty', $row['qty'] ?? '') }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[sale_reports][{{ $i }}][rate]" value="{{ old('meta.sale_reports.'.$i.'.rate', $row['rate'] ?? '') }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[sale_reports][{{ $i }}][total_gross]" value="{{ old('meta.sale_reports.'.$i.'.total_gross', $row['total_gross'] ?? '') }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[sale_reports][{{ $i }}][commission]" value="{{ old('meta.sale_reports.'.$i.'.commission', $row['commission'] ?? '') }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[sale_reports][{{ $i }}][brokery]" value="{{ old('meta.sale_reports.'.$i.'.brokery', $row['brokery'] ?? '') }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[sale_reports][{{ $i }}][checkary]" value="{{ old('meta.sale_reports.'.$i.'.checkary', $row['checkary'] ?? '') }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[sale_reports][{{ $i }}][munshiana]" value="{{ old('meta.sale_reports.'.$i.'.munshiana', $row['munshiana'] ?? '') }}"></td>
                        <td class="border p-0.5"><input class="erp-input w-full text-right" name="meta[sale_reports][{{ $i }}][total_net]" value="{{ old('meta.sale_reports.'.$i.'.total_net', $row['total_net'] ?? '') }}"></td>
                        <td class="border p-0.5 text-center"><input type="checkbox" name="meta[sale_reports][{{ $i }}][report_ind]" value="1" @checked(old('meta.sale_reports.'.$i.'.report_ind', $row['report_ind'] ?? false))></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @include('erp.grey.partials.voucher-strip', ['meta' => $meta['report_voucher'] ?? [], 'prefix' => 'meta[report_voucher]'])
    <div class="mt-2 flex justify-end gap-2">
        <button type="button" class="rounded border border-slate-500 bg-slate-100 px-2 py-1 text-[11px]">Voucher Post</button>
        <button type="button" class="rounded border border-slate-500 bg-slate-100 px-2 py-1 text-[11px]">Voucher Print</button>
    </div>
</fieldset>
