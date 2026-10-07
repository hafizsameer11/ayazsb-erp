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

<div class="erp-panel erp-contract-wise-panel border border-slate-500 bg-white shadow-md" data-erp-ui-panel>
    <div class="erp-cw-header border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
        <div class="erp-cw-header-row">
            <span class="erp-cw-title">{{ $screen['code'] }} — {{ $screen['label'] }}</span>
            <div class="erp-ui-mode-toggle" role="group" aria-label="UI mode">
                <span class="erp-ui-mode-label">UI</span>
                <button type="button" class="erp-ui-mode-btn" data-erp-ui-mode="classic">Classic</button>
                <button type="button" class="erp-ui-mode-btn" data-erp-ui-mode="modern">Modern</button>
            </div>
        </div>
    </div>

    <form
        class="erp-cw-form space-y-3 p-3"
        data-yarn-contract-form
        data-erp-ajax-save
        data-erp-ui-form
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

        <div class="erp-cw-body grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 md:grid-cols-2 lg:grid-cols-4">
            <section class="erp-cw-section erp-cw-section--voucher md:col-span-2 lg:col-span-4" data-section="voucher">
                <h3 class="erp-cw-section-title">Voucher</h3>
                <div class="erp-cw-section-grid grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">Voucher #</span>
                        <input class="erp-input border border-slate-400 bg-[#f0f0f0]" type="text" value="{{ $editingTransaction?->trans_no ?? 'Auto' }}" readonly>
                    </label>
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">Date</span>
                        <x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" />
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Voucher Type</span>
                        <input class="erp-input border border-slate-400 bg-[#f0f0f0]" name="meta[voucher_type]" value="{{ old('meta.voucher_type', $meta['voucher_type'] ?? $voucherType) }}" readonly>
                    </label>
                    <label class="erp-field erp-span-4 md:col-span-2 lg:col-span-4">
                        <span class="erp-label">Remarks</span>
                        <input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}">
                    </label>
                </div>
            </section>

            <section class="erp-cw-section erp-cw-section--logistics md:col-span-2 lg:col-span-4" data-section="logistics">
                <h3 class="erp-cw-section-title">Logistics</h3>
                <div class="erp-cw-section-grid grid gap-2 md:grid-cols-2 lg:grid-cols-4">
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
                        <span class="erp-label">Driver Name</span>
                        <input class="erp-input" name="meta[driver_name]" value="{{ old('meta.driver_name', $meta['driver_name'] ?? '') }}">
                    </label>
                </div>
            </section>

            <section class="erp-cw-section erp-cw-section--party md:col-span-2 lg:col-span-4" data-section="party">
                <h3 class="erp-cw-section-title">Party &amp; contract</h3>
                <div class="erp-cw-section-grid grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                    <label class="erp-field erp-span-4 md:col-span-2">
                        <span class="erp-label">Godown</span>
                        <select class="erp-input" name="from_godown_id" required>
                            <option value="">Select godown</option>
                            @foreach(($godowns ?? []) as $godown)
                                <option value="{{ $godown->id }}" @selected((string) old('from_godown_id', $editingTransaction?->from_godown_id ?? $selectedContract?->godown_id) === (string) $godown->id)>{{ $godown->id }} — {{ $godown->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="erp-field erp-span-8 md:col-span-2">
                        <span class="erp-label">Party ID and Name</span>
                        <select class="erp-input js-account-search" name="account_id" required>
                            <option value="">Select party</option>
                            @foreach(($accountParties ?? []) as $account)
                                <option value="{{ $account->id }}" @selected((string) old('account_id', $editingTransaction?->account_id ?? $selectedContract?->account_id) === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="erp-field erp-span-5 md:col-span-2">
                        <span class="erp-label">Contract ID</span>
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

                    <label class="erp-field erp-span-3 md:col-span-2">
                        <span class="erp-label">Broker ID and Name</span>
                        <input class="erp-input border border-slate-400 bg-[#f0f0f0]" type="text" data-yarn-broker-description readonly value="{{ $selectedContract?->broker?->name }}">
                        <input type="hidden" name="broker_account_id" value="{{ old('broker_account_id', $meta['broker_account_id'] ?? $selectedContract?->broker_account_id) }}">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Commission %</span>
                        <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0]" type="text" name="commission_percent" value="{{ old('commission_percent', $meta['commission_percent'] ?? $selectedContract?->commission_percent ?? 0) }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Brokery %</span>
                        <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0]" type="text" name="brokery_percent" value="{{ old('brokery_percent', $meta['brokery_percent'] ?? $selectedContract?->brokery_percent ?? 0) }}" readonly tabindex="-1">
                    </label>
                </div>
            </section>

            <section class="erp-cw-section erp-cw-section--yarn md:col-span-2 lg:col-span-4" data-section="yarn">
                <h3 class="erp-cw-section-title">Yarn &amp; quantity</h3>
                <div class="erp-cw-section-grid grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Yarn Type</span>
                        <input class="erp-input border border-slate-400 bg-[#f0f0f0]" type="text" name="yarn_type" value="{{ old('yarn_type', $meta['yarn_type'] ?? $selectedContract?->yarn_type ?? 'any') }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Yarn ID</span>
                        <select class="erp-input border border-slate-400 bg-[#f0f0f0]" data-yarn-item-display disabled>
                            <option value="">—</option>
                            @foreach(($items ?? []) as $item)
                                <option value="{{ $item->id }}" @selected((string) old('item_id', $meta['item_id'] ?? $selectedContract?->item_id) === (string) $item->id)>{{ $item->code }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="item_id" value="{{ old('item_id', $meta['item_id'] ?? $selectedContract?->item_id) }}">
                    </label>
                    <label class="erp-field erp-span-8 md:col-span-2">
                        <span class="erp-label">Yarn description</span>
                        <input class="erp-input border border-slate-400 bg-[#f0f0f0]" type="text" data-yarn-item-description readonly value="{{ $selectedContract?->item?->name }}">
                    </label>

                    <label class="erp-field erp-span-3">
                        <span class="erp-label">Packing Size</span>
                        <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0]" type="text" name="packing_size" value="{{ old('packing_size', $meta['packing_size'] ?? $selectedContract?->packing_size ?? 0) }}" readonly tabindex="-1" data-yarn-calc-trigger>
                    </label>
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">No of Bags</span>
                        <input class="erp-input text-right" type="number" step="0.0001" min="0" name="quantity" value="{{ old('quantity', $meta['quantity'] ?? $editingTransaction?->total_qty ?? 0) }}" required data-yarn-calc-trigger>
                    </label>
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">No of Cones</span>
                        <input class="erp-input text-right" type="number" step="0.0001" min="0" name="no_of_cones" value="{{ old('no_of_cones', $meta['no_of_cones'] ?? 0) }}" data-yarn-calc-trigger>
                    </label>
                    <label class="erp-field erp-span-3">
                        <span class="erp-label">Rate / LBs</span>
                        <input class="erp-input text-right" type="number" step="0.0001" min="0" name="rate" value="{{ old('rate', $meta['rate'] ?? $selectedContract?->rate ?? 0) }}" required data-yarn-calc-trigger>
                    </label>
                </div>
            </section>

            <section class="erp-cw-section erp-cw-section--totals md:col-span-2 lg:col-span-4" data-section="totals">
                <h3 class="erp-cw-section-title">Totals</h3>
                <div class="erp-cw-section-grid erp-cw-totals-grid grid gap-2 md:grid-cols-2 lg:grid-cols-4">
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Total LBs</span>
                        <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0]" type="text" name="weight_lbs" value="{{ old('weight_lbs', $meta['weight_lbs'] ?? 0) }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Total KGs</span>
                        <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0]" type="text" name="total_kgs" value="{{ old('total_kgs', $meta['total_kgs'] ?? 0) }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Total Amount</span>
                        <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0]" type="text" name="total_amount" value="{{ old('total_amount', $meta['total_amount'] ?? 0) }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Commission</span>
                        <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0]" type="text" name="total_commission" value="{{ old('total_commission', $meta['total_commission'] ?? 0) }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-span-2">
                        <span class="erp-label">Brokery</span>
                        <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0]" type="text" name="total_brokery" value="{{ old('total_brokery', $meta['total_brokery'] ?? 0) }}" readonly tabindex="-1">
                    </label>
                    <label class="erp-field erp-cw-net erp-span-4 md:col-span-2">
                        <span class="erp-label">Net Amount</span>
                        <input class="erp-input text-right border border-slate-400 bg-[#f0f0f0] text-[14px] font-semibold" type="text" name="total_net_amount" value="{{ old('total_net_amount', $meta['total_net_amount'] ?? 0) }}" readonly tabindex="-1">
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

        <div class="erp-cw-actions flex flex-wrap gap-2 border border-slate-300 bg-[#f0f0f0] p-2">
            <button type="submit" class="erp-cw-btn-primary rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-[12px] font-semibold hover:bg-white">{{ $editingTransaction ? 'Update' : 'Save' }}</button>
            <a href="{{ route('erp.yarn.dashboard') }}" class="erp-cw-btn-secondary rounded border border-slate-500 bg-white px-4 py-1.5 text-[12px] font-semibold hover:bg-slate-50">Exit</a>
        </div>
    </form>

    @include('erp.yarn.partials.recent-transactions')
</div>

@push('scripts')
    <script>
        window.erpYarnItems = @json($yarnItemsPayload);
        window.erpYarnContracts = @json($yarnContractsPayload);

        (function () {
            var STORAGE_KEY = 'erp-ui-mode';
            var panel = document.querySelector('[data-erp-ui-panel]');
            if (!panel) return;

            function applyMode(mode) {
                var isModern = mode === 'modern';
                panel.classList.toggle('erp-ui-modern', isModern);
                panel.querySelectorAll('[data-erp-ui-mode]').forEach(function (btn) {
                    var active = btn.getAttribute('data-erp-ui-mode') === mode;
                    btn.classList.toggle('is-active', active);
                    btn.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            }

            var saved = null;
            try {
                saved = localStorage.getItem(STORAGE_KEY);
            } catch (e) {}

            applyMode(saved === 'classic' || saved === 'modern' ? saved : 'modern');

            panel.querySelectorAll('[data-erp-ui-mode]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var mode = btn.getAttribute('data-erp-ui-mode');
                    applyMode(mode);
                    try {
                        localStorage.setItem(STORAGE_KEY, mode);
                    } catch (e) {}
                });
            });
        })();
    </script>
@endpush
