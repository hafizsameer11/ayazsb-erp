@php
    $direction = $direction ?? 'purchase';
    $contractsForDirection = ($contracts ?? collect())->where('direction', $direction)->values();
@endphp

<div class="erp-panel border border-slate-500 bg-white shadow-md">
    <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
        {{ $screen['code'] }} — {{ $screen['label'] }}
    </div>
    <form class="space-y-3 p-3" action="{{ route('erp.yarn.screen.store', ['screen' => $screen['slug']]) }}" method="post">
        @csrf
        <div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 md:grid-cols-2 lg:grid-cols-4">
            <label class="erp-field"><span class="erp-label">Contract #</span><input class="erp-input" name="contract_no" value="{{ old('contract_no') }}" required autocomplete="off"></label>
            <label class="erp-field"><span class="erp-label">Dated</span><x-erp-date-input name="contract_date" :value="old('contract_date', $contract->contract_date ?? null)" :required="true" /></label>
            <label class="erp-field">
                <span class="erp-label">Contract type</span>
                <select class="erp-input" name="contract_type" required>
                    @foreach(['BY RATE', 'EMANI'] as $type)
                        <option value="{{ $type }}" @selected(old('contract_type', 'BY RATE') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field">
                <span class="erp-label">Status</span>
                <select class="erp-input" name="status">
                    @foreach(['open' => 'Open', 'hold' => 'Hold', 'closed' => 'Closed'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'open') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="erp-field md:col-span-2">
                <span class="erp-label">{{ $direction === 'sale' ? 'Customer / Contractee account' : 'Supplier / Party account' }}</span>
                <select class="erp-input" name="account_id" required>
                    <option value="">Select account</option>
                    @foreach(($accountParties ?? []) as $account)
                        <option value="{{ $account->id }}" @selected((string) old('account_id') === (string) $account->id)>{{ $account->code }} — {{ $account->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field md:col-span-2">
                <span class="erp-label">Yarn item</span>
                <select class="erp-input" name="item_id">
                    <option value="">Select yarn</option>
                    @foreach(($items ?? []) as $item)
                        <option value="{{ $item->id }}" @selected((string) old('item_id') === (string) $item->id)>{{ $item->code }} — {{ $item->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field"><span class="erp-label">Godown</span>
                <select class="erp-input" name="godown_id">
                    <option value="">Select godown</option>
                    @foreach(($godowns ?? []) as $godown)
                        <option value="{{ $godown->id }}" @selected((string) old('godown_id') === (string) $godown->id)>{{ $godown->code }} — {{ $godown->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="erp-field"><span class="erp-label">Yarn tag</span><input class="erp-input" name="yarn_tag" value="{{ old('yarn_tag') }}"></label>
            <label class="erp-field"><span class="erp-label">Condition</span><input class="erp-input" name="condition" value="{{ old('condition', 'GOOD') }}"></label>
            <label class="erp-field"><span class="erp-label">Unit</span><input class="erp-input" name="unit" value="{{ old('unit', 'LBS') }}"></label>
            <label class="erp-field"><span class="erp-label">No. bags / qty</span><input class="erp-input text-right" type="number" step="0.0001" min="0" name="quantity" value="{{ old('quantity', '0') }}"></label>
            <label class="erp-field"><span class="erp-label">Total weight (LBS)</span><input class="erp-input text-right" type="number" step="0.0001" min="0" name="weight_lbs" value="{{ old('weight_lbs', '0') }}"></label>
            <label class="erp-field"><span class="erp-label">Packing size</span><input class="erp-input text-right" type="number" step="0.0001" min="0" name="packing_size" value="{{ old('packing_size', '0') }}"></label>
            <label class="erp-field"><span class="erp-label">Packing weight</span><input class="erp-input text-right" type="number" step="0.0001" min="0" name="packing_weight" value="{{ old('packing_weight', '0') }}"></label>
            <label class="erp-field"><span class="erp-label">Rate</span><input class="erp-input text-right" type="number" step="0.0001" min="0" name="rate" value="{{ old('rate', '0') }}"></label>
            <label class="erp-field"><span class="erp-label">Sale / transfer rate</span><input class="erp-input text-right" type="number" step="0.0001" min="0" name="sale_rate" value="{{ old('sale_rate') }}"></label>
            <label class="erp-field md:col-span-2"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks') }}"></label>
        </div>

        <div class="flex gap-2 border border-slate-300 bg-[#f0f0f0] p-2">
            <button class="rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-[12px] font-semibold hover:bg-white">Save contract</button>
        </div>
    </form>

    <div class="border-t border-slate-300 p-3">
        <div class="mb-2 text-[11px] font-semibold uppercase text-slate-600">Saved {{ $direction }} contracts</div>
        <div class="overflow-x-auto border border-slate-400">
            <table class="w-full min-w-[900px] border-collapse text-[12px]">
                <thead>
                    <tr class="bg-[#d8d8d8]">
                        <th class="border border-slate-400 px-1 py-1">Contract</th>
                        <th class="border border-slate-400 px-1 py-1">Date</th>
                        <th class="border border-slate-400 px-1 py-1">Type</th>
                        <th class="border border-slate-400 px-1 py-1">Account</th>
                        <th class="border border-slate-400 px-1 py-1">Yarn</th>
                        <th class="border border-slate-400 px-1 py-1 text-right">Weight</th>
                        <th class="border border-slate-400 px-1 py-1 text-right">Rate</th>
                        <th class="border border-slate-400 px-1 py-1">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contractsForDirection as $contract)
                        <tr>
                            <td class="border border-slate-300 px-1 py-1 font-mono">{{ $contract->contract_no }}</td>
                            <td class="border border-slate-300 px-1 py-1">{{ \App\Support\ErpDate::display($contract->contract_date) }}</td>
                            <td class="border border-slate-300 px-1 py-1">{{ $contract->contract_type }}</td>
                            <td class="border border-slate-300 px-1 py-1">{{ $contract->account?->code }} — {{ $contract->account?->name }}</td>
                            <td class="border border-slate-300 px-1 py-1">{{ $contract->item?->code }} — {{ $contract->item?->name }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $contract->weight_lbs, 2) }}</td>
                            <td class="border border-slate-300 px-1 py-1 text-right">{{ number_format((float) $contract->rate, 2) }}</td>
                            <td class="border border-slate-300 px-1 py-1">{{ strtoupper($contract->status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="border border-slate-300 px-2 py-2 text-slate-500">No contracts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
