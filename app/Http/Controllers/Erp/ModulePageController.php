<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionLine;
use App\Models\Item;
use App\Models\Party;
use App\Services\PostingService;
use App\Services\VoucherNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ModulePageController extends Controller
{
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

        return view('erp.module-screen', [
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
            'recentTransactions' => InventoryTransaction::query()
                ->where('module', $module)
                ->where('screen_slug', $screenMeta['slug'])
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }

    public function storeScreenData(Request $request, string $screen, VoucherNumberService $numberService): RedirectResponse
    {
        $module = (string) $request->route('module');
        $definition = self::MODULES[$module] ?? abort(404);
        $screenMeta = $this->findScreen($definition['groups'], $screen);
        abort_if($screenMeta === null, 404);
        abort_unless($this->allowed("{$module}.{$screenMeta['slug']}.create"), 403);

        $data = $request->validate([
            'trans_date' => ['required', 'date'],
            'party_id' => ['nullable', 'integer', 'exists:parties,id'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.item_id' => ['nullable', 'integer', 'exists:items,id'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.qty' => ['nullable', 'numeric', 'min:0'],
            'lines.*.unit' => ['nullable', 'string', 'max:20'],
            'lines.*.weight_lbs' => ['nullable', 'numeric', 'min:0'],
            'lines.*.rate' => ['nullable', 'numeric', 'min:0'],
            'lines.*.amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $transaction = DB::transaction(function () use ($data, $module, $screenMeta, $numberService) {
            $transNo = $numberService->nextTransaction($module, $screenMeta['slug']);
            $transaction = InventoryTransaction::query()->create([
                'module' => $module,
                'screen_slug' => $screenMeta['slug'],
                'trans_no' => $transNo,
                'trans_date' => $data['trans_date'],
                'party_id' => $data['party_id'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            $totalQty = 0.0;
            $totalAmount = 0.0;
            foreach ($data['lines'] as $line) {
                if (empty($line['item_id']) && empty($line['description']) && empty($line['qty']) && empty($line['amount'])) {
                    continue;
                }
                $qty = (float) ($line['qty'] ?? 0);
                $rate = (float) ($line['rate'] ?? 0);
                $amount = (float) ($line['amount'] ?? ($qty * $rate));
                $totalQty += $qty;
                $totalAmount += $amount;

                InventoryTransactionLine::query()->create([
                    'inventory_transaction_id' => $transaction->id,
                    'item_id' => $line['item_id'] ?? null,
                    'description' => $line['description'] ?? null,
                    'qty' => $qty,
                    'unit' => $line['unit'] ?? null,
                    'weight_lbs' => (float) ($line['weight_lbs'] ?? 0),
                    'rate' => $rate,
                    'amount' => $amount,
                ]);
            }

            $transaction->update([
                'total_qty' => $totalQty,
                'total_amount' => $totalAmount,
            ]);

            return $transaction;
        });

        return back()->with('status', "Transaction {$transaction->trans_no} saved.");
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
            'transaction' => $transaction->load('lines.item', 'party'),
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

