<tr>
    @include('erp.accounts.vouchers.partials.line-account-select', ['i' => $i, 'voucherCode' => $voucherCode])
    <td class="border border-slate-300 p-0"><input class="erp-input w-full" type="text" name="lines[{{ $i }}][description]"></td>
    <td class="border border-slate-300 p-0"><input class="erp-input w-full text-right font-mono" type="text" name="lines[{{ $i }}][debit]" placeholder="0.00"></td>
    <td class="border border-slate-300 p-0"><input class="erp-input w-full text-right font-mono" type="text" name="lines[{{ $i }}][credit]" placeholder="0.00"></td>
</tr>
