@php
    $direction = $direction ?? 'purchase';
    $editingTransaction = $editingTransaction ?? null;
    $contracts = $contracts ?? collect();
    $voucherType = $direction === 'sale' ? 'YSV' : 'YPV';
    $meta = $editingTransaction?->meta ?? [];
    $cancelUrl = route('erp.yarn.screen', array_merge(
        ['screen' => $screen['slug']],
        \App\Support\RecordHistory::historyQuery(request()),
    ));
    $selectedContract = $contracts->firstWhere('id', (int) old('yarn_contract_id', $editingTransaction?->yarn_contract_id));
    $yarnContractsPayload = $contracts->map(fn ($c) => [
        'id' => $c->id,
        'account_id' => $c->account_id,
        'contract_no' => $c->contract_no,
        'contract_code' => $c->contract_code,
        'contract_date' => $c->contract_date?->format('Y-m-d'),
        'item_id' => $c->item_id,
        'packing_size' => $c->packing_size,
        'packing_weight' => $c->packing_weight,
        'quantity' => $c->quantity,
        'no_of_cones' => $c->no_of_cones,
        'rate' => $c->rate,
        'commission_percent' => $c->commission_percent,
        'brokery_percent' => $c->brokery_percent,
        'yarn_type' => $c->yarn_type,
        'broker_account_id' => $c->broker_account_id,
        'broker_name' => $c->broker?->name,
        'weight_lbs' => $c->weight_lbs,
        'total_kgs' => $c->total_kgs,
        'total_amount' => $c->total_amount,
        'total_commission' => $c->total_commission,
        'total_brokery' => $c->total_brokery,
        'total_net_amount' => $c->total_net_amount,
    ])->values();
    $yarnItemsPayload = collect($yarnItemsPayload ?? ($items ?? collect())->map(fn ($item) => is_array($item) ? $item : [
        'id' => $item->id,
        'code' => $item->code,
        'name' => $item->name,
        'pack_size_cones' => $item->pack_size_cones,
        'packing_weight' => $item->packing_weight,
        'lov_label' => $item->code . ' — ' . $item->name,
    ]))->values();
@endphp

