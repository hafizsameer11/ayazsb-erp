@php
    $screenSlug = $screen['slug'] ?? 'accounts';
    $isAccounts = $screenSlug === 'accounts';
    $selectedReport = request('report', $isAccounts ? 'account-statement' : 'summary');
    $postableAccounts = $postableAccounts ?? collect();
    $inventoryScreenOptions = $inventoryScreenOptions ?? [];
@endphp
<section class="space-y-2">
    @if ($isAccounts)
        <input type="hidden" name="report" value="{{ $selectedReport }}">
    @endif

    <div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 md:grid-cols-2">
        <label class="erp-field">
            <span class="erp-label">From date</span>
            <x-erp-date-input name="from_date" :value="request('from_date')" :default-blank="true" placeholder="All dates" />
        </label>
        <label class="erp-field">
            <span class="erp-label">To date</span>
            <x-erp-date-input name="to_date" :value="request('to_date')" :default-blank="true" placeholder="All dates" />
        </label>
    </div>

    <div class="grid gap-2 border border-slate-400 bg-[#fdfdfd] p-2 md:grid-cols-2 xl:grid-cols-3">
        <label class="erp-field xl:col-span-2">
            <span class="erp-label">Sub-ledger account (optional)</span>
            <select class="erp-input js-account-search" name="account_id" data-placeholder="All accounts">
                <option value="" @selected(! request()->filled('account_id'))>All</option>
                @foreach ($postableAccounts as $account)
                    <option
                        value="{{ $account->id }}"
                        @selected((string) request('account_id') === (string) $account->id)
                    >{{ $account->code }} — {{ $account->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="erp-field">
            <span class="erp-label">Or account code / name</span>
            <input class="erp-input" type="text" name="account_query" value="{{ request('account_query', request('account_search')) }}" placeholder="e.g. 03-03-0301-05 or MAHAD">
        </label>

        @unless ($isAccounts)
            <label class="erp-field">
                <span class="erp-label">Transaction screen (optional)</span>
                <select class="erp-input" name="screen_slug">
                    <option value="" @selected(! request()->filled('screen_slug'))>All</option>
                    @foreach ($inventoryScreenOptions as $option)
                        <option value="{{ $option['slug'] }}" @selected(request('screen_slug') === $option['slug'])>{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field">
                <span class="erp-label">Contract no. (optional)</span>
                <input class="erp-input" type="text" name="contract_query" value="{{ request('contract_query') }}" placeholder="Contract number">
            </label>
        @endunless

        <label class="erp-field">
            <span class="erp-label">Status (optional)</span>
            <select class="erp-input" name="status">
                <option value="" @selected(! request()->filled('status'))>All</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="posted" @selected(request('status') === 'posted')>Posted</option>
            </select>
        </label>
    </div>
    <p class="text-[11px] text-slate-600">
        @if ($isAccounts)
            Leave dates empty to include all dates. Account statement needs a sub-ledger account; other reports can run for all accounts.
        @else
            Leave dates empty to include all dates. All filters are optional.
        @endif
    </p>
</section>
