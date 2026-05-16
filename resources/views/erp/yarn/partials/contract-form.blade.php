@php
    $direction = $direction ?? 'purchase';
    $editingContract = $editingContract ?? null;
    $codePrefix = $direction === 'sale' ? 'YSC' : 'YPC';
    $contractCode = old('contract_code', $editingContract?->contract_code ?? ($editingContract ? $codePrefix . $editingContract->contract_no : ''));
    $statusVal = old('status', $editingContract?->status ?? 'open');
    if ($statusVal === 'closed') {
        $statusVal = 'close';
    }
    $cancelUrl = route('erp.yarn.screen', array_merge(
        ['screen' => $screen['slug']],
        \App\Support\RecordHistory::historyQuery(request()),
    ));
    $yarnItemsPayload = ($items ?? collect())->map(fn ($item) => [
        'id' => $item->id,
        'code' => $item->code,
        'name' => $item->name,
        'pack_size_cones' => $item->pack_size_cones,
        'packing_weight' => $item->packing_weight,
    ])->values();
@endphp

<div class="erp-panel border border-slate-500 bg-white shadow-md">
    <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
        {{ $screen['code'] }} — {{ $screen['label'] }}
    </div>

    <form
        class="space-y-3 p-3"
        data-yarn-contract-form
        data-erp-ajax-save
        @if($editingContract) data-erp-editing="1" @endif
        action="{{ $editingContract ? route('erp.yarn.screen.contract.update', ['screen' => $screen['slug'], 'contract' => $editingContract]) : route('erp.yarn.screen.store', ['screen' => $screen['slug']]) }}"
        method="post"
    >
        @csrf
        <div data-erp-form-feedback class="hidden" aria-live="polite"></div>
        @if ($editingContract)
            @method('PATCH')
            @include('erp.partials.erp-editing-banner', [
                'editingLabel' => $editingContract->contract_no,
                'cancelUrl' => $cancelUrl,
            ])
        @endif

        <div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 md:grid-cols-2 lg:grid-cols-4">
            <label class="erp-field">
                <span class="erp-label">Contract #</span>
                <input class="erp-input" name="contract_no" value="{{ old('contract_no', $editingContract?->contract_no) }}" required autocomplete="off" @if($editingContract) readonly @endif data-yarn-calc-trigger>
            </label>
            <label class="erp-field">
                <span class="erp-label">Date</span>
                <x-erp-date-input name="contract_date" :value="old('contract_date', $editingContract?->contract_date)" :required="true" />
            </label>
            <label class="erp-field">
                <span class="erp-label">Contract ID</span>
                <input class="erp-input border border-slate-400 bg-[#f0f0f0]" type="text" value="{{ $editingContract?->id ?? 'Auto' }}" readonly>
            </label>
            <label class="erp-field">
                <span class="erp-label">Contract Code</span>
                <input class="erp-input border border-slate-400 bg-[#f0f0f0]" name="contract_code" value="{{ $contractCode }}" readonly>
            </label>

            <label class="erp-field">
                <span class="erp-label">Contract Status</span>
                <select class="erp-input" name="status" required>
                    @foreach(['open' => 'Open', 'close' => 'Close'] as $value => $label)
                        <option value="{{ $value }}" @selected($statusVal === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field">
                <span class="erp-label">Payment Term</span>
                <select class="erp-input" name="payment_term" required>
                    @foreach(['cash' => 'Cash', 'credit' => 'Credit'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_term', $editingContract?->payment_term ?? 'cash') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="erp-field md:col-span-2">
                <span class="erp-label">Party ID and Name</span>
                <select class="erp-input js-account-search" name="account_id" required>
                    <option value="">Select party</option>
                    @foreach(($accountParties ?? []) as $account)
                        <option value="{{ $account->id }}" @selected((string) old('account_id', $editingContract?->account_id) === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="erp-field md:col-span-2">
                <span class="erp-label">Broker ID and Name</span>
                <select class="erp-input js-account-search" name="broker_account_id">
                    <option value="">Select broker</option>
                    @foreach(($accountParties ?? []) as $account)
                        <option value="{{ $account->id }}" @selected((string) old('broker_account_id', $editingContract?->broker_account_id) === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field">
                <span class="erp-label">Commission %</span>
                <input class="erp-input text-right" type="number" step="0.0001" min="0" name="commission_percent" value="{{ old('commission_percent', $editingContract?->commission_percent ?? 0) }}" data-yarn-calc-trigger>
            </label>
            <label class="erp-field">
                <span class="erp-label">Brokery %</span>
                <input class="erp-input text-right" type="number" step="0.0001" min="0" name="brokery_percent" value="{{ old('brokery_percent', $editingContract?->brokery_percent ?? 0) }}" data-yarn-calc-trigger>
            </label>

            <label class="erp-field">
                <span class="erp-label">Yarn Type</span>
                <select class="erp-input" name="yarn_type">
                    @foreach(['any' => 'Any', 'warp' => 'Warp', 'weft' => 'Weft'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('yarn_type', $editingContract?->yarn_type ?? 'any') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field">
                <span class="erp-label">Yarn ID</span>
                <select class="erp-input" name="item_id" required>
                    <option value="">Select yarn</option>
                    @foreach(($items ?? []) as $item)
                        <option value="{{ $item->id }}" @selected((string) old('item_id', $editingContract?->item_id) === (string) $item->id)>{{ $item->code }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field md:col-span-2">
                <span class="erp-label">Yarn description</span>
                <input class="erp-input border border-slate-400 bg-[#f0f0f0]" type="text" data-yarn-item-description readonly value="{{ $editingContract?->item ? $editingContract->item->code . ' — ' . $editingContract->item->name : '' }}">
            </label>

            <label class="erp-field">
                <span class="erp-label">Packing Size (Cones)</span>
                <input class="erp-input text-right" type="number" step="1" min="0" name="packing_size" value="{{ old('packing_size', $editingContract?->packing_size ?? $editingContract?->item?->pack_size_cones ?? 0) }}" required data-yarn-calc-trigger>
            </label>
            <label class="erp-field">
                <span class="erp-label">No of Bags</span>
                <input class="erp-input text-right" type="number" step="0.0001" min="0" name="quantity" value="{{ old('quantity', $editingContract?->quantity ?? 0) }}" required data-yarn-calc-trigger>
            </label>
            <label class="erp-field">
                <span class="erp-label">No of Cones</span>
                <input class="erp-input text-right" type="number" step="0.0001" min="0" name="no_of_cones" value="{{ old('no_of_cones', $editingContract?->no_of_cones ?? 0) }}" data-yarn-calc-trigger>
            </label>
            <label class="erp-field">
                <span class="erp-label">Rate / LBs</span>
                <input class="erp-input text-right" type="number" step="0.0001" min="0" name="rate" value="{{ old('rate', $editingContract?->rate ?? 0) }}" required data-yarn-calc-trigger>
            </label>

            <label class="erp-field">
                <span class="erp-label">Total LBs</span>
                <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0]" type="text" name="weight_lbs" value="{{ old('weight_lbs', $editingContract?->weight_lbs ?? 0) }}" readonly tabindex="-1">
            </label>
            <label class="erp-field">
                <span class="erp-label">Total KGs</span>
                <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0]" type="text" name="total_kgs" value="{{ old('total_kgs', $editingContract?->total_kgs ?? 0) }}" readonly tabindex="-1">
            </label>
            <label class="erp-field">
                <span class="erp-label">Total Amount</span>
                <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0]" type="text" name="total_amount" value="{{ old('total_amount', $editingContract?->total_amount ?? 0) }}" readonly tabindex="-1">
            </label>
            <label class="erp-field">
                <span class="erp-label">Total Commission</span>
                <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0]" type="text" name="total_commission" value="{{ old('total_commission', $editingContract?->total_commission ?? 0) }}" readonly tabindex="-1">
            </label>
            <label class="erp-field">
                <span class="erp-label">Total Brokery</span>
                <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0]" type="text" name="total_brokery" value="{{ old('total_brokery', $editingContract?->total_brokery ?? 0) }}" readonly tabindex="-1">
            </label>
            <label class="erp-field md:col-span-2">
                <span class="erp-label">Total Net Amount</span>
                <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0] text-[14px] font-semibold" type="text" name="total_net_amount" value="{{ old('total_net_amount', $editingContract?->total_net_amount ?? 0) }}" readonly tabindex="-1">
            </label>

            <label class="erp-field md:col-span-4">
                <span class="erp-label">Remarks</span>
                <input class="erp-input" name="remarks" value="{{ old('remarks', $editingContract?->remarks) }}">
            </label>
        </div>

        <div class="flex gap-2 border border-slate-300 bg-[#f0f0f0] p-2">
            <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-[12px] font-semibold hover:bg-white">{{ $editingContract ? 'Update' : 'Save' }}</button>
            <a href="{{ route('erp.yarn.dashboard') }}" class="rounded border border-slate-500 bg-white px-4 py-1.5 text-[12px] font-semibold hover:bg-slate-50">Exit</a>
        </div>
    </form>

    @include('erp.partials.records-history', [
        'historyType' => 'contract',
        'historyTitle' => 'Saved ' . $direction . ' contracts',
        'historyEmpty' => 'No ' . $direction . ' contracts yet. Save a contract above; records will list here grouped by date.',
        'recordsForDay' => $recordsForDay ?? collect(),
        'historyDate' => $historyDate ?? null,
        'historyNav' => $historyNav ?? [],
        'moduleKey' => 'yarn',
        'screen' => $screen,
        'permissionPrefix' => $permissionPrefix ?? ('yarn.' . $screen['slug']),
    ])
</div>

@push('scripts')
    <script>
        window.erpYarnItems = @json($yarnItemsPayload);
        document.querySelector('[data-yarn-contract-form]')?.querySelector('[name="contract_no"]')?.addEventListener('input', (e) => {
            const prefix = @json($codePrefix);
            const code = document.querySelector('[name="contract_code"]');
            if (code && !code.dataset.locked) {
                code.value = prefix + (e.target.value || '');
            }
        });
    </script>
@endpush