<div class="erp-panel erp-contract-wise-panel">
    <header class="erp-cw-header">
        <div class="erp-cw-header-copy">
            <p class="erp-cw-kicker">{{ $screen['code'] }}</p>
            <h1 class="erp-cw-title">{{ $screen['label'] }}</h1>
        </div>
        <div class="erp-cw-header-meta">
            <span class="erp-cw-chip">{{ $voucherType }}</span>
            <span class="erp-cw-chip erp-cw-chip--soft">{{ $editingTransaction ? 'Editing' : 'New entry' }}</span>
        </div>
    </header>

    <form
        class="erp-cw-form"
        data-yarn-contract-form
        data-erp-ajax-save
        @if($editingTransaction) data-erp-editing="1" @endif
        action="{{ $editingTransaction ? route('erp.yarn.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction]) : route('erp.yarn.screen.store', ['screen' => $screen['slug']]) }}"
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

        <div class="erp-cw-body">
            <section class="erp-cw-section erp-cw-section--voucher" data-section="voucher">
                <h3 class="erp-cw-section-title">Voucher</h3>
                <div class="erp-cw-section-grid">
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">Voucher #</span>
                        <input class="erp-input" type="text" value="{{ $editingTransaction?->trans_no ?? 'Auto' }}" readonly>
                    </label>
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">Date</span>
                        <x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" />
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Voucher type</span>
                        <input class="erp-input" name="meta[voucher_type]" value="{{ old('meta.voucher_type', $meta['voucher_type'] ?? $voucherType) }}" readonly>
                    </label>
                    <label class="erp-field erp-span-4">
                        <span class="erp-label">Remarks</span>
                        <input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}" placeholder="Optional notes">
                    </label>
                </div>
            </section>

            <section class="erp-cw-section" data-section="logistics">
                <h3 class="erp-cw-section-title">Logistics</h3>
                <div class="erp-cw-section-grid">
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">DO #</span>
                        <input class="erp-input" name="meta[do_no]" value="{{ old('meta.do_no', $meta['do_no'] ?? '') }}">
                    </label>
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">Bility #</span>
                        <input class="erp-input" name="meta[bility_no]" value="{{ old('meta.bility_no', $meta['bility_no'] ?? '') }}">
                    </label>
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">Vehicle #</span>
                        <input class="erp-input" name="meta[vehicle_no]" value="{{ old('meta.vehicle_no', $meta['vehicle_no'] ?? '') }}">
                    </label>
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">Driver name</span>
                        <input class="erp-input" name="meta[driver_name]" value="{{ old('meta.driver_name', $meta['driver_name'] ?? '') }}">
                    </label>
                </div>
            </section>

            <section class="erp-cw-section" data-section="party">
                <h3 class="erp-cw-section-title">Party &amp; contract</h3>
                <div class="erp-cw-section-grid">
                    <label class="erp-field erp-span-4">
                        <span class="erp-label">Godown</span>
                        <select class="erp-input" name="from_godown_id" required>
                            <option value="">Select godown</option>
                            @foreach(($godowns ?? []) as $godown)
                                <option value="{{ $godown->id }}" @selected((string) old('from_godown_id', $editingTransaction?->from_godown_id ?? $selectedContract?->godown_id) === (string) $godown->id)>{{ $godown->id }} — {{ $godown->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="erp-field erp-span-8">
                        <span class="erp-label">Party</span>
                        <select class="erp-input js-account-search" name="account_id" required>
                            <option value="">Select party</option>
                            @foreach(($accountParties ?? []) as $account)
                                <option value="{{ $account->id }}" @selected((string) old('account_id', $editingTransaction?->account_id ?? $selectedContract?->account_id) === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="erp-field erp-span-5">
                        <span class="erp-label">Contract</span>
                        <select class="erp-input" name="yarn_contract_id" required>
                            <option value="">Select contract</option>
                            @foreach($contracts as $contract)
                                <option
                                    value="{{ $contract->id }}"
                                    @selected((string) old('yarn_contract_id', $editingTransaction?->yarn_contract_id) === (string) $contract->id)
                                >{{ $contract->id }} — {{ $contract->contract_code ?? $contract->contract_no }} — {{ $contract->contract_date?->format('d-m-Y') }} — {{ $contract->item?->code }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="erp-field erp-span-3">
                        <span class="erp-label">Broker</span>
                        <input class="erp-input" type="text" data-yarn-broker-description readonly value="{{ $selectedContract?->broker?->name }}" placeholder="From contract">
                        <input type="hidden" name="broker_account_id" value="{{ old('broker_account_id', $meta['broker_account_id'] ?? $selectedContract?->broker_account_id) }}">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Commission %</span>
                        <input class="erp-input text-right" type="text" name="commission_percent" value="{{ old('commission_percent', $meta['commission_percent'] ?? $selectedContract?->commission_percent ?? 0) }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Brokery %</span>
                        <input class="erp-input text-right" type="text" name="brokery_percent" value="{{ old('brokery_percent', $meta['brokery_percent'] ?? $selectedContract?->brokery_percent ?? 0) }}" readonly tabindex="-1">
                    </label>
                </div>
            </section>

            <section class="erp-cw-section" data-section="yarn">
                <h3 class="erp-cw-section-title">Yarn &amp; quantity</h3>
                <div class="erp-cw-section-grid">
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Yarn type</span>
                        <input class="erp-input" type="text" name="yarn_type" value="{{ old('yarn_type', $meta['yarn_type'] ?? $selectedContract?->yarn_type ?? 'any') }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Yarn ID</span>
                        <select class="erp-input" data-yarn-item-display disabled>
                            <option value="">—</option>
                            @foreach(($items ?? []) as $item)
                                <option value="{{ $item->id }}" @selected((string) old('item_id', $meta['item_id'] ?? $selectedContract?->item_id) === (string) $item->id)>{{ $item->code }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="item_id" value="{{ old('item_id', $meta['item_id'] ?? $selectedContract?->item_id) }}">
                    </label>
                    <label class="erp-field erp-span-8">
                        <span class="erp-label">Yarn description</span>
                        <input class="erp-input" type="text" data-yarn-item-description readonly value="{{ $selectedContract?->item?->name }}" placeholder="Filled from contract">
                    </label>

                    <label class="erp-field erp-span-3">
                        <span class="erp-label">Packing size</span>
                        <input class="erp-input text-right" type="text" name="packing_size" value="{{ old('packing_size', $meta['packing_size'] ?? $selectedContract?->packing_size ?? 0) }}" readonly tabindex="-1" data-yarn-calc-trigger>
                    </label>
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">No of bags</span>
                        <input class="erp-input text-right" type="number" step="0.0001" min="0" name="quantity" value="{{ old('quantity', $meta['quantity'] ?? $editingTransaction?->total_qty ?? 0) }}" required data-yarn-calc-trigger>
                    </label>
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">No of cones</span>
                        <input class="erp-input text-right" type="number" step="0.0001" min="0" name="no_of_cones" value="{{ old('no_of_cones', $meta['no_of_cones'] ?? 0) }}" data-yarn-calc-trigger>
                    </label>
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">Rate / LBs</span>
                        <input class="erp-input text-right" type="number" step="0.0001" min="0" name="rate" value="{{ old('rate', $meta['rate'] ?? $selectedContract?->rate ?? 0) }}" required data-yarn-calc-trigger>
                    </label>
                </div>
            </section>

            <section class="erp-cw-section erp-cw-section--totals" data-section="totals">
                <h3 class="erp-cw-section-title">Totals</h3>
                <div class="erp-cw-section-grid erp-cw-totals-grid">
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Total LBs</span>
                        <input class="erp-input text-right" type="text" name="weight_lbs" value="{{ old('weight_lbs', $meta['weight_lbs'] ?? 0) }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Total KGs</span>
                        <input class="erp-input text-right" type="text" name="total_kgs" value="{{ old('total_kgs', $meta['total_kgs'] ?? 0) }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Amount</span>
                        <input class="erp-input text-right" type="text" name="total_amount" value="{{ old('total_amount', $meta['total_amount'] ?? 0) }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Commission</span>
                        <input class="erp-input text-right" type="text" name="total_commission" value="{{ old('total_commission', $meta['total_commission'] ?? 0) }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Brokery</span>
                        <input class="erp-input text-right" type="text" name="total_brokery" value="{{ old('total_brokery', $meta['total_brokery'] ?? 0) }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-cw-net erp-span-2">
                        <span class="erp-label">Net amount</span>
                        <input class="erp-input text-right" type="text" name="total_net_amount" value="{{ old('total_net_amount', $meta['total_net_amount'] ?? 0) }}" readonly tabindex="-1">
                    </label>
                </div>
            </section>
        </div>

        <input type="hidden" name="submit_action" value="post">
        <input type="hidden" name="lines[0][item_id]" value="{{ old('lines.0.item_id', $meta['item_id'] ?? $selectedContract?->item_id) }}">
        <input type="hidden" name="lines[0][qty]" value="{{ old('lines.0.qty', $meta['quantity'] ?? 0) }}">
        <input type="hidden" name="lines[0][weight_lbs]" value="{{ old('lines.0.weight_lbs', $meta['weight_lbs'] ?? 0) }}">
        <input type="hidden" name="lines[0][rate]" value="{{ old('lines.0.rate', $meta['rate'] ?? 0) }}">
        <input type="hidden" name="lines[0][amount]" value="{{ old('lines.0.amount', $meta['total_net_amount'] ?? 0) }}">

        <div class="erp-cw-actions">
            <a href="{{ route('erp.yarn.dashboard') }}" class="erp-cw-btn-secondary">Exit</a>
            <button type="submit" class="erp-cw-btn-primary">{{ $editingTransaction ? 'Update voucher' : 'Save voucher' }}</button>
        </div>
    </form>

    <div class="erp-cw-history">
        @include('erp.yarn.partials.recent-transactions')
    </div>
</div>

@push('scripts')
    <script>
        window.erpYarnItems = @json($yarnItemsPayload);
        window.erpYarnContracts = @json($yarnContractsPayload);
    </script>
@endpush
