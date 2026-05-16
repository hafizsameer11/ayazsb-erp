@php
    $editingTransaction = $editingTransaction ?? null;
    $moduleKey = $moduleKey ?? 'yarn';
    $cancelUrl = route('erp.' . $moduleKey . '.screen', array_merge(
        ['screen' => $screen['slug']],
        \App\Support\RecordHistory::historyQuery(request()),
    ));
    $contractMode = $contractMode ?? 'single';
    $singleContracts = $singleContracts ?? ($contracts ?? collect());
    $showGodownPair = $showGodownPair ?? false;
    $showSourceIssue = $showSourceIssue ?? false;
    $showAdjustment = $showAdjustment ?? false;
    $lineLabel = $lineLabel ?? 'Yarn detail lines';
    $defaultVoucherType = $defaultVoucherType ?? strtoupper(str_replace('-', '_', $screen['slug']));
@endphp

<div class="erp-panel border border-slate-500 bg-white shadow-md">
    <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
        {{ $screen['code'] }} — {{ $screen['label'] }}
    </div>
    <form
        class="space-y-3 p-3"
        data-erp-ajax-save
        @if($editingTransaction) data-erp-editing="1" @endif
        action="{{ $editingTransaction ? route('erp.' . $moduleKey . '.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.' . $moduleKey . '.screen.store', ['screen' => $screen['slug']]) }}"
        method="post"
    >
        @csrf
        <div data-erp-form-feedback class="hidden" aria-live="polite"></div>
        @if ($editingTransaction)
            @method('PATCH')
            @include('erp.partials.erp-editing-banner', [
                'editingLabel' => $editingTransaction->trans_no,
                'cancelUrl' => $cancelUrl,
            ])
        @endif
        <div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 md:grid-cols-2 lg:grid-cols-4">
            <label class="erp-field"><span class="erp-label">Trans #</span><input class="erp-input" value="{{ $editingTransaction?->trans_no ?? 'Auto' }}" readonly></label>
            <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
            <label class="erp-field"><span class="erp-label">Voucher type</span><input class="erp-input" name="meta[voucher_type]" value="{{ old('meta.voucher_type', $editingTransaction?->meta['voucher_type'] ?? $defaultVoucherType) }}"></label>
            <label class="erp-field"><span class="erp-label">Reference</span><input class="erp-input" name="meta[ref_no]" value="{{ old('meta.ref_no', $editingTransaction?->meta['ref_no'] ?? '') }}"></label>

            @if($contractMode === 'single')
                <label class="erp-field md:col-span-2">
                    <span class="erp-label">Contract Id</span>
                    <select class="erp-input" name="yarn_contract_id" required>
                        <option value="">Select contract</option>
                        @foreach($singleContracts as $contract)
                            <option value="{{ $contract->id }}" @selected((string) old('yarn_contract_id', $editingTransaction?->yarn_contract_id) === (string) $contract->id)>
                                {{ $contract->contract_no }} — {{ $contract->account?->name }} — {{ $contract->item?->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field md:col-span-2">
                    <span class="erp-label">Account</span>
                    <select class="erp-input" name="account_id">
                        <option value="">Use contract account</option>
                        @foreach(($accountParties ?? []) as $account)
                            <option value="{{ $account->id }}" @selected((string) old('account_id', $editingTransaction?->account_id) === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                        @endforeach
                    </select>
                </label>
            @elseif($contractMode === 'transfer')
                <label class="erp-field md:col-span-2">
                    <span class="erp-label">From Contract Id</span>
                    <select class="erp-input" name="from_yarn_contract_id" required>
                        <option value="">Select from contract</option>
                        @foreach(($contracts ?? []) as $contract)
                            <option value="{{ $contract->id }}" @selected((string) old('from_yarn_contract_id', $editingTransaction?->from_yarn_contract_id) === (string) $contract->id)>
                                {{ $contract->contract_no }} — {{ $contract->account?->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field md:col-span-2">
                    <span class="erp-label">To Contract Id</span>
                    <select class="erp-input" name="to_yarn_contract_id" required>
                        <option value="">Select to contract</option>
                        @foreach(($contracts ?? []) as $contract)
                            <option value="{{ $contract->id }}" @selected((string) old('to_yarn_contract_id', $editingTransaction?->to_yarn_contract_id) === (string) $contract->id)>
                                {{ $contract->contract_no }} — {{ $contract->account?->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
            @else
                <label class="erp-field md:col-span-2">
                    <span class="erp-label">Account</span>
                    <select class="erp-input" name="account_id">
                        <option value="">Select account</option>
                        @foreach(($accountParties ?? []) as $account)
                            <option value="{{ $account->id }}" @selected((string) old('account_id', $editingTransaction?->account_id) === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            @if($showSourceIssue)
                <label class="erp-field">
                    <span class="erp-label">Issue Id</span>
                    <select class="erp-input" name="source_transaction_id">
                        <option value="">Select issue</option>
                        @foreach(($issueTransactions ?? []) as $issue)
                            <option value="{{ $issue->id }}" @selected((string) old('source_transaction_id', $editingTransaction?->source_transaction_id) === (string) $issue->id)>
                                {{ $issue->trans_no }} — {{ $issue->yarnContract?->contract_no }}
                            </option>
                        @endforeach
                    </select>
                </label>
            @endif

            @if($showGodownPair)
                <label class="erp-field">
                    <span class="erp-label">From Godown</span>
                    <select class="erp-input" name="from_godown_id" required>
                        <option value="">Select from</option>
                        @foreach(($godowns ?? []) as $godown)
                            <option value="{{ $godown->id }}" @selected((string) old('from_godown_id', $editingTransaction?->from_godown_id) === (string) $godown->id)>{{ $godown->code }} — {{ $godown->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field">
                    <span class="erp-label">To Godown</span>
                    <select class="erp-input" name="to_godown_id" required>
                        <option value="">Select to</option>
                        @foreach(($godowns ?? []) as $godown)
                            <option value="{{ $godown->id }}" @selected((string) old('to_godown_id', $editingTransaction?->to_godown_id) === (string) $godown->id)>{{ $godown->code }} — {{ $godown->name }}</option>
                        @endforeach
                    </select>
                </label>
            @else
                <label class="erp-field">
                    <span class="erp-label">Godown</span>
                    <select class="erp-input" name="from_godown_id">
                        <option value="">Use contract godown</option>
                        @foreach(($godowns ?? []) as $godown)
                            <option value="{{ $godown->id }}" @selected((string) old('from_godown_id', $editingTransaction?->from_godown_id) === (string) $godown->id)>{{ $godown->code }} — {{ $godown->name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            <label class="erp-field"><span class="erp-label">Yarn tag</span><input class="erp-input" name="meta[yarn_tag]" value="{{ old('meta.yarn_tag', $editingTransaction?->meta['yarn_tag'] ?? '') }}"></label>
            <label class="erp-field md:col-span-2"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
        </div>

        <div class="text-[11px] font-semibold uppercase text-slate-600">{{ $lineLabel }}</div>
        <div data-erp-detail-lines data-name-prefix="lines" class="space-y-1">
            <div class="overflow-x-auto border border-slate-400">
            <table class="w-full min-w-[1080px] border-collapse text-[12px]">
                <thead>
                    <tr class="bg-[#d8d8d8]">
                        <th class="border border-slate-400 px-1 py-1">Yarn</th>
                        <th class="border border-slate-400 px-1 py-1">Type</th>
                        <th class="border border-slate-400 px-1 py-1">Description</th>
                        <th class="border border-slate-400 px-1 py-1">Bags / Qty</th>
                        <th class="border border-slate-400 px-1 py-1">Cones</th>
                        <th class="border border-slate-400 px-1 py-1">Weight LBS</th>
                        <th class="border border-slate-400 px-1 py-1">Rate</th>
                        <th class="border border-slate-400 px-1 py-1">Sale / Transfer rate</th>
                        @if($showAdjustment)
                            <th class="border border-slate-400 px-1 py-1">Gain / Short</th>
                        @endif
                        <th class="border border-slate-400 px-1 py-1">Amount</th>
                    </tr>
                </thead>
                <tbody data-erp-detail-lines-body>
                    @php
                        $editingLines = $editingTransaction?->lines;
                        $lineRowCount = $editingLines && $editingLines->isNotEmpty() ? max(3, $editingLines->count()) : 3;
                    @endphp
                    @for ($i = 0; $i < $lineRowCount; $i++)
                        @include('erp.yarn.partials.movement-line-row', ['i' => $i, 'line' => $editingLines?->get($i), 'showAdjustment' => $showAdjustment])
                    @endfor
                </tbody>
            </table>
            </div>
            <template data-erp-detail-line-template>
                @include('erp.yarn.partials.movement-line-row', ['i' => 0, 'showAdjustment' => $showAdjustment])
            </template>
            @include('erp.partials.erp-add-line-row')
        </div>

        <div class="flex flex-wrap gap-2 border border-slate-300 bg-[#f0f0f0] p-2">
            <button type="submit" name="submit_action" value="post" class="rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-[12px] font-semibold hover:bg-white">{{ $editingTransaction ? 'Update' : 'Save' }}</button>
        </div>
    </form>

    @include('erp.yarn.partials.recent-transactions')
</div>
