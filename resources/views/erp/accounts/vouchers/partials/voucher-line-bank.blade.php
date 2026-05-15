<tr>
    @include('erp.accounts.vouchers.partials.line-account-select', ['i' => $i, 'voucherCode' => $voucherCode])
    <td class="border border-slate-300 p-0"><select class="erp-input w-full" name="lines[{{ $i }}][tag]"><option value=""></option></select></td>
    <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" name="lines[{{ $i }}][meta][instrument_no]"></td>
    <td class="border border-slate-300 p-0"><input class="erp-input erp-date-input w-full" type="text" name="lines[{{ $i }}][meta][instrument_date]" placeholder="DD-MM-YYYY" autocomplete="off"></td>
    <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" name="lines[{{ $i }}][meta][title]"></td>
    <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" name="lines[{{ $i }}][description]"></td>
    <td class="border border-slate-300 p-0"><input class="erp-input w-full text-right font-mono" type="text" name="lines[{{ $i }}][debit]" placeholder="0.00" inputmode="decimal"></td>
    <td class="border border-slate-300 p-0"><input class="erp-input w-full text-right font-mono" type="text" name="lines[{{ $i }}][credit]" placeholder="0.00" inputmode="decimal"></td>
</tr>
