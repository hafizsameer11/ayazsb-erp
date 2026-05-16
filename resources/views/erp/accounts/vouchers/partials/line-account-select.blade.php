<td class="border border-slate-300 p-0 align-middle">
    {{-- Only `js-account-search` — Tom Select copies classes onto .ts-wrapper; keep utilities minimal. --}}
    <select class="js-account-search" name="lines[{{ $i }}][account_id]" autocomplete="off">
        @include('erp.accounts.partials.account-select-options', ['selectedAccountId' => old("lines.$i.account_id", $line->account_id ?? null)])
    </select>
</td>
