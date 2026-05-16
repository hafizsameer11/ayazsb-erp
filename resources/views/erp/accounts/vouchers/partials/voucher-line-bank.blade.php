@php
    $line = $line ?? null;
@endphp
<tr>
    @include('erp.accounts.vouchers.partials.line-account-select', ['i' => $i, 'line' => $line, 'voucherCode' => $voucherCode])
    <td class="border border-slate-300 p-0"><select class="erp-input w-full" name="lines[{{ $i }}][tag]"><option value=""></option></select></td>
    <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" name="lines[{{ $i }}][meta][instrument_no]"></td>
    <td class="border border-slate-300 p-0"><input class="erp-input erp-date-input w-full" type="text" name="lines[{{ $i }}][meta][instrument_date]" placeholder="DD-MM-YYYY" autocomplete="off"></td>
    <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" name="lines[{{ $i }}][meta][title]"></td>
    <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" name="lines[{{ $i }}][description]" value="{{ old("lines.$i.description", $line->description ?? '') }}"></td>
    <td class="border border-slate-300 p-0"><input class="erp-input w-full text-right font-mono" type="text" name="lines[{{ $i }}][debit]" value="{{ old("lines.$i.debit", $line && (float) $line->debit > 0 ? number_format((float) $line->debit, 2, '.', '') : '') }}" placeholder="0.00" inputmode="decimal"></td>
    <td class="border border-slate-300 p-0"><input class="erp-input w-full text-right font-mono" type="text" name="lines[{{ $i }}][credit]" value="{{ old("lines.$i.credit", $line && (float) $line->credit > 0 ? number_format((float) $line->credit, 2, '.', '') : '') }}" placeholder="0.00" inputmode="decimal"></td>
</tr>
