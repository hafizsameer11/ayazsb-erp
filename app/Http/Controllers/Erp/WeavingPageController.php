<?php

namespace App\Http\Controllers\Erp;

use App\Http\Concerns\AuthorizesWeaving;
use App\Http\Concerns\NormalizesErpDates;
use App\Http\Concerns\RespondsWithJsonOrRedirect;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\GreyConversionContract;
use App\Models\GreyQuality;
use App\Models\Item;
use App\Models\WeavingBeam;
use App\Models\WeavingDepartment;
use App\Models\WeavingLoom;
use App\Models\WeavingPieceLength;
use App\Models\WeavingProductionEntry;
use App\Models\WeavingProductionLine;
use App\Models\WeavingSet;
use App\Models\WeavingTransaction;
use App\Models\WeavingTransactionLine;
use App\Rules\ErpDate;
use App\Services\WeavingNumberService;
use App\Services\WeavingStockService;
use App\Services\WeavingTransactionTotalsService;
use App\Services\WeavingVoucherBridgeService;
use App\Support\RecordHistory;
use App\Support\WeavingModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WeavingPageController extends Controller
{
    use AuthorizesWeaving;
    use NormalizesErpDates;
    use RespondsWithJsonOrRedirect;

    private const STORE_SCREENS = ['store-issue', 'purchase-order', 'purchase-return'];

    private const YARN_SCREENS = [
        'yarn-receipt',
        'yarn-stock-adjustment',
        'yarn-issuance-to-sizing',
        'yarn-return-sizing-to-stock',
        'yarn-issuance-stock-to-production',
        'yarn-return-production-to-stock',
        'yarn-return-stock-to-party',
    ];

    private const FABRIC_SCREENS = [
        'grey-stock-adjustment',
        'rejection-receipt-packi-parchi',
        'mending-form',
        'fabric-issue-conversion-kachi',
        'fabric-issue-conversion-pachi',
        'fabric-issue-sale-kachi',
        'fabric-return',
        'rejection-sale',
        'rejection-stock-quality-transfer',
    ];

    public function dashboard(): View
    {
        abort_unless($this->weavingAllowed('store-issue', 'view') || $this->weavingAllowed('master-data', 'view'), 403);
        $definition = WeavingModule::definition();

        return view('erp.module-dashboard', [
            'activeModule' => 'weaving',
            'moduleKey' => 'weaving',
            'moduleLabel' => $definition['label'],
            'groups' => $definition['groups'],
            'permissionPrefix' => 'weaving.dashboard',
            'pageTitle' => $definition['label'],
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => $definition['label']],
            ],
        ]);
    }

    public function screen(Request $request, string $screen): View
    {
        $screenMeta = WeavingModule::findScreen($screen) ?? abort(404);
        abort_unless($this->weavingAllowed($screen, 'view'), 403);

        if ($screen === 'master-data') {
            return redirect()->route('erp.weaving.master-data');
        }

        $viewData = $this->baseViewData($screenMeta);
        $viewData = array_merge($viewData, $this->lookupData());
        $viewData = array_merge($viewData, RecordHistory::buildForDay(
            $request,
            WeavingTransaction::query()
                ->where('screen_slug', $screen)
                ->with(['account', 'department', 'lines.item'])
                ->orderByDesc('trans_date')
                ->orderByDesc('id'),
            'trans_date',
            'erp.weaving.screen',
            ['screen' => $screen],
        ));

        if ($screen === 'production-data-entry') {
            $viewData['editingProduction'] = $this->resolveEditingProduction($request);
            $viewName = 'erp.weaving.production-data-entry';

            return view($viewName, $viewData);
        }

        if ($screen === 'set-receipt-details') {
            $viewData['editingSet'] = $this->resolveEditingSet($request);

            return view('erp.weaving.set-receipt-details', $viewData);
        }

        $viewData['editingTransaction'] = $this->resolveEditingTransaction($request, $screen);

        if (in_array($screen, self::STORE_SCREENS, true)) {
            return view('erp.weaving.store-transaction', $viewData);
        }

        if (in_array($screen, self::YARN_SCREENS, true)) {
            $viewData['yarnPool'] = WeavingModule::yarnStockPoolForScreen($screen);

            return view('erp.weaving.yarn-transaction', $viewData);
        }

        if (in_array($screen, self::FABRIC_SCREENS, true)) {
            return view('erp.weaving.fabric-transaction', $viewData);
        }

        $viewName = 'erp.weaving.' . $screen;

        abort_unless(view()->exists($viewName), 404);

        return view($viewName, $viewData);
    }

    public function store(Request $request, string $screen): RedirectResponse|JsonResponse
    {
        $screenMeta = WeavingModule::findScreen($screen) ?? abort(404);
        abort_unless($this->weavingAllowed($screen, 'create'), 403);

        if ($screen === 'set-receipt-details') {
            return $this->storeSetReceipt($request);
        }

        if ($screen === 'production-data-entry') {
            return $this->storeProduction($request);
        }

        return $this->storeTransaction($request, $screenMeta);
    }

    public function update(Request $request, string $screen, WeavingTransaction $transaction): RedirectResponse|JsonResponse
    {
        $screenMeta = WeavingModule::findScreen($screen) ?? abort(404);
        abort_if($transaction->screen_slug !== $screen, 404);
        abort_unless($this->weavingAllowed($screen, 'edit'), 403);

        return $this->persistTransaction($request, $screenMeta, $transaction);
    }

    public function destroy(string $screen, WeavingTransaction $transaction): RedirectResponse
    {
        abort_if($transaction->screen_slug !== $screen, 404);
        abort_unless($this->weavingAllowed($screen, 'delete'), 403);

        $transaction->delete();

        return redirect()
            ->route('erp.weaving.screen', ['screen' => $screen])
            ->with('status', "Transaction {$transaction->trans_no} deleted.");
    }

    public function post(string $screen, WeavingTransaction $transaction): RedirectResponse
    {
        abort_if($transaction->screen_slug !== $screen, 404);
        abort_unless($this->weavingAllowed($screen, 'post'), 403);

        $transaction->update(['status' => 'posted']);

        return back()->with('status', "Transaction {$transaction->trans_no} posted.");
    }

    public function generateVoucher(string $screen, WeavingTransaction $transaction, WeavingVoucherBridgeService $voucherBridge): RedirectResponse
    {
        abort_if($transaction->screen_slug !== $screen, 404);
        abort_unless($this->weavingAllowed($screen, 'post'), 403);

        try {
            $voucher = $voucherBridge->generateForTransaction($transaction->fresh(['lines.item', 'account']));
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return back()->with('status', "Voucher {$voucher->voucher_number} generated.");
    }

    public function updateVoucher(string $screen, WeavingTransaction $transaction, WeavingVoucherBridgeService $voucherBridge): RedirectResponse
    {
        abort_if($transaction->screen_slug !== $screen, 404);
        abort_unless($this->weavingAllowed($screen, 'edit'), 403);

        try {
            $voucherBridge->syncExisting($transaction->fresh(['lines.item', 'account']));
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return back()->with('status', 'Voucher updated.');
    }

    public function generateSetVoucher(WeavingSet $set, WeavingVoucherBridgeService $voucherBridge): RedirectResponse
    {
        abort_unless($this->weavingAllowed('set-receipt-details', 'post'), 403);

        try {
            $voucher = $voucherBridge->generateForSet($set->fresh());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('erp.weaving.screen', ['screen' => 'set-receipt-details', 'edit' => $set->id])
            ->with('status', "Voucher {$voucher->voucher_number} generated.");
    }

    public function updateSetVoucher(WeavingSet $set, WeavingVoucherBridgeService $voucherBridge): RedirectResponse
    {
        abort_unless($this->weavingAllowed('set-receipt-details', 'edit'), 403);

        try {
            $voucherBridge->syncExistingSet($set->fresh());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return back()->with('status', 'Set receipt voucher updated.');
    }

    /**
     * @param array{slug: string, label: string, code: string} $screenMeta
     */
    private function storeTransaction(Request $request, array $screenMeta): RedirectResponse|JsonResponse
    {
        $transaction = DB::transaction(fn () => $this->createTransaction($request, $screenMeta));

        return $this->jsonOrRedirect(
            $request,
            redirect()->route('erp.weaving.screen', ['screen' => $screenMeta['slug'], 'edit' => $transaction->id]),
            "Saved {$transaction->trans_no}."
        );
    }

    /**
     * @param array{slug: string, label: string, code: string} $screenMeta
     */
    private function persistTransaction(Request $request, array $screenMeta, WeavingTransaction $transaction): RedirectResponse|JsonResponse
    {
        $transaction = DB::transaction(fn () => $this->saveTransaction($request, $screenMeta, $transaction));

        return $this->jsonOrRedirect(
            $request,
            redirect()->route('erp.weaving.screen', array_merge(['screen' => $screenMeta['slug'], 'edit' => $transaction->id], RecordHistory::historyQuery($request))),
            "Updated {$transaction->trans_no}."
        );
    }

    /**
     * @param array{slug: string, label: string, code: string} $screenMeta
     */
    private function createTransaction(Request $request, array $screenMeta): WeavingTransaction
    {
        $this->normalizeErpDates($request, ['trans_date']);
        $data = $this->validateTransaction($request, $screenMeta['slug']);
        $data = $this->mergeWeavingTransactionData($data, $screenMeta['slug']);
        $totals = app(WeavingTransactionTotalsService::class)->calculateLines($data['lines'] ?? []);

        $transaction = WeavingTransaction::query()->create([
            'screen_slug' => $screenMeta['slug'],
            'trans_no' => app(WeavingNumberService::class)->nextTransaction($screenMeta['slug']),
            'trans_date' => $data['trans_date'],
            'account_id' => $data['account_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'source_transaction_id' => $data['source_transaction_id'] ?? null,
            'grey_conversion_contract_id' => $data['grey_conversion_contract_id'] ?? null,
            'grey_quality_id' => $data['grey_quality_id'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'meta' => $data['meta'] ?? [],
            'total_qty' => $totals['total_qty'],
            'total_amount' => $totals['total_amount'],
            'status' => ($data['submit_action'] ?? '') === 'post' ? 'posted' : 'draft',
            'created_by' => Auth::id(),
        ]);

        $this->syncLines($transaction, $totals['lines']);
        $this->applyStockForScreen($screenMeta['slug'], $totals['lines']);
        $this->applySpecialScreenEffects($screenMeta['slug'], $transaction, $data);

        return $transaction->fresh(['lines.item']);
    }

    /**
     * @param array{slug: string, label: string, code: string} $screenMeta
     */
    private function saveTransaction(Request $request, array $screenMeta, WeavingTransaction $transaction): WeavingTransaction
    {
        $this->normalizeErpDates($request, ['trans_date']);
        $data = $this->validateTransaction($request, $screenMeta['slug']);
        $data = $this->mergeWeavingTransactionData($data, $screenMeta['slug']);
        $totals = app(WeavingTransactionTotalsService::class)->calculateLines($data['lines'] ?? []);

        $transaction->update([
            'trans_date' => $data['trans_date'],
            'account_id' => $data['account_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'source_transaction_id' => $data['source_transaction_id'] ?? null,
            'grey_conversion_contract_id' => $data['grey_conversion_contract_id'] ?? null,
            'grey_quality_id' => $data['grey_quality_id'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'meta' => array_merge($transaction->meta ?? [], $data['meta'] ?? []),
            'total_qty' => $totals['total_qty'],
            'total_amount' => $totals['total_amount'],
        ]);

        $transaction->lines()->delete();
        $this->syncLines($transaction, $totals['lines']);
        $this->applyStockForScreen($screenMeta['slug'], $totals['lines']);
        $this->applySpecialScreenEffects($screenMeta['slug'], $transaction, $data);

        return $transaction->fresh(['lines.item']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTransaction(Request $request, string $screen): array
    {
        $requiresParty = (bool) data_get(config('weaving_vouchers.screens.' . $screen), 'requires_party');

        return $request->validate([
            'trans_date' => ['required', 'date', new ErpDate],
            'account_id' => $this->subLedgerAccountRule($requiresParty),
            'department_id' => ['nullable', 'integer', 'exists:weaving_departments,id'],
            'source_transaction_id' => ['nullable', 'integer', 'exists:weaving_transactions,id'],
            'grey_conversion_contract_id' => ['nullable', 'integer', 'exists:grey_conversion_contracts,id'],
            'grey_quality_id' => ['nullable', 'integer', 'exists:grey_qualities,id'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'submit_action' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
            'meta.broker_account_id' => $this->subLedgerAccountRule(),
            'meta.sizing_party_account_id' => $this->subLedgerAccountRule(),
            'meta.voucher_type' => ['nullable', 'string', 'max:20'],
            'lines' => ['nullable', 'array'],
            'lines.*.item_id' => ['nullable', 'integer', 'exists:items,id'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.qty' => ['nullable', 'numeric'],
            'lines.*.rate' => ['nullable', 'numeric'],
            'lines.*.amount' => ['nullable', 'numeric'],
            'lines.*.meta' => ['nullable', 'array'],
            'lines.*.meta.cc_account_id' => $this->subLedgerAccountRule(),
            'lines.*.meta.beam_id' => ['nullable', 'integer', 'exists:weaving_beams,id'],
            'lines.*.meta.loom_id' => ['nullable', 'integer', 'exists:weaving_looms,id'],
        ]);
    }

    /**
     * @return list<string|Rule>
     */
    private function subLedgerAccountRule(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'integer',
            Rule::exists('accounts', 'id')->where(fn ($query) => $query->where('level', 'sub_ledger')->where('is_active', true)),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mergeWeavingTransactionData(array $data, string $screen): array
    {
        if (empty($data['account_id']) && ! empty($data['grey_conversion_contract_id'])) {
            $data['account_id'] = GreyConversionContract::query()
                ->whereKey($data['grey_conversion_contract_id'])
                ->value('account_id');
        }

        $data['meta'] = $data['meta'] ?? [];
        $screenConfig = config('weaving_vouchers.screens.' . $screen, []);
        if (empty($data['meta']['voucher_type']) && ! empty($screenConfig['voucher_type'])) {
            $data['meta']['voucher_type'] = $screenConfig['voucher_type'];
        }

        if ($editingVoucherId = $data['meta']['voucher_id'] ?? null) {
            $data['meta']['voucher_id'] = $editingVoucherId;
        }

        return $data;
    }

    /**
     * @param list<array<string, mixed>> $lines
     */
    private function syncLines(WeavingTransaction $transaction, array $lines): void
    {
        foreach ($lines as $line) {
            WeavingTransactionLine::query()->create([
                'weaving_transaction_id' => $transaction->id,
                'item_id' => $line['item_id'] ?? null,
                'line_no' => $line['line_no'] ?? 0,
                'description' => $line['description'] ?? null,
                'qty' => $line['qty'] ?? 0,
                'rate' => $line['rate'] ?? 0,
                'amount' => $line['amount'] ?? 0,
                'meta' => $line['meta'] ?? [],
            ]);
        }
    }

    /**
     * @param list<array<string, mixed>> $lines
     */
    private function applyStockForScreen(string $screen, array $lines): void
    {
        $stock = app(WeavingStockService::class);
        $itemLines = array_values(array_filter($lines, fn ($l) => ! empty($l['item_id']) && (float) ($l['qty'] ?? 0) != 0));

        if ($itemLines === []) {
            return;
        }

        match ($screen) {
            'store-issue' => $stock->applyStoreIssue($itemLines),
            'purchase-order' => $stock->applyStoreReceipt($itemLines),
            'purchase-return' => $stock->applyStoreIssue($itemLines),
            'yarn-stock-adjustment' => $stock->applyYarnAdjustment('stock', $itemLines),
            'yarn-receipt' => $stock->applyYarnMovement('yarn-receipt', $itemLines),
            'sized-beams-issuance' => null,
            default => WeavingModule::yarnMovementForScreen($screen)['in'] || WeavingModule::yarnMovementForScreen($screen)['out']
                ? $stock->applyYarnMovement($screen, $itemLines)
                : null,
        };
    }

    private function storeSetReceipt(Request $request): RedirectResponse|JsonResponse
    {
        $this->normalizeErpDates($request, ['entry_date', 'receipt_date', 'meta.voucher_date']);
        $data = $request->validate([
            'set_id' => ['nullable', 'integer', 'exists:weaving_sets,id'],
            'set_no' => ['nullable', 'string', 'max:40'],
            'company_set_no' => ['nullable', 'string', 'max:40'],
            'entry_date' => ['nullable', 'date', new ErpDate],
            'receipt_date' => ['nullable', 'date', new ErpDate],
            'sizing_party_account_id' => $this->subLedgerAccountRule(),
            'grey_conversion_contract_id' => ['nullable', 'integer', 'exists:grey_conversion_contracts,id'],
            'grey_quality_id' => ['nullable', 'integer', 'exists:grey_qualities,id'],
            'shrink_percent' => ['nullable', 'numeric'],
            'width' => ['nullable', 'numeric'],
            'ends_tareen' => ['nullable', 'numeric'],
            'meters' => ['nullable', 'numeric'],
            'meta' => ['nullable', 'array'],
            'beams' => ['nullable', 'array'],
            'beams.*.beam_no' => ['nullable', 'string', 'max:40'],
            'beams.*.beam_length' => ['nullable', 'numeric'],
        ]);

        $set = DB::transaction(function () use ($data) {
            $setNo = $data['set_no'] ?? app(WeavingNumberService::class)->nextSetNo();
            $set = isset($data['set_id'])
                ? WeavingSet::query()->findOrFail($data['set_id'])
                : new WeavingSet(['set_no' => $setNo]);

            $set->fill([
                'set_no' => $setNo,
                'company_set_no' => $data['company_set_no'] ?? null,
                'entry_date' => $data['entry_date'] ?? now(),
                'receipt_date' => $data['receipt_date'] ?? now(),
                'sizing_party_account_id' => $data['sizing_party_account_id'] ?? null,
                'grey_conversion_contract_id' => $data['grey_conversion_contract_id'] ?? null,
                'grey_quality_id' => $data['grey_quality_id'] ?? null,
                'shrink_percent' => $data['shrink_percent'] ?? 0,
                'width' => $data['width'] ?? null,
                'ends_tareen' => $data['ends_tareen'] ?? null,
                'meters' => $data['meters'] ?? 0,
                'meta' => $data['meta'] ?? [],
                'created_by' => Auth::id(),
            ]);
            $set->save();

            $set->beams()->delete();
            foreach ($data['beams'] ?? [] as $beam) {
                if (empty($beam['beam_no'])) {
                    continue;
                }
                WeavingBeam::query()->create([
                    'weaving_set_id' => $set->id,
                    'beam_no' => $beam['beam_no'],
                    'beam_length' => $beam['beam_length'] ?? 0,
                    'status' => 'available',
                ]);
            }

            return $set;
        });

        return $this->jsonOrRedirect(
            $request,
            redirect()->route('erp.weaving.screen', ['screen' => 'set-receipt-details', 'edit' => $set->id]),
            "Set {$set->set_no} saved."
        );
    }

    private function storeProduction(Request $request): RedirectResponse|JsonResponse
    {
        $this->normalizeErpDates($request, ['doc_date']);
        $data = $request->validate([
            'entry_id' => ['nullable', 'integer', 'exists:weaving_production_entries,id'],
            'doc_date' => ['required', 'date', new ErpDate],
            'contract_grey_quality_id' => ['nullable', 'integer', 'exists:grey_qualities,id'],
            'production_grey_quality_id' => ['nullable', 'integer', 'exists:grey_qualities,id'],
            'meta' => ['nullable', 'array'],
            'lines' => ['nullable', 'array'],
            'lines.*.sr' => ['nullable', 'integer'],
            'lines.*.loom_id' => ['nullable', 'integer', 'exists:weaving_looms,id'],
            'lines.*.beam_id' => ['nullable', 'integer', 'exists:weaving_beams,id'],
            'lines.*.weaving_set_id' => ['nullable', 'integer', 'exists:weaving_sets,id'],
            'lines.*.grey_conversion_contract_id' => ['nullable', 'integer', 'exists:grey_conversion_contracts,id'],
            'lines.*.grey_quality_id' => ['nullable', 'integer', 'exists:grey_qualities,id'],
            'lines.*.width' => ['nullable', 'numeric'],
            'lines.*.beam_balance' => ['nullable', 'numeric'],
            'lines.*.sides' => ['nullable', 'array'],
            'lines.*.beam_status' => ['nullable', 'string', 'max:40'],
        ]);

        $entry = DB::transaction(function () use ($data) {
            if (! empty($data['entry_id'])) {
                $entry = WeavingProductionEntry::query()->findOrFail($data['entry_id']);
            } else {
                $entry = WeavingProductionEntry::query()->create([
                    'doc_no' => app(WeavingNumberService::class)->nextProductionDocNo(),
                    'doc_date' => $data['doc_date'],
                    'created_by' => Auth::id(),
                ]);
            }

            $entry->update([
                'doc_date' => $data['doc_date'],
                'contract_grey_quality_id' => $data['contract_grey_quality_id'] ?? null,
                'production_grey_quality_id' => $data['production_grey_quality_id'] ?? null,
                'meta' => $data['meta'] ?? [],
            ]);

            $entry->lines()->each(fn ($line) => $line->pieceLengths()->delete());
            $entry->lines()->delete();

            foreach ($data['lines'] ?? [] as $i => $row) {
                if (empty($row['loom_id']) && empty($row['grey_quality_id'])) {
                    continue;
                }
                WeavingProductionLine::query()->create([
                    'weaving_production_entry_id' => $entry->id,
                    'sr' => $row['sr'] ?? ($i + 1),
                    'loom_id' => $row['loom_id'] ?? null,
                    'beam_id' => $row['beam_id'] ?? null,
                    'weaving_set_id' => $row['weaving_set_id'] ?? null,
                    'grey_conversion_contract_id' => $row['grey_conversion_contract_id'] ?? null,
                    'grey_quality_id' => $row['grey_quality_id'] ?? null,
                    'width' => $row['width'] ?? null,
                    'beam_balance' => $row['beam_balance'] ?? null,
                    'sides' => $row['sides'] ?? [],
                    'beam_status' => $row['beam_status'] ?? null,
                ]);
            }

            return $entry;
        });

        return $this->jsonOrRedirect(
            $request,
            redirect()->route('erp.weaving.screen', ['screen' => 'production-data-entry', 'edit' => $entry->id]),
            "Production {$entry->doc_no} saved."
        );
    }

    /**
     * @param array{slug: string, label: string, code: string} $screenMeta
     * @return array<string, mixed>
     */
    private function baseViewData(array $screenMeta): array
    {
        $definition = WeavingModule::definition();

        return [
            'activeModule' => 'weaving',
            'moduleKey' => 'weaving',
            'moduleLabel' => $definition['label'],
            'screen' => $screenMeta,
            'permissionPrefix' => 'weaving.' . $screenMeta['slug'],
            'pageTitle' => $screenMeta['label'],
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => $definition['label'], 'route' => 'erp.weaving.dashboard'],
                ['label' => $screenMeta['label']],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function lookupData(): array
    {
        $stock = app(WeavingStockService::class);

        return [
            'accountParties' => Account::query()->postable()->orderBy('code')->get(),
            'departments' => WeavingDepartment::query()->where('is_active', true)->orderBy('code')->get(),
            'looms' => WeavingLoom::query()->where('is_active', true)->orderBy('loom_no')->get(),
            'storeItems' => Item::query()->whereIn('module', ['store', 'shared'])->where('is_active', true)->orderBy('code')->get(),
            'yarnItems' => Item::query()->whereIn('module', ['yarn', 'shared'])->where('is_active', true)->orderBy('code')->get(),
            'greyQualities' => GreyQuality::query()->where('is_active', true)->orderBy('quality_no')->get(),
            'conversionContracts' => GreyConversionContract::query()->with('account', 'quality')->orderByDesc('contract_date')->get(),
            'weavingSets' => WeavingSet::query()->with('beams')->orderByDesc('id')->limit(100)->get(),
            'storeStockMap' => $stock->storeStockMap(),
            'yarnStockMap' => $stock->yarnStockMap('stock'),
            'sizingStockMap' => $stock->yarnStockMap('sizing'),
            'productionStockMap' => $stock->yarnStockMap('production'),
            'purchaseOrders' => WeavingTransaction::query()->where('screen_slug', 'purchase-order')->orderByDesc('id')->limit(50)->get(),
        ];
    }

    private function resolveEditingTransaction(Request $request, string $screen): ?WeavingTransaction
    {
        $editId = $request->query('edit');
        if ($editId === null || $editId === '') {
            return null;
        }

        $transaction = WeavingTransaction::query()
            ->where('screen_slug', $screen)
            ->with(['lines.item', 'account', 'department', 'voucher'])
            ->find($editId);

        abort_if($transaction === null, 404);

        return $transaction;
    }

    private function resolveEditingSet(Request $request): ?WeavingSet
    {
        $editId = $request->query('edit');
        if ($editId === null || $editId === '') {
            return null;
        }

        return WeavingSet::query()->with(['beams', 'voucher'])->find($editId) ?? abort(404);
    }

    private function resolveEditingProduction(Request $request): ?WeavingProductionEntry
    {
        $editId = $request->query('edit');
        if ($editId === null || $editId === '') {
            return null;
        }

        return WeavingProductionEntry::query()->with('lines.loom', 'lines.beam')->find($editId) ?? abort(404);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applySpecialScreenEffects(string $screen, WeavingTransaction $transaction, array $data): void
    {
        if ($screen !== 'sized-beams-issuance') {
            return;
        }

        foreach ($data['lines'] ?? [] as $line) {
            $beamId = (int) ($line['meta']['beam_id'] ?? 0);
            $loomId = (int) ($line['meta']['loom_id'] ?? 0);
            if ($beamId <= 0) {
                continue;
            }
            WeavingBeam::query()->whereKey($beamId)->update([
                'status' => 'issued',
                'loom_id' => $loomId > 0 ? $loomId : null,
            ]);
        }
    }

    public function print(string $screen, WeavingTransaction $transaction): View
    {
        abort_if($transaction->screen_slug !== $screen, 404);
        abort_unless($this->weavingAllowed($screen, 'print'), 403);

        return view('erp.weaving.print.transaction', [
            'screen' => WeavingModule::findScreen($screen),
            'transaction' => $transaction->load(['lines.item', 'account', 'department']),
        ]);
    }
}
