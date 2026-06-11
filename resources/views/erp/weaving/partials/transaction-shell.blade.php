@php
    $editingTransaction = $editingTransaction ?? null;
    $meta = $editingTransaction?->meta ?? [];
    $cancelUrl = route('erp.weaving.screen', array_merge(['screen' => $screen['slug']], \App\Support\RecordHistory::historyQuery(request())));
    $storeAction = route('erp.weaving.screen.store', ['screen' => $screen['slug']]);
    $updateAction = $editingTransaction
        ? route('erp.weaving.screen.update', ['screen' => $screen['slug'], 'transaction' => $editingTransaction])
        : null;
    $showVoucher = $showVoucher ?? true;
    $showDepartment = $showDepartment ?? false;
    $showParty = $showParty ?? false;
    $showSourcePo = $showSourcePo ?? false;
    $gridPartial = $gridPartial ?? 'erp.weaving.partials.store-line-grid';
    $headerPartial = $headerPartial ?? null;
    $panelTitle = $panelTitle ?? strtoupper($screen['label']);
@endphp

<div class="flex min-h-[calc(100vh-8rem)] flex-col">
    <div class="erp-panel shrink-0 border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            {{ $screen['code'] }} — {{ $panelTitle }}
        </div>
        <form
            id="weaving-form-{{ $screen['slug'] }}"
            class="space-y-2 p-3"
            data-erp-ajax-save
            data-weaving-totals-form
            @if($editingTransaction) data-erp-editing="1" @endif
            action="{{ $updateAction ?? $storeAction }}"
            method="post"
        >
            @csrf
            @if($editingTransaction) @method('PATCH') @endif
            <div data-erp-form-feedback class="hidden" aria-live="polite"></div>
            @if ($editingTransaction)
                @include('erp.partials.erp-editing-banner', ['editingLabel' => $editingTransaction->trans_no, 'cancelUrl' => $cancelUrl])
            @endif

            @if ($headerPartial)
                @include($headerPartial, compact('editingTransaction', 'meta', 'screen'))
            @else
                <div class="grid gap-2 md:grid-cols-4 lg:grid-cols-6">
                    @if ($editingTransaction)
                        <label class="erp-field"><span class="erp-label">Trans #</span><input class="erp-input bg-[#f8f8f8]" value="{{ $editingTransaction->trans_no }}" readonly></label>
                    @endif
                    <label class="erp-field"><span class="erp-label">Date</span><input class="erp-input" type="date" name="trans_date" value="{{ old('trans_date', optional($editingTransaction?->trans_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required></label>
                    @if ($showDepartment)
                        <label class="erp-field"><span class="erp-label">Dept</span>
                            <select class="erp-input" name="department_id">
                                <option value="">—</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected(old('department_id', $editingTransaction?->department_id) == $dept->id)>{{ $dept->code }} — {{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    @if ($showParty)
                        <label class="erp-field md:col-span-2"><span class="erp-label">Party Code</span>
                            @include('erp.grey.partials.code-name-pair', [
                                'selectName' => 'account_id',
                                'selectedId' => old('account_id', $editingTransaction?->account_id),
                                'options' => $accountParties,
                                'required' => (bool) data_get(config('weaving_vouchers.screens.' . $screen['slug']), 'requires_party'),
                                'targetId' => 'weaving-party-' . $screen['slug'],
                            ])
                        </label>
                    @endif
                    @if ($showSourcePo)
                        <label class="erp-field md:col-span-2"><span class="erp-label">Source PO</span>
                            <select class="erp-input" name="source_transaction_id">
                                <option value="">—</option>
                                @foreach ($purchaseOrders as $po)
                                    <option value="{{ $po->id }}" @selected(old('source_transaction_id', $editingTransaction?->source_transaction_id) == $po->id)>{{ $po->trans_no }} ({{ optional($po->trans_date)->format('d/m/Y') }})</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    <label class="erp-field md:col-span-2"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks', $editingTransaction?->remarks) }}"></label>
                </div>
            @endif

            @include($gridPartial, [
                'editingTransaction' => $editingTransaction,
                'meta' => $meta,
            ])

            @if ($showVoucher)
                @include('erp.weaving.partials.voucher-footer', [
                    'editingTransaction' => $editingTransaction,
                    'meta' => $meta,
                    'screen' => $screen,
                ])
            @endif

            <input type="hidden" name="submit_action" value="save">
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold hover:bg-white">Save</button>
                @allowed($permissionPrefix . '.post')
                    <button type="submit" name="submit_action" value="post" class="rounded border border-emerald-700 bg-emerald-100 px-4 py-1 text-[12px] font-semibold hover:bg-emerald-50">Save &amp; Post</button>
                @endallowed
            </div>
        </form>
    </div>
    <div class="erp-panel flex min-h-0 flex-1 flex-col border border-t-0 border-slate-500 bg-white shadow-md">
        @include('erp.partials.records-history', ['historyType' => 'transaction', 'moduleKey' => 'weaving', 'screen' => $screen, 'permissionPrefix' => $permissionPrefix])
    </div>
</div>
