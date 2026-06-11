<?php

namespace App\Http\Controllers\Erp;

use App\Http\Concerns\AuthorizesWeaving;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Item;
use App\Models\WeavingAccountSetting;
use App\Models\WeavingDepartment;
use App\Models\WeavingLoom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WeavingMasterDataController extends Controller
{
    use AuthorizesWeaving;

    public function show(Request $request): View
    {
        abort_unless($this->weavingAllowed('master-data', 'view'), 403);

        $tab = $request->query('tab', 'departments');
        if (! in_array($tab, ['departments', 'looms', 'store-items', 'account-settings'], true)) {
            $tab = 'departments';
        }

        return view('erp.weaving.master-data', [
            'activeModule' => 'weaving',
            'moduleKey' => 'weaving',
            'moduleLabel' => 'Weaving',
            'activeTab' => $tab,
            'permissionPrefix' => 'weaving.master-data',
            'pageTitle' => 'Weaving Master Data',
            'screen' => ['slug' => 'master-data', 'label' => 'Weaving Master Data', 'code' => 'WEAVSP_0004'],
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => 'Weaving', 'route' => 'erp.weaving.dashboard'],
                ['label' => 'Weaving Master Data'],
            ],
            'departments' => WeavingDepartment::query()->with('expenseAccount')->orderBy('code')->get(),
            'looms' => WeavingLoom::query()->orderBy('loom_no')->get(),
            'storeItems' => Item::query()->whereIn('module', ['store', 'shared'])->orderBy('code')->get(),
            'accountParties' => Account::query()->postable()->orderBy('code')->get(),
            'accountSettings' => WeavingAccountSetting::current()->load([
                'storeStockAccount', 'yarnStockAccount', 'greyStockAccount',
                'defaultExpenseAccount', 'sizingExpenseAccount',
                'fabricSalesAccount', 'fabricCogsAccount',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->weavingAllowed('master-data', 'create'), 403);

        $tab = $request->input('tab', 'departments');

        return match ($tab) {
            'looms' => $this->storeLooms($request),
            'store-items' => $this->storeItems($request),
            'account-settings' => $this->storeAccountSettings($request),
            default => $this->storeDepartments($request),
        };
    }

    private function subLedgerRule(): array
    {
        return [
            'nullable', 'integer',
            Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('level', 'sub_ledger')->where('is_active', true)),
        ];
    }

    private function storeDepartments(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'departments' => ['required', 'array'],
            'departments.*.id' => ['nullable', 'integer'],
            'departments.*.code' => ['nullable', 'string', 'max:20'],
            'departments.*.name' => ['nullable', 'string', 'max:120'],
            'departments.*.expense_account_id' => $this->subLedgerRule(),
            'departments.*.is_active' => ['nullable', 'boolean'],
        ]);

        foreach ($data['departments'] as $row) {
            if (empty($row['code']) && empty($row['name'])) {
                continue;
            }
            WeavingDepartment::query()->updateOrCreate(
                ['id' => $row['id'] ?? null],
                [
                    'code' => $row['code'] ?? ('D' . str_pad((string) WeavingDepartment::query()->count() + 1, 3, '0', STR_PAD_LEFT)),
                    'name' => $row['name'] ?? $row['code'],
                    'expense_account_id' => $row['expense_account_id'] ?? null,
                    'is_active' => ! empty($row['is_active']),
                ]
            );
        }

        return redirect()->route('erp.weaving.master-data', ['tab' => 'departments'])->with('status', 'Departments saved.');
    }

    private function storeLooms(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'looms' => ['required', 'array'],
            'looms.*.id' => ['nullable', 'integer'],
            'looms.*.loom_no' => ['nullable', 'string', 'max:40'],
            'looms.*.name' => ['nullable', 'string', 'max:120'],
            'looms.*.loom_type' => ['nullable', 'string', 'max:80'],
            'looms.*.is_active' => ['nullable', 'boolean'],
        ]);

        foreach ($data['looms'] as $row) {
            if (empty($row['loom_no'])) {
                continue;
            }
            WeavingLoom::query()->updateOrCreate(
                ['loom_no' => $row['loom_no']],
                [
                    'name' => $row['name'] ?? $row['loom_no'],
                    'loom_type' => $row['loom_type'] ?? null,
                    'is_active' => ! empty($row['is_active']),
                ]
            );
        }

        return redirect()->route('erp.weaving.master-data', ['tab' => 'looms'])->with('status', 'Looms saved.');
    }

    private function storeItems(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.code' => ['nullable', 'string', 'max:40'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
            'items.*.is_active' => ['nullable', 'boolean'],
        ]);

        foreach ($data['items'] as $row) {
            if (empty($row['code']) && empty($row['name'])) {
                continue;
            }
            Item::query()->updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'] ?? $row['code'],
                    'module' => 'store',
                    'unit' => $row['unit'] ?? 'PCS',
                    'is_active' => ! empty($row['is_active']),
                ]
            );
        }

        return redirect()->route('erp.weaving.master-data', ['tab' => 'store-items'])->with('status', 'Store items saved.');
    }

    private function storeAccountSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_stock_account_id' => $this->subLedgerRule(),
            'yarn_stock_account_id' => $this->subLedgerRule(),
            'grey_stock_account_id' => $this->subLedgerRule(),
            'default_expense_account_id' => $this->subLedgerRule(),
            'sizing_expense_account_id' => $this->subLedgerRule(),
            'fabric_sales_account_id' => $this->subLedgerRule(),
            'fabric_cogs_account_id' => $this->subLedgerRule(),
        ]);

        WeavingAccountSetting::current()->update($data);

        return redirect()->route('erp.weaving.master-data', ['tab' => 'account-settings'])->with('status', 'Account mapping saved.');
    }
}
