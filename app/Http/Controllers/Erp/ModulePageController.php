<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionLine;
use App\Models\Godown;
use App\Models\Account;
use App\Models\Item;
use App\Models\Party;
use App\Models\YarnContract;
use App\Services\PostingService;
use App\Services\VoucherNumberService;
use App\Services\YarnContractBalanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ModulePageController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const YARN_DEDICATED_SCREENS = [
        'purchase-contract' => 'purchase-contract',
        'purchase-contract-wise' => 'purchase-contract-wise',
        'sale-contract' => 'sale-contract',
        'sale-contract-wise' => 'sale-contract-wise',
        'issuance' => 'issuance',
        'issuance-return' => 'issuance-return',
        'issuance-transfer' => 'issuance-transfer',
        'godown-transfer' => 'godown-transfer',
        'gain-shortage' => 'gain-shortage',
    ];

    /**
     * @var array<string, array<string, mixed>>
     */
    private const MODULES = [
        'yarn' => [
            'label' => 'Yarn Management',
            'groups' => [
                'Transactions' => [
                    ['slug' => 'purchase-contract', 'label' => 'Yarn Purchase Contract', 'code' => 'YARNSP_0004'],
                    ['slug' => 'purchase-contract-wise', 'label' => 'Yarn Purchase (Contract Wise)', 'code' => 'YARNSP_0006'],
                    ['slug' => 'purchase-without-contract', 'label' => 'Yarn Purchase (Without Contract)', 'code' => 'YARNSP_0007'],
                    ['slug' => 'sale-contract', 'label' => 'Yarn Sale Contract', 'code' => 'YARNSP_0008'],
                    ['slug' => 'sale-contract-wise', 'label' => 'Yarn Sale (Contract Wise)', 'code' => 'YARNSP_0009'],
                    ['slug' => 'sale-without-contract', 'label' => 'Yarn Sale (Without Contract)', 'code' => 'YARNSP_0019'],
                    ['slug' => 'issuance', 'label' => 'Yarn Issuance', 'code' => 'YARNSP_0010'],
                    ['slug' => 'receipt-processed', 'label' => 'Yarn Receipt (Processed)', 'code' => 'YARNSP_0011'],
                    ['slug' => 'receipt-processed-auto', 'label' => 'Yarn Receipt (Processed) Auto Consumption', 'code' => 'YARNSP_0025'],
                    ['slug' => 'issuance-return', 'label' => 'Yarn Issuance Return', 'code' => 'YARNSP_0012'],
                    ['slug' => 'issuance-transfer', 'label' => 'Yarn Issuance Transfer', 'code' => 'YARNSP_0013'],
                    ['slug' => 'godown-transfer', 'label' => 'Yarn Godown Transfer', 'code' => 'YARNSP_0014'],
                    ['slug' => 'loom-transfer', 'label' => 'Loom Yarn Transfer (For Warping)', 'code' => 'YARNSP_0015'],
                    ['slug' => 'gain-shortage', 'label' => 'Yarn (Gain / Shortage)', 'code' => 'YARNSP_0026'],
                ],
                'Setup' => [
                    ['slug' => 'master-yarn', 'label' => 'Yarn Master Data (Yarn Master)', 'code' => 'YARNSP_0003'],
                    ['slug' => 'master-items', 'label' => 'Yarn Master Data (Yarn Items)', 'code' => 'YARNSP_0003'],
                    ['slug' => 'master-godowns', 'label' => 'Yarn Master Data (Godown)', 'code' => 'YARNSP_0003'],
                    ['slug' => 'opening', 'label' => 'Yarn Opening', 'code' => 'YARNSP_0016'],
                ],
            ],
        ],
        'grey' => [
            'label' => 'Grey Management',
            'groups' => [
                'Grey Transactions' => [
                    ['slug' => 'purchase', 'label' => 'Grey Purchase', 'code' => 'GREYSP_0006'],
                    ['slug' => 'sale', 'label' => 'Grey Sale', 'code' => 'GREYSP_0007'],
                ],
                'Conversion Transactions' => [
                    ['slug' => 'conversion-contract', 'label' => 'Conversion Contract', 'code' => 'GREYSP_0011'],
                    ['slug' => 'conversion-inward', 'label' => 'Grey Conversion Inward', 'code' => 'GREYSP_0012'],
                ],
                'Setup' => [
                    ['slug' => 'master-grey', 'label' => 'Grey Master Data (Grey Master)', 'code' => 'GREYSP_0003'],
                    ['slug' => 'master-godowns', 'label' => 'Grey Master Data (Grey Godowns)', 'code' => 'GREYSP_0003'],
                    ['slug' => 'opening', 'label' => 'Grey Opening', 'code' => 'GREYSP_0013'],
                ],
            ],
        ],
        'reports' => [
            'label' => 'Reports',
            'groups' => [
                'Accounts & Finance Reports' => [
                    ['slug' => 'accounts', 'label' => 'Accounts & Finance Reports Panel', 'code' => 'REPTPN_0002'],
                ],
                'Yarn Management Reports' => [
                    ['slug' => 'yarn', 'label' => 'Yarn Management Reports Panel', 'code' => 'REPTPN_0002'],
                ],
                'Grey Management Reports' => [
                    ['slug' => 'grey', 'label' => 'Grey Management Reports Panel', 'code' => 'REPTPN_0002'],
                ],
            ],
        ],
    ];

    public function dashboard(string $module): View
    {
        $definition = self::MODULES[$module] ?? abort(404);
        abort_unless($this->allowed("{$module}.dashboard.view"), 403);

        return view('erp.module-dashboard', [
            'activeModule' => $module,
            'moduleKey' => $module,
            'moduleLabel' => $definition['label'],
            'groups' => $definition['groups'],
            'permissionPrefix' => "{$module}.dashboard",
            'pageTitle' => $definition['label'],
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => $definition['label']],
            ],
        ]);
    }

    public function screen(Request $request, string $screen): View
    {
        $module = (string) $request->route('module');
        $definition = self::MODULES[$module] ?? abort(404);
        $screenMeta = $this->findScreen($definition['groups'], $screen);

        abort_if($screenMeta === null, 404);
        abort_unless($this->allowed("{$module}.{$screenMeta['slug']}.view"), 403);

        $contracts = $module === 'yarn'
            ? YarnContract::query()
                ->with(['account', 'party', 'item', 'godown'])
                ->orderByDesc('contract_date')
                ->orderBy('contract_no')
                ->get()
            : collect();
        $accountParties = $module === 'yarn'
            ? Account::query()->postable()->orderBy('code')->get()
            : collect();

        $viewData = [
            'activeModule' => $module,
            'moduleKey' => $module,
            'moduleLabel' => $definition['label'],
            'screen' => $screenMeta,
            'permissionPrefix' => "{$module}.{$screenMeta['slug']}",
            'pageTitle' => $screenMeta['label'],
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => $definition['label'], 'route' => 'erp.' . $module . '.dashboard'],
                ['label' => $screenMeta['label']],
            ],
            'items' => Item::query()->whereIn('module', [$module, 'shared'])->orderBy('code')->get(),
            'parties' => Party::query()->orderBy('name')->get(),
            'accountParties' => $accountParties,
            'godowns' => Godown::query()->whereIn('module', [$module, 'shared'])->orderBy('code')->get(),
            'contracts' => $contracts,
            'purchaseContracts' => $contracts->where('direction', 'purchase')->values(),
            'saleContracts' => $contracts->where('direction', 'sale')->values(),
            'issueTransactions' => $module === 'yarn'
                ? InventoryTransaction::query()
                    ->where('module', 'yarn')
                    ->where('screen_slug', 'issuance')
                    ->with('yarnContract.account')
                    ->latest()
                    ->limit(50)
                    ->get()
                : collect(),
            'recentTransactions' => InventoryTransaction::query()
                ->where('module', $module)
                ->where('screen_slug', $screenMeta['slug'])
                ->with(['account', 'party', 'yarnContract.account', 'fromYarnContract.account', 'toYarnContract.account'])
                ->latest()
                ->limit(20)
                ->get(),
        ];

        if ($module === 'yarn' && isset(self::YARN_DEDICATED_SCREENS[$screenMeta['slug']])) {
            return view('erp.yarn.' . self::YARN_DEDICATED_SCREENS[$screenMeta['slug']], $viewData);
        }

        return view('erp.module-screen', $viewData);
    }

    public function storeScreenData(Request $request, string $screen, VoucherNumberService $numberService, YarnContractBalanceService $balanceService): RedirectResponse
    {
        $module = (string) $request->route('module');
        $definition = self::MODULES[$module] ?? abort(404);
        $screenMeta = $this->findScreen($definition['groups'], $screen);
        abort_if($screenMeta === null, 404);
        abort_unless($this->allowed("{$module}.{$screenMeta['slug']}.create"), 403);

        if ($module === 'yarn' && in_array($screenMeta['slug'], ['purchase-contract', 'sale-contract'], true)) {
            return $this->storeYarnContract($request, $screenMeta['slug']);
        }

        $data = $request->validate([
            'trans_date' => ['required', 'date'],
            'party_id' => ['nullable', 'integer', 'exists:parties,id'],
            'account_id' => ['nullable', 'integer', Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('level', 'sub_ledger')->where('is_active', true))],
            'from_account_id' => ['nullable', 'integer', Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('level', 'sub_ledger')->where('is_active', true))],
            'to_account_id' => ['nullable', 'integer', Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('level', 'sub_ledger')->where('is_active', true))],
            'yarn_contract_id' => ['nullable', 'integer', 'exists:yarn_contracts,id'],
            'from_yarn_contract_id' => ['nullable', 'integer', 'exists:yarn_contracts,id'],
            'to_yarn_contract_id' => ['nullable', 'integer', 'exists:yarn_contracts,id'],
            'source_transaction_id' => ['nullable', 'integer', 'exists:inventory_transactions,id'],
            'from_godown_id' => ['nullable', 'integer', 'exists:godowns,id'],
            'to_godown_id' => ['nullable', 'integer', 'exists:godowns,id'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'submit_action' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['nullable', 'integer', 'exists:items,id'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.qty' => ['nullable', 'numeric', 'min:0'],
            'lines.*.unit' => ['nullable', 'string', 'max:20'],
            'lines.*.weight_lbs' => ['nullable', 'numeric', 'min:0'],
            'lines.*.rate' => ['nullable', 'numeric', 'min:0'],
            'lines.*.amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.meta' => ['nullable', 'array'],
        ]);

        if ($module === 'yarn') {
            $this->validateYarnTransaction($screenMeta['slug'], $data, $balanceService);
        }

        $postOnSubmit = ($data['submit_action'] ?? null) === 'post';
        if ($postOnSubmit) {
            abort_unless($this->allowed("{$module}.{$screenMeta['slug']}.post"), 403);
        }

        $transaction = DB::transaction(function () use ($data, $module, $screenMeta, $numberService, $postOnSubmit) {
            $transNo = $numberService->nextTransaction($module, $screenMeta['slug']);
            $primaryContract = isset($data['yarn_contract_id'])
                ? YarnContract::query()->find($data['yarn_contract_id'])
                : null;
            $fromContract = isset($data['from_yarn_contract_id'])
                ? YarnContract::query()->find($data['from_yarn_contract_id'])
                : null;
            $transaction = InventoryTransaction::query()->create([
                'module' => $module,
                'screen_slug' => $screenMeta['slug'],
                'trans_no' => $transNo,
                'trans_date' => $data['trans_date'],
                'party_id' => $data['party_id'] ?? $primaryContract?->party_id ?? $fromContract?->party_id,
                'account_id' => $data['account_id'] ?? $primaryContract?->account_id ?? $fromContract?->account_id,
                'from_account_id' => $data['from_account_id'] ?? $fromContract?->account_id,
                'to_account_id' => $data['to_account_id'] ?? (isset($data['to_yarn_contract_id']) ? YarnContract::query()->find($data['to_yarn_contract_id'])?->account_id : null),
                'yarn_contract_id' => $data['yarn_contract_id'] ?? null,
                'from_yarn_contract_id' => $data['from_yarn_contract_id'] ?? null,
                'to_yarn_contract_id' => $data['to_yarn_contract_id'] ?? null,
                'source_transaction_id' => $data['source_transaction_id'] ?? null,
                'from_godown_id' => $data['from_godown_id'] ?? $primaryContract?->godown_id,
                'to_godown_id' => $data['to_godown_id'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => $postOnSubmit ? 'posted' : 'draft',
                'meta' => $data['meta'] ?? [],
                'created_by' => Auth::id(),
            ]);

            $totalQty = 0.0;
            $totalAmount = 0.0;
            foreach ($data['lines'] as $line) {
                if (empty($line['item_id']) && empty($line['description']) && empty($line['qty']) && empty($line['weight_lbs']) && empty($line['amount'])) {
                    continue;
                }
                $qty = (float) ($line['qty'] ?? 0);
                $weight = (float) ($line['weight_lbs'] ?? 0);
                $rate = (float) ($line['rate'] ?? 0);
                $amountBasis = $weight > 0 ? $weight : $qty;
                $amount = (float) ($line['amount'] ?? ($amountBasis * $rate));
                $totalQty += $qty > 0 ? $qty : $weight;
                $totalAmount += $amount;

                InventoryTransactionLine::query()->create([
                    'inventory_transaction_id' => $transaction->id,
                    'item_id' => $line['item_id'] ?? null,
                    'description' => $line['description'] ?? null,
                    'qty' => $qty,
                    'unit' => $line['unit'] ?? null,
                    'weight_lbs' => $weight,
                    'rate' => $rate,
                    'amount' => $amount,
                    'meta' => $line['meta'] ?? [],
                ]);
            }

            $transaction->update([
                'total_qty' => $totalQty,
                'total_amount' => $totalAmount,
            ]);

            return $transaction;
        });

        return back()->with('status', "Transaction {$transaction->trans_no} " . ($postOnSubmit ? 'posted.' : 'saved.'));
    }

    private function storeYarnContract(Request $request, string $screen): RedirectResponse
    {
        $direction = $screen === 'sale-contract' ? 'sale' : 'purchase';

        $data = $request->validate([
            'contract_no' => ['required', 'string', 'max:80'],
            'contract_date' => ['required', 'date'],
            'contract_type' => ['required', 'string', Rule::in(['BY RATE', 'EMANI'])],
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('level', 'sub_ledger')->where('is_active', true))],
            'party_id' => ['nullable', 'integer', 'exists:parties,id'],
            'item_id' => ['nullable', 'integer', 'exists:items,id'],
            'godown_id' => ['nullable', 'integer', 'exists:godowns,id'],
            'yarn_tag' => ['nullable', 'string', 'max:80'],
            'condition' => ['nullable', 'string', 'max:80'],
            'unit' => ['nullable', 'string', 'max:20'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'weight_lbs' => ['nullable', 'numeric', 'min:0'],
            'packing_size' => ['nullable', 'numeric', 'min:0'],
            'packing_weight' => ['nullable', 'numeric', 'min:0'],
            'rate' => ['nullable', 'numeric', 'min:0'],
            'sale_rate' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['open', 'closed', 'hold'])],
            'remarks' => ['nullable', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
        ]);
        $fallbackPartyId = $data['party_id'] ?? Party::query()->value('id');

        $contract = YarnContract::query()->updateOrCreate(
            ['contract_no' => $data['contract_no'], 'direction' => $direction],
            [
                'contract_type' => $data['contract_type'],
                'contract_date' => $data['contract_date'],
                'party_id' => $fallbackPartyId,
                'account_id' => $data['account_id'],
                'item_id' => $data['item_id'] ?? null,
                'godown_id' => $data['godown_id'] ?? null,
                'yarn_tag' => $data['yarn_tag'] ?? null,
                'condition' => $data['condition'] ?? null,
                'unit' => $data['unit'] ?? 'LBS',
                'quantity' => $data['quantity'] ?? 0,
                'weight_lbs' => $data['weight_lbs'] ?? 0,
                'packing_size' => $data['packing_size'] ?? 0,
                'packing_weight' => $data['packing_weight'] ?? 0,
                'rate' => $data['rate'] ?? 0,
                'sale_rate' => $data['sale_rate'] ?? null,
                'status' => $data['status'] ?? 'open',
                'remarks' => $data['remarks'] ?? null,
                'meta' => $data['meta'] ?? [],
                'created_by' => Auth::id(),
            ]
        );

        return back()->with('status', "Yarn contract {$contract->contract_no} saved.");
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateYarnTransaction(string $screen, array $data, YarnContractBalanceService $balanceService): void
    {
        if (in_array($screen, ['purchase-contract-wise', 'sale-contract-wise', 'issuance', 'issuance-return', 'gain-shortage'], true)
            && empty($data['yarn_contract_id'])) {
            throw ValidationException::withMessages(['yarn_contract_id' => 'Select a yarn contract.']);
        }

        if ($screen === 'issuance-transfer') {
            if (empty($data['from_yarn_contract_id']) || empty($data['to_yarn_contract_id'])) {
                throw ValidationException::withMessages(['from_yarn_contract_id' => 'Select both from and to contracts.']);
            }

            if ((int) $data['from_yarn_contract_id'] === (int) $data['to_yarn_contract_id']) {
                throw ValidationException::withMessages(['to_yarn_contract_id' => 'From and to contracts must be different.']);
            }
        }

        if ($screen === 'godown-transfer') {
            if (empty($data['from_godown_id']) || empty($data['to_godown_id'])) {
                throw ValidationException::withMessages(['from_godown_id' => 'Select both from and to godowns.']);
            }

            if ((int) $data['from_godown_id'] === (int) $data['to_godown_id']) {
                throw ValidationException::withMessages(['to_godown_id' => 'From and to godowns must be different.']);
            }
        }

        $lines = $data['lines'] ?? [];
        if (! is_array($lines)) {
            return;
        }

        if (in_array($screen, ['issuance', 'sale-contract-wise'], true)) {
            $this->ensureAvailableContractWeight((int) $data['yarn_contract_id'], $balanceService->transactionWeight($lines), $balanceService);
        }

        if ($screen === 'issuance-transfer') {
            $this->ensureAvailableContractWeight((int) $data['from_yarn_contract_id'], $balanceService->transactionWeight($lines), $balanceService);
        }

        if ($screen === 'gain-shortage') {
            $shortageWeight = array_reduce($lines, static function (float $total, array $line): float {
                if (strtolower((string) data_get($line, 'meta.adjustment_type')) !== 'shortage') {
                    return $total;
                }

                $weight = (float) ($line['weight_lbs'] ?? 0);

                return $total + ($weight > 0 ? $weight : (float) ($line['qty'] ?? 0));
            }, 0.0);

            if ($shortageWeight > 0) {
                $this->ensureAvailableContractWeight((int) $data['yarn_contract_id'], $shortageWeight, $balanceService);
            }
        }
    }

    private function ensureAvailableContractWeight(int $contractId, float $requiredWeight, YarnContractBalanceService $balanceService): void
    {
        if ($requiredWeight <= 0) {
            return;
        }

        $contract = YarnContract::query()->findOrFail($contractId);
        $available = $balanceService->availableWeight($contract);

        if ($requiredWeight > ($available + 0.0001)) {
            throw ValidationException::withMessages([
                'lines' => "Requested yarn weight exceeds available contract balance. Available: {$available} LBS.",
            ]);
        }
    }

    public function postScreenData(Request $request, string $screen, InventoryTransaction $transaction, PostingService $postingService): RedirectResponse
    {
        $module = (string) $request->route('module');
        abort_unless($this->allowed("{$module}.{$screen}.post"), 403);
        abort_unless($transaction->module === $module && $transaction->screen_slug === $screen, 404);
        if ($transaction->status === 'posted') {
            return back()->with('status', "Transaction {$transaction->trans_no} already posted.");
        }
        $postingService->postInventoryTransaction($transaction);

        return back()->with('status', "Transaction {$transaction->trans_no} posted.");
    }

    public function printScreenData(Request $request, string $screen, InventoryTransaction $transaction): View
    {
        $module = (string) $request->route('module');
        abort_unless($this->allowed("{$module}.{$screen}.print"), 403);
        abort_unless($transaction->module === $module && $transaction->screen_slug === $screen, 404);

        return view('erp.module-transaction-print', [
            'activeModule' => $module,
            'pageTitle' => 'Print Transaction',
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => ucfirst($module), 'route' => "erp.{$module}.dashboard"],
                ['label' => 'Print'],
            ],
            'transaction' => $transaction->load('lines.item', 'account', 'fromAccount', 'toAccount', 'party', 'yarnContract.account', 'fromYarnContract.account', 'toYarnContract.account', 'fromGodown', 'toGodown'),
        ]);
    }

    /**
     * @param array<string, array<int, array<string, string>>> $groups
     * @return array<string, string>|null
     */
    private function findScreen(array $groups, string $slug): ?array
    {
        foreach ($groups as $items) {
            foreach ($items as $item) {
                if ($item['slug'] === $slug) {
                    return $item;
                }
            }
        }

        return null;
    }

    private function allowed(string $permission): bool
    {
        $user = Auth::user();

        return $user instanceof \App\Models\User && $user->hasPermission($permission);
    }
}

