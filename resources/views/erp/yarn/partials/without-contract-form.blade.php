@php
    $direction = $direction ?? 'purchase';
    $editingTransaction = $editingTransaction ?? null;
    $voucherType = $direction === 'sale' ? 'YSV' : 'YPV';
    $meta = $editingTransaction?->meta ?? [];
    $cancelUrl = route('erp.yarn.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
    $yarnItemsPayload = collect($yarnItemsPayload ?? ($items ?? collect())->map(fn ($item) => is_array($item) ? $item : [
        'id' => $item->id,
        'code' => $item->code,
        'name' => $item->name,
        'pack_size_cones' => $item->pack_size_cones,
        'packing_weight' => $item->packing_weight,
        'lov_label' => $item->code . ' — ' . $item->name,
    ]))->values();
@endphp

<div class="erp-panel border border-slate-500 bg-white shadow-md">
    <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
        {{ $screen['code'] }} — {{ $screen['label'] }}
    </div>

    <form
        class="space-y-3 p-3"
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
            @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingTransaction->trans_no, 'cancelUrl' => $cancelUrl])
        @endif

        <div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 md:grid-cols-2 lg:grid-cols-4">
            <label class="erp-field"><span class="erp-label">Voucher #</span><input class="erp-input bg-[#f0f0f0]" readonly value="{{ $editingTransaction?->trans_no ?? 'Auto' }}"></label>
            <label class="erp-field"><span class="erp-label">Date</span><x-erp-date-input name="trans_date" :value="old('trans_date', $editingTransaction?->trans_date)" :required="true" /></label>
            <label class="erp-field"><span class="erp-label">Voucher Type</span><input class="erp-input bg-[#f0f0f0]" name="meta[voucher_type]" value="{{ old('meta.voucher_type', $meta['voucher_type'] ?? $voucherType) }}" readonly></label>
            <label class="erp-field"><span class="erp-label">DO #</span><input class="erp-input" name="meta[do_no]" value="{{ old('meta.do_no', $meta['do_no'] ?? '') }}"></label>
            <label class="erp-field"><span class="erp-label">Bility #</span><input class="erp-input" name="meta[bility_no]" value="{{ old('meta.bility_no', $meta['bility_no'] ?? '') }}"></label>
            <label class="erp-field"><span class="erp-label">Vehicle #</span><input class="erp-input" name="meta[vehicle_no]" value="{{ old('meta.vehicle_no', $meta['vehicle_no'] ?? '') }}"></label>
            <label class="erp-field md:col-span-2"><span class="erp-label">Driver Name</span><input class="erp-input" name="meta[driver_name]" value="{{ old('meta.driver_name', $meta['driver_name'] ?? '') }}"></label>
            <label class="erp-field md:col-span-2"><span class="erp-label">Godown</span>
                <select class="erp-input" name="from_godown_id" required>
                    <option value="">Select godown</option>
                    @foreach(($godowns ?? []) as $godown)
                        <option value="{{ $godown->id }}" @selected((string) old('from_godown_id', $editingTransaction?->from_godown_id) === (string) $godown->id)>{{ $godown->id }} — {{ $godown->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field md:col-span-2"><span class="erp-label">Party ID and Name</span>
                <select class="erp-input js-account-search" name="account_id" required>
                    <option value="">Select party</option>
                    @foreach(($accountParties ?? []) as $account)
                        <option value="{{ $account->id }}" @selected((string) old('account_id', $editingTransaction?->account_id) === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field md:col-span-2"><span class="erp-label">Broker ID and Name</span>
                <select class="erp-input" name="broker_account_id">
                    <option value="">Select broker</option>
                    @foreach(($accountParties ?? []) as $account)
                        <option value="{{ $account->id }}" @selected((string) old('broker_account_id', $meta['broker_account_id'] ?? '') === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field"><span class="erp-label">Commission %</span><input class="erp-input text-right" type="number" step="0.0001" min="0" name="commission_percent" value="{{ old('commission_percent', $meta['commission_percent'] ?? 0) }}" data-yarn-calc-trigger></label>
            <label class="erp-field"><span class="erp-label">Brokery %</span><input class="erp-input text-right" type="number" step="0.0001" min="0" name="brokery_percent" value="{{ old('brokery_percent', $meta['brokery_percent'] ?? 0) }}" data-yarn-calc-trigger></label>
            <label class="erp-field"><span class="erp-label">Yarn Type</span>
                <select class="erp-input" name="yarn_type">
                    @foreach(['any' => 'Any', 'warp' => 'Warp', 'weft' => 'Weft'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('yarn_type', $meta['yarn_type'] ?? 'any') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field"><span class="erp-label">Yarn Id</span>
                <select class="erp-input" name="item_id" required data-yarn-item-select>
                    <option value="">Select yarn</option>
                    @foreach($yarnItemsPayload as $item)
                        <option value="{{ $item['id'] }}" data-payload="{{ json_encode($item) }}" @selected((string) old('item_id', $meta['item_id'] ?? '') === (string) $item['id'])>{{ $item['lov_label'] ?? ($item['code'] . ' — ' . $item['name']) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field"><span class="erp-label">Packing Size</span><input class="erp-input text-right bg-[#f0f0f0]" name="packing_size" value="{{ old('packing_size', $meta['packing_size'] ?? '') }}" readonly data-yarn-calc-trigger></label>
            <label class="erp-field"><span class="erp-label">No of Bags</span><input class="erp-input text-right" type="number" step="0.0001" min="0" name="quantity" value="{{ old('quantity', $meta['quantity'] ?? $editingTransaction?->total_qty ?? 0) }}" required data-yarn-calc-trigger></label>
            <label class="erp-field"><span class="erp-label">No of Cones</span><input class="erp-input text-right" type="number" step="0.0001" min="0" name="no_of_cones" value="{{ old('no_of_cones', $meta['no_of_cones'] ?? 0) }}" data-yarn-calc-trigger></label>
            <label class="erp-field"><span class="erp-label">Rate / LBs</span><input class="erp-input text-right" type="number" step="0.0001" min="0" name="rate" value="{{ old('rate', $meta['rate'] ?? 0) }}" required data-yarn-calc-trigger></label>
            <label class="erp-field"><span class="erp-label">Total LBs</span><input class="erp-input text-right bg-[#f0f0f0]" name="weight_lbs" value="{{ old('weight_lbs', $meta['weight_lbs'] ?? 0) }}" readonly></label>
            <label class="erp-field"><span class="erp-label">Total KGs</span><input class="erp-input text-right bg-[#f0f0f0]" name="total_kgs" value="{{ old('total_kgs', $meta['total_kgs'] ?? 0) }}" readonly></label>
            <label class="erp-field"><span class="erp-label">Total Amount</span><input class="erp-input text-right bg-[#f0f0f0]" name="total_amount" value="{{ old('total_amount', $meta['total_amount'] ?? 0) }}" readonly></label>
            <label class="erp-field"><span class="erp-label">Total Commission</span><input class="erp-input text-right bg-[#f0f0f0]" name="total_commission" value="{{ old('total_commission', $meta['total_commission'] ?? 0) }}" readonly></label>
            <label class="erp-field"><span class="erp-label">Total Brokery</span><input class="erp-input text-right bg-[#f0f0f0]" name="total_brokery" value="{{ old('total_brokery', $meta['total_brokery'] ?? 0) }}" readonly></label>
            <label class="erp-field md:col-span-2"><span class="erp-label">Total Net Amount</span><input class="erp-input text-right bg-[#f0f0f0] font-semibold" name="total_net_amount" value="{{ old('total_net_amount', $meta['total_net_amount'] ?? 0) }}" readonly></label>
            <label class="erp-field md:col-span-4"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
        </div>

        <input type="hidden" name="submit_action" value="post">
        <input type="hidden" name="lines[0][item_id]" value="{{ old('lines.0.item_id', $meta['item_id'] ?? '') }}">
        <input type="hidden" name="lines[0][qty]" value="{{ old('lines.0.qty', $meta['quantity'] ?? 0) }}">
        <input type="hidden" name="lines[0][weight_lbs]" value="{{ old('lines.0.weight_lbs', $meta['weight_lbs'] ?? 0) }}">
        <input type="hidden" name="lines[0][rate]" value="{{ old('lines.0.rate', $meta['rate'] ?? 0) }}">
        <input type="hidden" name="lines[0][amount]" value="{{ old('lines.0.amount', $meta['total_net_amount'] ?? 0) }}">

        <div class="flex flex-wrap gap-2 border border-slate-300 bg-[#f0f0f0] p-2">
            <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-[12px] font-semibold hover:bg-white">{{ $editingTransaction ? 'Update' : 'Save' }}</button>
        </div>
    </form>

    @include('erp.yarn.partials.recent-transactions')
</div>

@push('scripts')
    <script>window.erpYarnItems = @json($yarnItemsPayload);</script>
@endpush
