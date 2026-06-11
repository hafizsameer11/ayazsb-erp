@extends('layouts.erp')

@section('title', 'Weaving Master Data')

@section('content')
    @php
        $activeTab = $activeTab ?? 'departments';
        $settings = $accountSettings ?? \App\Models\WeavingAccountSetting::current();
        $blankDept = ['id' => '', 'code' => '', 'name' => '', 'expense_account_id' => '', 'is_active' => true];
        $deptRows = $departments->map(fn ($d) => [
            'id' => $d->id, 'code' => $d->code, 'name' => $d->name,
            'expense_account_id' => $d->expense_account_id, 'is_active' => $d->is_active,
        ])->all();
        while (count($deptRows) < 8) { $deptRows[] = $blankDept; }
        $blankLoom = ['id' => '', 'loom_no' => '', 'name' => '', 'loom_type' => '', 'is_active' => true];
        $loomRows = $looms->map(fn ($l) => ['id' => $l->id, 'loom_no' => $l->loom_no, 'name' => $l->name, 'loom_type' => $l->loom_type, 'is_active' => $l->is_active])->all();
        while (count($loomRows) < 8) { $loomRows[] = $blankLoom; }
        $blankItem = ['id' => '', 'code' => '', 'name' => '', 'unit' => 'PCS', 'is_active' => true];
        $itemRows = $storeItems->map(fn ($i) => ['id' => $i->id, 'code' => $i->code, 'name' => $i->name, 'unit' => $i->unit, 'is_active' => $i->is_active])->all();
        while (count($itemRows) < 8) { $itemRows[] = $blankItem; }
        $accountFields = [
            'store_stock_account_id' => 'Store Stock Account',
            'yarn_stock_account_id' => 'Yarn Stock Account',
            'grey_stock_account_id' => 'Grey / Fabric Stock Account',
            'default_expense_account_id' => 'Default Cost Center (CC) / Expense',
            'sizing_expense_account_id' => 'Sizing Expense Account',
            'fabric_sales_account_id' => 'Fabric Sales Account',
            'fabric_cogs_account_id' => 'Fabric COGS / Conversion Account',
        ];
    @endphp
    <div class="erp-panel border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">{{ $screen['code'] }} — WEAVING MASTER DATA</div>
        <nav class="flex flex-wrap gap-1 border-b border-slate-300 px-2 pt-2 text-[11px]">
            @foreach (['departments' => 'Departments', 'looms' => 'Looms', 'store-items' => 'Store Items', 'account-settings' => 'Account Mapping'] as $key => $label)
                <a href="{{ route('erp.weaving.master-data', ['tab' => $key]) }}" class="-mb-px border border-b-0 border-slate-400 px-3 py-1.5 font-semibold {{ $activeTab === $key ? 'bg-white text-slate-900' : 'bg-[#d8d8d8] text-slate-600 hover:bg-[#ececec]' }}">{{ $label }}</a>
            @endforeach
        </nav>
        <form class="p-3" method="post" action="{{ route('erp.weaving.master-data.store') }}">
            @csrf
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            @if ($activeTab === 'account-settings')
                <p class="mb-3 text-[11px] text-slate-600">Map weaving stock and control accounts to sub-ledgers from Chart of Accounts. Party accounts are picked on each transaction screen.</p>
                <div class="grid gap-3 md:grid-cols-2">
                    @foreach ($accountFields as $field => $label)
                        <label class="erp-field"><span class="erp-label">{{ $label }}</span>
                            @include('erp.grey.partials.code-name-pair', [
                                'selectName' => $field,
                                'selectedId' => old($field, $settings->{$field}),
                                'options' => $accountParties,
                                'targetId' => 'weaving-gl-' . $field,
                            ])
                        </label>
                    @endforeach
                </div>
            @elseif ($activeTab === 'departments')
                <table class="w-full border-collapse text-[11px]" data-erp-detail-lines>
                    <thead class="bg-[#d8d8d8]"><tr><th class="border border-slate-400 px-1 py-1">Code</th><th class="border border-slate-400 px-1 py-1">Name</th><th class="border border-slate-400 px-1 py-1">Default CC Account</th><th class="border border-slate-400 px-1 py-1">Active</th></tr></thead>
                    <tbody>
                        @foreach ($deptRows as $i => $row)
                            <tr data-erp-detail-line>
                                @if ($row['id'])<input type="hidden" name="departments[{{ $i }}][id]" value="{{ $row['id'] }}">@endif
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="departments[{{ $i }}][code]" value="{{ $row['code'] }}"></td>
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="departments[{{ $i }}][name]" value="{{ $row['name'] }}"></td>
                                <td class="border border-slate-300 p-0.5">
                                    <select class="erp-input w-full" name="departments[{{ $i }}][expense_account_id]">
                                        <option value="">—</option>
                                        @foreach ($accountParties as $acc)
                                            <option value="{{ $acc->id }}" @selected($row['expense_account_id'] == $acc->id)>{{ $acc->code }} — {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="border border-slate-300 px-2 text-center"><input type="checkbox" name="departments[{{ $i }}][is_active]" value="1" @checked($row['is_active'])></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @elseif ($activeTab === 'looms')
                <table class="w-full border-collapse text-[11px]" data-erp-detail-lines>
                    <thead class="bg-[#d8d8d8]"><tr><th class="border border-slate-400 px-1 py-1">Loom #</th><th class="border border-slate-400 px-1 py-1">Name</th><th class="border border-slate-400 px-1 py-1">Type</th><th class="border border-slate-400 px-1 py-1">Active</th></tr></thead>
                    <tbody>
                        @foreach ($loomRows as $i => $row)
                            <tr data-erp-detail-line>
                                @if ($row['id'])<input type="hidden" name="looms[{{ $i }}][id]" value="{{ $row['id'] }}">@endif
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="looms[{{ $i }}][loom_no]" value="{{ $row['loom_no'] }}"></td>
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="looms[{{ $i }}][name]" value="{{ $row['name'] }}"></td>
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="looms[{{ $i }}][loom_type]" value="{{ $row['loom_type'] }}"></td>
                                <td class="border border-slate-300 px-2 text-center"><input type="checkbox" name="looms[{{ $i }}][is_active]" value="1" @checked($row['is_active'])></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <table class="w-full border-collapse text-[11px]" data-erp-detail-lines>
                    <thead class="bg-[#d8d8d8]"><tr><th class="border border-slate-400 px-1 py-1">Code</th><th class="border border-slate-400 px-1 py-1">Name</th><th class="border border-slate-400 px-1 py-1">Unit</th><th class="border border-slate-400 px-1 py-1">Active</th></tr></thead>
                    <tbody>
                        @foreach ($itemRows as $i => $row)
                            <tr data-erp-detail-line>
                                @if ($row['id'])<input type="hidden" name="items[{{ $i }}][id]" value="{{ $row['id'] }}">@endif
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="items[{{ $i }}][code]" value="{{ $row['code'] }}"></td>
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="items[{{ $i }}][name]" value="{{ $row['name'] }}"></td>
                                <td class="border border-slate-300 p-0.5"><input class="erp-input w-full" name="items[{{ $i }}][unit]" value="{{ $row['unit'] }}"></td>
                                <td class="border border-slate-300 px-2 text-center"><input type="checkbox" name="items[{{ $i }}][is_active]" value="1" @checked($row['is_active'])></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
            <button type="submit" class="mt-2 rounded border border-slate-600 bg-slate-200 px-4 py-1 text-[12px] font-semibold hover:bg-white">Save</button>
        </form>
    </div>
@endsection
