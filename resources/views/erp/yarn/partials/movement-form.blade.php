@php
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
    @include('erp.partials.erp-form-toolbar')

    <form class="space-y-3 p-3" action="{{ route('erp.yarn.screen.store', ['screen' => $screen['slug']]) }}" method="post">
        @csrf
        <div class="grid gap-2 border border-slate-400 bg-[#f4f4f4] p-2 md:grid-cols-2 lg:grid-cols-4">
            <label class="erp-field"><span class="erp-label">Trans #</span><input class="erp-input" value="Auto" readonly></label>
            <label class="erp-field"><span class="erp-label">Dated</span><input class="erp-input" type="date" name="trans_date" value="{{ old('trans_date', now()->toDateString()) }}" required></label>
            <label class="erp-field"><span class="erp-label">Voucher type</span><input class="erp-input" name="meta[voucher_type]" value="{{ old('meta.voucher_type', $defaultVoucherType) }}"></label>
            <label class="erp-field"><span class="erp-label">Reference</span><input class="erp-input" name="meta[ref_no]" value="{{ old('meta.ref_no') }}"></label>

            @if($contractMode === 'single')
                <label class="erp-field md:col-span-2">
                    <span class="erp-label">Contract Id</span>
                    <select class="erp-input" name="yarn_contract_id" required>
                        <option value="">Select contract</option>
                        @foreach($singleContracts as $contract)
                            <option value="{{ $contract->id }}" @selected((string) old('yarn_contract_id') === (string) $contract->id)>
                                {{ $contract->contract_no }} — {{ $contract->party?->name }} — {{ $contract->item?->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field md:col-span-2">
                    <span class="erp-label">Party</span>
                    <select class="erp-input" name="party_id">
                        <option value="">Use contract party</option>
                        @foreach(($parties ?? []) as $party)
                            <option value="{{ $party->id }}" @selected((string) old('party_id') === (string) $party->id)>{{ $party->code }} — {{ $party->name }}</option>
                        @endforeach
                    </select>
                </label>
            @elseif($contractMode === 'transfer')
                <label class="erp-field md:col-span-2">
                    <span class="erp-label">From Contract Id</span>
                    <select class="erp-input" name="from_yarn_contract_id" required>
                        <option value="">Select from contract</option>
                        @foreach(($contracts ?? []) as $contract)
                            <option value="{{ $contract->id }}" @selected((string) old('from_yarn_contract_id') === (string) $contract->id)>
                                {{ $contract->contract_no }} — {{ $contract->party?->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field md:col-span-2">
                    <span class="erp-label">To Contract Id</span>
                    <select class="erp-input" name="to_yarn_contract_id" required>
                        <option value="">Select to contract</option>
                        @foreach(($contracts ?? []) as $contract)
                            <option value="{{ $contract->id }}" @selected((string) old('to_yarn_contract_id') === (string) $contract->id)>
                                {{ $contract->contract_no }} — {{ $contract->party?->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
            @else
                <label class="erp-field md:col-span-2">
                    <span class="erp-label">Party</span>
                    <select class="erp-input" name="party_id">
                        <option value="">Select party</option>
                        @foreach(($parties ?? []) as $party)
                            <option value="{{ $party->id }}" @selected((string) old('party_id') === (string) $party->id)>{{ $party->code }} — {{ $party->name }}</option>
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
                            <option value="{{ $issue->id }}" @selected((string) old('source_transaction_id') === (string) $issue->id)>
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
                            <option value="{{ $godown->id }}" @selected((string) old('from_godown_id') === (string) $godown->id)>{{ $godown->code }} — {{ $godown->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="erp-field">
                    <span class="erp-label">To Godown</span>
                    <select class="erp-input" name="to_godown_id" required>
                        <option value="">Select to</option>
                        @foreach(($godowns ?? []) as $godown)
                            <option value="{{ $godown->id }}" @selected((string) old('to_godown_id') === (string) $godown->id)>{{ $godown->code }} — {{ $godown->name }}</option>
                        @endforeach
                    </select>
                </label>
            @else
                <label class="erp-field">
                    <span class="erp-label">Godown</span>
                    <select class="erp-input" name="from_godown_id">
                        <option value="">Use contract godown</option>
                        @foreach(($godowns ?? []) as $godown)
                            <option value="{{ $godown->id }}" @selected((string) old('from_godown_id') === (string) $godown->id)>{{ $godown->code }} — {{ $godown->name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            <label class="erp-field"><span class="erp-label">Yarn tag</span><input class="erp-input" name="meta[yarn_tag]" value="{{ old('meta.yarn_tag') }}"></label>
            <label class="erp-field md:col-span-2"><span class="erp-label">Remarks</span><input class="erp-input" name="remarks" value="{{ old('remarks') }}"></label>
        </div>

        <div class="text-[11px] font-semibold uppercase text-slate-600">{{ $lineLabel }}</div>
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
                <tbody>
                    @for($i = 0; $i < 6; $i++)
                        <tr>
                            <td class="border border-slate-300 p-1">
                                <select class="erp-input" name="lines[{{ $i }}][item_id]">
                                    <option value="">Select yarn</option>
                                    @foreach(($items ?? []) as $item)
                                        <option value="{{ $item->id }}">{{ $item->code }} — {{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="border border-slate-300 p-1">
                                <select class="erp-input" name="lines[{{ $i }}][meta][yarn_type]">
                                    <option value="">Type</option>
                                    <option value="WARP">WARP</option>
                                    <option value="WEFT">WEFT</option>
                                </select>
                            </td>
                            <td class="border border-slate-300 p-1"><input class="erp-input" name="lines[{{ $i }}][description]"></td>
                            <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][qty]"></td>
                            <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][meta][cones]"></td>
                            <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][weight_lbs]"></td>
                            <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][rate]"></td>
                            <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.0001" min="0" name="lines[{{ $i }}][meta][transfer_rate]"></td>
                            @if($showAdjustment)
                                <td class="border border-slate-300 p-1">
                                    <select class="erp-input" name="lines[{{ $i }}][meta][adjustment_type]">
                                        <option value="gain">Gain</option>
                                        <option value="shortage">Shortage</option>
                                    </select>
                                </td>
                            @endif
                            <td class="border border-slate-300 p-1"><input class="erp-input text-right" type="number" step="0.01" min="0" name="lines[{{ $i }}][amount]"></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-2 border border-slate-300 bg-[#f0f0f0] p-2">
            <button name="submit_action" value="save" class="rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-[12px] font-semibold hover:bg-white">Save</button>
            <button name="submit_action" value="post" class="rounded border border-slate-600 bg-slate-200 px-4 py-1.5 text-[12px] font-semibold hover:bg-white">Post voucher</button>
        </div>
    </form>

    @include('erp.yarn.partials.recent-transactions')
</div>
