<?php

namespace App\Http\Controllers\Erp;

use App\Http\Concerns\AuthorizesAdminDelete;
use App\Http\Concerns\NormalizesErpDates;
use App\Http\Controllers\Controller;
use App\Rules\ErpDate;
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
use App\Services\YarnContractCalculationService;
use App\Support\RecordHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ModulePageController extends Controller
{
    use AuthorizesAdminDelete;
    use NormalizesErpDates;

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
                    ['slug' => 'master-data', 'label' => 'Yarn Master Data', 'code' => 'YARNSP_0003'],
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

    public function screen(Request $request, string $screen): View|RedirectResponse
    {
        $module = (string) $request->route('module');

        if ($module === 'yarn') {
            $legacyMasterTab = match ($screen) {
                'master-yarn' => 'master',
                'master-items' => 'items',
                'master-godowns' => 'godowns',
                default => null,
            };
            if ($legacyMasterTab !== null) {
                return redirect()->route('erp.yarn.master-data', ['tab' => $legacyMasterTab]);
            }
        }

        $definition = self::MODULES[$module] ?? abort(404);
        $screenMeta = $this->findScreen($definition['groups'], $screen);

        abort_if($screenMeta === null, 404);
        abort_unless($this->allowed("{$module}.{$screenMeta['slug']}.view"), 403);

        $contracts = $module === 'yarn'
            ? YarnContract::query()
                ->with(['account', 'party', 'item', 'godown', 'broker'])
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
            ...RecordHistory::buildForDay(
                $request,
                InventoryTransaction::query()
                    ->where('module', $module)
                    ->where('screen_slug', $screenMeta['slug'])
                    ->with(['account', 'party', 'yarnContract.account', 'fromYarnContract.account', 'toYarnContract.account', 'lines'])
                    ->orderByDesc('trans_date')
                    ->orderByDesc('id'),
                'trans_date',
                'erp.' . $module . '.screen',
                ['screen' => $screenMeta['slug']],
            ),
        ];

        if ($module === 'yarn' && in_array($screenMeta['slug'], ['purchase-contract', 'sale-contract'], true)) {
            $contractDirection = $screenMeta['slug'] === 'sale-contract' ? 'sale' : 'purchase';
            $viewData = array_merge($viewData, RecordHistory::buildForDay(
                $request,
                YarnContract::query()
                    ->where('direction', $contractDirection)
                    ->with(['account', 'item', 'godown', 'broker'])
                    ->orderByDesc('contract_date')
                    ->orderByDesc('id'),
                'contract_date',
                'erp.yarn.screen',
                ['screen' => $screenMeta['slug']],
            ));
        }

        $viewData['editingTransaction'] = $this->resolveEditingTransaction($request, $module, $screenMeta);
        $viewData['editingContract'] = $this->resolveEditingContract($request, $module, $screenMeta);

        if ($viewData['editingContract'] instanceof YarnContract) {
            $viewData['editingContract']->load(['item', 'broker', 'account']);
        }

        if ($module === 'yarn' && isset(self::YARN_DEDICATED_SCREENS[$screenMeta['slug']])) {
            return view('erp.yarn.' . self::YARN_DEDICATED_SCREENS[$screenMeta['slug']], $viewData);
        }

        return view('erp.module-screen', $viewData);
    }

    public function storeScreenData(
        Request $request,
        string $screen,
        VoucherNumberService $numberService,
        YarnContractBalanceService $balanceService,
        YarnContractCalculationService $contractCalculator,
    ): RedirectResponse|JsonResponse {
        $module = (string) $request->route('module');
        $definition = self::MODULES[$module] ?? abort(404);
        $screenMeta = $this->findScreen($definition['groups'], $screen);
        abort_if($screenMeta === null, 404);
        abort_unless($this->allowed("{$module}.{$screenMeta['slug']}.create"), 403);

        if ($module === 'yarn' && in_array($screenMeta['slug'], ['purchase-contract', 'sale-contract'], true)) {
            return $this->storeYarnContract($request, $screenMeta['slug'], $contractCalculator);
        }

        if ($module === 'yarn' && in_array($screenMeta['slug'], ['purchase-contract-wise', 'sale-contract-wise'], true)) {
            return $this->storeYarnContractWise($request, $screenMeta['slug'], $numberService, $contractCalculator);
        }

        $this->normalizeErpDates($request, ['trans_date']);

        $data = $request->validate([
            'trans_date' => ['required', 'date', new ErpDate],
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

        $postOnSubmit = in_array($data['submit_action'] ?? 'post', ['post', 'save'], true);
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

    public function updateScreenData(
        Request $request,
        string $screen,
        InventoryTransaction $transaction,
        YarnContractBalanceService $balanceService,
        YarnContractCalculationService $contractCalculator,
    ): RedirectResponse|JsonResponse {
        $module = (string) $request->route('module');
        $definition = self::MODULES[$module] ?? abort(404);
        $screenMeta = $this->findScreen($definition['groups'], $screen);
        abort_if($screenMeta === null, 404);
        abort_unless($this->allowed("{$module}.{$screenMeta['slug']}.edit"), 403);
        abort_unless($transaction->module === $module && $transaction->screen_slug === $screenMeta['slug'], 404);

        if ($module === 'yarn' && in_array($screenMeta['slug'], ['purchase-contract-wise', 'sale-contract-wise'], true)) {
            return $this->updateYarnContractWise($request, $screenMeta['slug'], $transaction, $contractCalculator);
        }

        $this->normalizeErpDates($request, ['trans_date']);

        $data = $request->validate([
            'trans_date' => ['required', 'date', new ErpDate],
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

        $postOnSubmit = in_array($data['submit_action'] ?? 'post', ['post', 'save'], true);
        if ($postOnSubmit) {
            abort_unless($this->allowed("{$module}.{$screenMeta['slug']}.post"), 403);
        }

        DB::transaction(function () use ($data, $transaction, $postOnSubmit): void {
            $primaryContract = isset($data['yarn_contract_id'])
                ? YarnContract::query()->find($data['yarn_contract_id'])
                : null;
            $fromContract = isset($data['from_yarn_contract_id'])
                ? YarnContract::query()->find($data['from_yarn_contract_id'])
                : null;

            $transaction->update([
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
                'status' => $postOnSubmit ? 'posted' : $transaction->status,
                'meta' => $data['meta'] ?? [],
            ]);

            $transaction->lines()->delete();
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
        });

        return $this->jsonOrRedirect(
            $request,
            redirect()->route('erp.' . $module . '.screen', array_merge(['screen' => $screenMeta['slug']], $this->historyQuery($request))),
            "Transaction {$transaction->trans_no} updated."
        );
    }

    public function updateYarnContract(Request $request, string $screen, YarnContract $contract, YarnContractCalculationService $calculator): RedirectResponse|JsonResponse
    {
        $module = (string) $request->route('module');
        abort_unless($module === 'yarn', 404);
        $direction = $screen === 'sale-contract' ? 'sale' : 'purchase';
        abort_unless($contract->direction === $direction, 404);
        abort_unless($this->allowed("yarn.{$screen}.edit"), 403);

        $this->normalizeErpDates($request, ['contract_date']);
        $data = $request->validate($this->yarnContractValidationRules());
        $attributes = $this->mapYarnContractAttributes($data, $direction, $calculator, $contract);

        $contract->update($attributes);

        return $this->jsonOrRedirect(
            $request,
            redirect()->route('erp.yarn.screen', array_merge(['screen' => $screen], $this->historyQuery($request))),
            "Yarn contract {$contract->contract_no} updated."
        );
    }

    public function destroyScreenData(
        Request $request,
        string $screen,
        InventoryTransaction $transaction,
    ): RedirectResponse|JsonResponse {
        $this->ensureAdminCanDeleteRecords();

        $module = (string) $request->route('module');
        $definition = self::MODULES[$module] ?? abort(404);
        $screenMeta = $this->findScreen($definition['groups'], $screen);
        abort_if($screenMeta === null, 404);
        abort_unless($transaction->module === $module && $transaction->screen_slug === $screenMeta['slug'], 404);

        $transNo = $transaction->trans_no;
        $historyDate = \App\Support\ErpDate::display($transaction->trans_date);
        $transaction->delete();

        return $this->erpDeleteResponse(
            $request,
            'erp.' . $module . '.screen',
            ['screen' => $screenMeta['slug']],
            "Transaction {$transNo} deleted.",
            $historyDate,
        );
    }

    public function destroyYarnContract(Request $request, string $screen, YarnContract $contract): RedirectResponse|JsonResponse
    {
        $this->ensureAdminCanDeleteRecords();

        $module = (string) $request->route('module');
        abort_unless($module === 'yarn', 404);

        $direction = $screen === 'sale-contract' ? 'sale' : 'purchase';
        abort_unless($contract->direction === $direction, 404);

        $contractNo = $contract->contract_no;
        $historyDate = \App\Support\ErpDate::display($contract->contract_date);
        $contract->delete();

        return $this->erpDeleteResponse(
            $request,
            'erp.yarn.screen',
            ['screen' => $screen],
            "Yarn contract {$contractNo} deleted.",
            $historyDate,
        );
    }

    private function storeYarnContract(Request $request, string $screen, YarnContractCalculationService $calculator): RedirectResponse|JsonResponse
    {
        $direction = $screen === 'sale-contract' ? 'sale' : 'purchase';

        $this->normalizeErpDates($request, ['contract_date']);
        $data = $request->validate($this->yarnContractValidationRules());
        $attributes = $this->mapYarnContractAttributes($data, $direction, $calculator);

        $contract = YarnContract::query()->updateOrCreate(
            ['contract_no' => $data['contract_no'], 'direction' => $direction],
            [...$attributes, 'created_by' => Auth::id()]
        );

        return $this->jsonOrRedirect(
            $request,
            back(),
            "Yarn contract {$contract->contract_no} saved."
        );
    }

    private function storeYarnContractWise(Request $request, string $screen, VoucherNumberService $numberService, YarnContractCalculationService $calculator): RedirectResponse|JsonResponse
    {
        $this->normalizeErpDates($request, ['trans_date']);
        $data = $request->validate($this->yarnContractWiseValidationRules());
        $contract = YarnContract::query()->with('item')->findOrFail($data['yarn_contract_id']);

        if ((int) $contract->account_id !== (int) $data['account_id']) {
            throw ValidationException::withMessages(['yarn_contract_id' => 'Selected contract does not belong to this party.']);
        }

        $totals = $calculator->calculate([
            'quantity' => $data['quantity'],
            'no_of_cones' => $data['no_of_cones'] ?? 0,
            'packing_size' => $data['packing_size'],
            'rate' => $data['rate'],
            'commission_percent' => $data['commission_percent'] ?? $contract->commission_percent,
            'brokery_percent' => $data['brokery_percent'] ?? $contract->brokery_percent,
        ]);

        $meta = array_merge($data['meta'] ?? [], [
            'voucher_type' => $data['meta']['voucher_type'] ?? ($screen === 'sale-contract-wise' ? 'YSV' : 'YPV'),
            'do_no' => $data['meta']['do_no'] ?? null,
            'bility_no' => $data['meta']['bility_no'] ?? null,
            'vehicle_no' => $data['meta']['vehicle_no'] ?? null,
            'driver_name' => $data['meta']['driver_name'] ?? null,
            'item_id' => $data['item_id'],
            'packing_size' => $data['packing_size'],
            'quantity' => $data['quantity'],
            'no_of_cones' => $data['no_of_cones'] ?? 0,
            'rate' => $data['rate'],
            'commission_percent' => $data['commission_percent'] ?? $contract->commission_percent,
            'brokery_percent' => $data['brokery_percent'] ?? $contract->brokery_percent,
            'yarn_type' => $data['yarn_type'] ?? $contract->yarn_type,
            'weight_lbs' => $totals['weight_lbs'],
            'total_kgs' => $totals['total_kgs'],
            'total_amount' => $totals['total_amount'],
            'total_commission' => $totals['total_commission'],
            'total_brokery' => $totals['total_brokery'],
            'total_net_amount' => $totals['total_net_amount'],
        ]);

        $postOnSubmit = in_array($data['submit_action'] ?? 'post', ['post', 'save'], true);

        $transaction = DB::transaction(function () use ($data, $screen, $contract, $numberService, $totals, $meta, $postOnSubmit) {
            $transNo = $numberService->nextTransaction('yarn', $screen);

            $transaction = InventoryTransaction::query()->create([
                'module' => 'yarn',
                'screen_slug' => $screen,
                'trans_no' => $transNo,
                'trans_date' => $data['trans_date'],
                'party_id' => $contract->party_id,
                'account_id' => $data['account_id'],
                'yarn_contract_id' => $contract->id,
                'from_godown_id' => $data['from_godown_id'],
                'remarks' => $data['remarks'] ?? null,
                'status' => $postOnSubmit ? 'posted' : 'draft',
                'meta' => $meta,
                'total_qty' => $data['quantity'],
                'total_amount' => $totals['total_net_amount'],
                'created_by' => Auth::id(),
            ]);

            InventoryTransactionLine::query()->create([
                'inventory_transaction_id' => $transaction->id,
                'item_id' => $data['item_id'],
                'description' => $contract->item?->name,
                'qty' => $data['quantity'],
                'unit' => 'BAGS',
                'weight_lbs' => $totals['weight_lbs'],
                'rate' => $data['rate'],
                'amount' => $totals['total_net_amount'],
                'meta' => ['no_of_cones' => $data['no_of_cones'] ?? 0],
            ]);

            return $transaction;
        });

        return $this->jsonOrRedirect(
            $request,
            back(),
            "Transaction {$transaction->trans_no} " . ($postOnSubmit ? 'posted.' : 'saved.')
        );
    }

    private function updateYarnContractWise(Request $request, string $screen, InventoryTransaction $transaction, YarnContractCalculationService $calculator): RedirectResponse|JsonResponse
    {
        $this->normalizeErpDates($request, ['trans_date']);
        $data = $request->validate($this->yarnContractWiseValidationRules());
        $contract = YarnContract::query()->with('item')->findOrFail($data['yarn_contract_id']);

        if ((int) $contract->account_id !== (int) $data['account_id']) {
            throw ValidationException::withMessages(['yarn_contract_id' => 'Selected contract does not belong to this party.']);
        }

        $totals = $calculator->calculate([
            'quantity' => $data['quantity'],
            'no_of_cones' => $data['no_of_cones'] ?? 0,
            'packing_size' => $data['packing_size'],
            'rate' => $data['rate'],
            'commission_percent' => $data['commission_percent'] ?? $contract->commission_percent,
            'brokery_percent' => $data['brokery_percent'] ?? $contract->brokery_percent,
        ]);

        $meta = array_merge($transaction->meta ?? [], $data['meta'] ?? [], [
            'item_id' => $data['item_id'],
            'packing_size' => $data['packing_size'],
            'quantity' => $data['quantity'],
            'no_of_cones' => $data['no_of_cones'] ?? 0,
            'rate' => $data['rate'],
            'commission_percent' => $data['commission_percent'] ?? $contract->commission_percent,
            'brokery_percent' => $data['brokery_percent'] ?? $contract->brokery_percent,
            'yarn_type' => $data['yarn_type'] ?? $contract->yarn_type,
            'weight_lbs' => $totals['weight_lbs'],
            'total_kgs' => $totals['total_kgs'],
            'total_amount' => $totals['total_amount'],
            'total_commission' => $totals['total_commission'],
            'total_brokery' => $totals['total_brokery'],
            'total_net_amount' => $totals['total_net_amount'],
        ]);

        $postOnSubmit = in_array($data['submit_action'] ?? 'post', ['post', 'save'], true);

        DB::transaction(function () use ($data, $contract, $transaction, $totals, $meta, $postOnSubmit): void {
            $transaction->update([
                'trans_date' => $data['trans_date'],
                'account_id' => $data['account_id'],
                'yarn_contract_id' => $contract->id,
                'from_godown_id' => $data['from_godown_id'],
                'remarks' => $data['remarks'] ?? null,
                'status' => $postOnSubmit ? 'posted' : $transaction->status,
                'meta' => $meta,
                'total_qty' => $data['quantity'],
                'total_amount' => $totals['total_net_amount'],
            ]);

            $transaction->lines()->delete();
            InventoryTransactionLine::query()->create([
                'inventory_transaction_id' => $transaction->id,
                'item_id' => $data['item_id'],
                'description' => $contract->item?->name,
                'qty' => $data['quantity'],
                'unit' => 'BAGS',
                'weight_lbs' => $totals['weight_lbs'],
                'rate' => $data['rate'],
                'amount' => $totals['total_net_amount'],
                'meta' => ['no_of_cones' => $data['no_of_cones'] ?? 0],
            ]);
        });

        return $this->jsonOrRedirect(
            $request,
            redirect()->route('erp.yarn.screen', array_merge(['screen' => $screen], $this->historyQuery($request))),
            "Transaction {$transaction->trans_no} updated."
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function yarnContractValidationRules(): array
    {
        return [
            'contract_no' => ['required', 'string', 'max:80'],
            'contract_date' => ['required', 'date', new ErpDate],
            'contract_code' => ['nullable', 'string', 'max:80'],
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('level', 'sub_ledger')->where('is_active', true))],
            'party_id' => ['nullable', 'integer', 'exists:parties,id'],
            'broker_account_id' => ['nullable', 'integer', Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('level', 'sub_ledger')->where('is_active', true))],
            'payment_term' => ['required', 'string', Rule::in(['cash', 'credit'])],
            'commission_percent' => ['nullable', 'numeric', 'min:0'],
            'brokery_percent' => ['nullable', 'numeric', 'min:0'],
            'yarn_type' => ['nullable', 'string', Rule::in(['any', 'warp', 'weft'])],
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'no_of_cones' => ['nullable', 'numeric', 'min:0'],
            'packing_size' => ['required', 'numeric', 'min:0'],
            'rate' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['open', 'close', 'closed', 'hold'])],
            'remarks' => ['nullable', 'string', 'max:255'],
            'meta' => ['nullable', 'array'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function yarnContractWiseValidationRules(): array
    {
        return [
            'trans_date' => ['required', 'date', new ErpDate],
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('level', 'sub_ledger')->where('is_active', true))],
            'yarn_contract_id' => ['required', 'integer', 'exists:yarn_contracts,id'],
            'from_godown_id' => ['required', 'integer', 'exists:godowns,id'],
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'packing_size' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'no_of_cones' => ['nullable', 'numeric', 'min:0'],
            'rate' => ['required', 'numeric', 'min:0'],
            'commission_percent' => ['nullable', 'numeric', 'min:0'],
            'brokery_percent' => ['nullable', 'numeric', 'min:0'],
            'yarn_type' => ['nullable', 'string', Rule::in(['any', 'warp', 'weft'])],
            'remarks' => ['nullable', 'string', 'max:255'],
            'submit_action' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
            'meta.voucher_type' => ['nullable', 'string', 'max:20'],
            'meta.do_no' => ['nullable', 'string', 'max:80'],
            'meta.bility_no' => ['nullable', 'string', 'max:80'],
            'meta.vehicle_no' => ['nullable', 'string', 'max:80'],
            'meta.driver_name' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function mapYarnContractAttributes(array $data, string $direction, YarnContractCalculationService $calculator, ?YarnContract $existing = null): array
    {
        $item = Item::query()->find($data['item_id']);
        $packingWeight = $item?->packing_weight ?? 100;

        $totals = $calculator->calculate([
            'quantity' => $data['quantity'],
            'no_of_cones' => $data['no_of_cones'] ?? 0,
            'packing_size' => $data['packing_size'],
            'rate' => $data['rate'],
            'commission_percent' => $data['commission_percent'] ?? 0,
            'brokery_percent' => $data['brokery_percent'] ?? 0,
        ]);

        $status = $data['status'] ?? 'open';
        if (in_array($status, ['closed', 'hold'], true)) {
            $status = $status === 'closed' ? 'close' : 'open';
        }

        $contractCode = trim((string) ($data['contract_code'] ?? ''));
        if ($contractCode === '') {
            $contractCode = $calculator->buildContractCode($direction, $data['contract_no']);
        }

        $fallbackPartyId = $data['party_id'] ?? Party::query()->value('id');

        return [
            'contract_code' => $contractCode,
            'contract_type' => strtoupper($direction),
            'contract_date' => $data['contract_date'],
            'payment_term' => $data['payment_term'],
            'party_id' => $fallbackPartyId,
            'account_id' => $data['account_id'],
            'broker_account_id' => $data['broker_account_id'] ?? null,
            'commission_percent' => $data['commission_percent'] ?? 0,
            'brokery_percent' => $data['brokery_percent'] ?? 0,
            'yarn_type' => $data['yarn_type'] ?? 'any',
            'item_id' => $data['item_id'],
            'unit' => 'LBS',
            'quantity' => $data['quantity'],
            'no_of_cones' => $data['no_of_cones'] ?? 0,
            'weight_lbs' => $totals['weight_lbs'],
            'total_kgs' => $totals['total_kgs'],
            'packing_size' => $data['packing_size'],
            'packing_weight' => $packingWeight,
            'rate' => $data['rate'],
            'total_amount' => $totals['total_amount'],
            'total_commission' => $totals['total_commission'],
            'total_brokery' => $totals['total_brokery'],
            'total_net_amount' => $totals['total_net_amount'],
            'status' => $status,
            'remarks' => $data['remarks'] ?? null,
            'meta' => $data['meta'] ?? ($existing?->meta ?? []),
        ];
    }

    private function jsonOrRedirect(Request $request, RedirectResponse $redirect, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => $redirect->getTargetUrl(),
            ]);
        }

        return $redirect->with('status', $message);
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

    /**
     * @param array<string, string> $screenMeta
     */
    private function resolveEditingTransaction(Request $request, string $module, array $screenMeta): ?InventoryTransaction
    {
        $editId = $request->query('edit');
        if ($editId === null || $editId === '') {
            return null;
        }

        if (in_array($screenMeta['slug'], ['purchase-contract', 'sale-contract'], true)) {
            return null;
        }

        $transaction = InventoryTransaction::query()
            ->where('module', $module)
            ->where('screen_slug', $screenMeta['slug'])
            ->with(['lines.item', 'yarnContract', 'account'])
            ->find($editId);

        abort_if($transaction === null, 404);
        abort_unless($this->allowed("{$module}.{$screenMeta['slug']}.edit"), 403);

        return $transaction;
    }

    /**
     * @param array<string, string> $screenMeta
     */
    private function resolveEditingContract(Request $request, string $module, array $screenMeta): ?YarnContract
    {
        $editId = $request->query('edit');
        if ($editId === null || $editId === '' || $module !== 'yarn') {
            return null;
        }

        if (! in_array($screenMeta['slug'], ['purchase-contract', 'sale-contract'], true)) {
            return null;
        }

        $direction = $screenMeta['slug'] === 'sale-contract' ? 'sale' : 'purchase';
        $contract = YarnContract::query()
            ->where('direction', $direction)
            ->find($editId);

        abort_if($contract === null, 404);
        abort_unless($this->allowed("yarn.{$screenMeta['slug']}.edit"), 403);

        return $contract;
    }

    /**
     * @return array<string, mixed>
     */
    private function historyQuery(Request $request): array
    {
        return RecordHistory::historyQuery($request);
    }
}

