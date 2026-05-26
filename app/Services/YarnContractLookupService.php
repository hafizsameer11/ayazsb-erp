<?php

namespace App\Services;

use App\Models\GreyConversionContract;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionLine;
use App\Models\YarnContract;
use Illuminate\Support\Collection;

class YarnContractLookupService
{
    public function __construct(
        private readonly YarnStockAvailabilityService $stockService,
    ) {}

    /**
     * Contract references for opening remarks (queried by party).
     *
     * @return array<int, list<array<string, mixed>>>
     */
    public function contractRemarksByAccount(): array
    {
        $map = [];

        foreach (YarnContract::query()->with('account')->orderBy('contract_no')->get() as $contract) {
            if (! $contract->account_id) {
                continue;
            }
            $map[$contract->account_id][] = [
                'key' => 'yarn:' . $contract->id,
                'contract_type' => strtoupper($contract->contract_type ?? $contract->direction),
                'contract_no' => $contract->contract_no,
                'contract_code' => $contract->contract_code,
                'remarks' => $contract->remarks ?? '',
                'label' => ($contract->contract_code ?? $contract->contract_no) . ' — ' . strtoupper($contract->direction) . ' — Yarn',
            ];
        }

        foreach (GreyConversionContract::query()->with(['account', 'quality'])->orderBy('contract_no')->get() as $contract) {
            if (! $contract->account_id) {
                continue;
            }
            $map[$contract->account_id][] = [
                'key' => 'grey:' . $contract->id,
                'contract_type' => strtoupper($contract->contract_type ?? 'CONV'),
                'contract_no' => $contract->contract_no,
                'contract_code' => $contract->contract_code,
                'remarks' => $contract->remarks ?? '',
                'label' => ($contract->contract_code ?? $contract->contract_no) . ' — Grey — ' . ($contract->quality?->quality_no ?? ''),
            ];
        }

        return $map;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function greyConversionContractsPayload(?int $accountId = null, bool $onlyWithIssuanceHistory = false): array
    {
        $query = GreyConversionContract::query()->with(['account', 'quality'])->orderByDesc('contract_date');

        if ($accountId !== null) {
            $query->where('account_id', $accountId);
        }

        $contracts = $query->get();
        if ($onlyWithIssuanceHistory) {
            $idsWithIssuance = InventoryTransaction::query()
                ->where('module', 'yarn')
                ->where('screen_slug', 'issuance')
                ->whereNotNull('grey_conversion_contract_id')
                ->distinct()
                ->pluck('grey_conversion_contract_id')
                ->all();
            $contracts = $contracts->whereIn('id', $idsWithIssuance);
        }

        return $contracts->map(fn (GreyConversionContract $c) => $this->mapGreyContract($c))->values()->all();
    }

    /**
     * Grey conversion contracts with issuance history for a party (transfer “from contract”).
     *
     * @return array<int, array<string, mixed>>
     */
    public function fromContractsForTransfer(int $accountId): array
    {
        $contractIds = InventoryTransaction::query()
            ->where('module', 'yarn')
            ->where('screen_slug', 'issuance')
            ->where('account_id', $accountId)
            ->whereNotNull('grey_conversion_contract_id')
            ->distinct()
            ->pluck('grey_conversion_contract_id');

        return GreyConversionContract::query()
            ->with(['account', 'quality'])
            ->whereIn('id', $contractIds)
            ->orderByDesc('contract_date')
            ->get()
            ->map(function (GreyConversionContract $c) use ($accountId) {
                $mapped = $this->mapGreyContract($c);
                $mapped['issued_bags_for_party'] = $this->issuedBagsOnGreyContract((int) $c->id, $accountId);
                $mapped['avg_issue_rate'] = $this->averageIssueRate((int) $c->id, $accountId);

                return $mapped;
            })
            ->values()
            ->all();
    }

    /**
     * Net bags issued on a grey conversion contract (issuance − return − transfer out).
     */
    public function issuedBagsOnGreyContract(int $greyContractId, ?int $accountId = null, ?int $excludeTransactionId = null): float
    {
        $issued = $this->sumBagsOnGreyContract($greyContractId, ['issuance'], $accountId, $excludeTransactionId);
        $returned = $this->sumBagsOnGreyContract($greyContractId, ['issuance-return'], $accountId, $excludeTransactionId);
        $transferredOut = $this->sumBagsOnGreyContract($greyContractId, ['issuance-transfer'], $accountId, $excludeTransactionId);

        return max(0, $issued - $returned - $transferredOut);
    }

    public function remainingBagsOnGreyContract(int $greyContractId, ?int $excludeTransactionId = null): float
    {
        $contract = GreyConversionContract::query()->find($greyContractId);
        if (! $contract) {
            return 0.0;
        }

        $required = (float) $contract->required_bags;
        $issued = $this->issuedBagsOnGreyContract($greyContractId, null, $excludeTransactionId);

        return max(0, $required - $issued);
    }

    /**
     * Lines still available to transfer/return per item for party + grey contract.
     *
     * @return list<array<string, mixed>>
     */
    public function issuableLinesForPartyContract(int $accountId, int $greyContractId, ?int $excludeTransactionId = null): array
    {
        $issuedByItem = $this->bagsByItemOnGreyContract($greyContractId, ['issuance'], $accountId, $excludeTransactionId);
        $returnedByItem = $this->bagsByItemOnGreyContract($greyContractId, ['issuance-return'], $accountId, $excludeTransactionId);
        $transferredByItem = $this->bagsByItemOnGreyContract($greyContractId, ['issuance-transfer'], $accountId, $excludeTransactionId);

        $lines = [];
        foreach ($issuedByItem as $itemId => $issued) {
            $available = max(0, $issued - ($returnedByItem[$itemId] ?? 0) - ($transferredByItem[$itemId] ?? 0));
            if ($available <= 0) {
                continue;
            }
            $rate = $this->lastIssueRateForItem($greyContractId, $accountId, (int) $itemId);
            $stock = collect($this->stockService->yarnItemsPayload())->firstWhere('id', (int) $itemId);
            $itemCode = $stock['code'] ?? '';
            $itemRate = $rate > 0 ? $rate : ($stock['purchase_rate'] ?? 0);
            $lines[] = [
                'item_id' => (int) $itemId,
                'item_code' => $itemCode,
                'item_name' => $stock['name'] ?? '',
                'available_bags' => round($available, 4),
                'available_cones' => $stock['available_cones'] ?? 0,
                'packing_size' => $stock['pack_size_cones'] ?? 0,
                'purchase_rate' => $itemRate,
                'lov_label' => implode(' | ', array_filter([
                    $itemCode,
                    'Bags: ' . number_format($available, 2),
                    'Cones: ' . number_format((float) ($stock['available_cones'] ?? 0), 2),
                    $itemRate > 0 ? 'Rate: ' . number_format($itemRate, 2) : null,
                ])),
            ];
        }

        return $lines;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function yarnItemsPayload(): array
    {
        return $this->stockService->yarnItemsPayload();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function issuanceOptionsPayload(): array
    {
        return $this->stockService->issuanceOptionsPayload();
    }

    /**
     * @return list<int>
     */
    public function issuancePartyAccountIds(): array
    {
        return $this->stockService->issuancePartyAccountIds();
    }

    /**
     * @param list<string> $screens
     * @return array<int, float>
     */
    private function bagsByItemOnGreyContract(
        int $greyContractId,
        array $screens,
        ?int $accountId,
        ?int $excludeTransactionId,
    ): array {
        $totals = [];

        foreach ($screens as $screen) {
            $query = InventoryTransaction::query()
                ->where('module', 'yarn')
                ->where('screen_slug', $screen)
                ->with('lines');

            if ($accountId !== null) {
                if ($screen === 'issuance-transfer') {
                    $query->where('from_account_id', $accountId);
                } else {
                    $query->where('account_id', $accountId);
                }
            }

            if ($excludeTransactionId !== null) {
                $query->where('id', '!=', $excludeTransactionId);
            }

            if ($screen === 'issuance-transfer') {
                $query->where('meta->from_grey_conversion_contract_id', $greyContractId);
            } else {
                $query->where('grey_conversion_contract_id', $greyContractId);
            }

            foreach ($query->get() as $transaction) {
                foreach ($transaction->lines as $line) {
                    if (! $line->item_id) {
                        continue;
                    }
                    $totals[$line->item_id] = ($totals[$line->item_id] ?? 0) + (float) $line->qty;
                }
            }
        }

        return $totals;
    }

    /**
     * @param list<string> $screens
     */
    private function sumBagsOnGreyContract(
        int $greyContractId,
        array $screens,
        ?int $accountId,
        ?int $excludeTransactionId,
    ): float {
        return array_sum($this->bagsByItemOnGreyContract($greyContractId, $screens, $accountId, $excludeTransactionId));
    }

    private function averageIssueRate(int $greyContractId, int $accountId): float
    {
        $lines = InventoryTransactionLine::query()
            ->whereHas('transaction', fn ($q) => $q
                ->where('module', 'yarn')
                ->where('screen_slug', 'issuance')
                ->where('grey_conversion_contract_id', $greyContractId)
                ->where('account_id', $accountId))
            ->whereNotNull('rate')
            ->get();

        if ($lines->isEmpty()) {
            return 0.0;
        }

        return round((float) $lines->avg('rate'), 4);
    }

    private function lastIssueRateForItem(int $greyContractId, int $accountId, int $itemId): float
    {
        return (float) InventoryTransactionLine::query()
            ->where('item_id', $itemId)
            ->whereHas('transaction', fn ($q) => $q
                ->where('module', 'yarn')
                ->where('screen_slug', 'issuance')
                ->where('grey_conversion_contract_id', $greyContractId)
                ->where('account_id', $accountId))
            ->orderByDesc('id')
            ->value('rate');
    }

    /**
     * @return array<string, mixed>
     */
    private function mapGreyContract(GreyConversionContract $c): array
    {
        $issued = $this->issuedBagsOnGreyContract((int) $c->id);
        $required = (float) $c->required_bags;
        $remaining = max(0, $required - $issued);

        return [
            'id' => $c->id,
            'account_id' => $c->account_id,
            'contract_no' => $c->contract_no,
            'contract_code' => $c->contract_code,
            'contract_type' => $c->contract_type,
            'contract_date' => $c->contract_date?->format('Y-m-d'),
            'grey_quality_no' => $c->quality?->quality_no,
            'grey_quality_name' => $c->quality?->quality_name,
            'required_bags' => $required,
            'issued_bags' => round($issued, 4),
            'remaining_bags' => round($remaining, 4),
            'remarks' => $c->remarks,
            'qty_mtr' => $c->qty_mtr,
            'lov_label' => implode(' | ', array_filter([
                $c->quality?->quality_no,
                $c->contract_date?->format('d-m-Y'),
                $c->contract_code ?? $c->contract_no,
                'Req: ' . number_format($required, 2),
                'Issued: ' . number_format($issued, 2),
                'Bal: ' . number_format($remaining, 2),
            ])),
        ];
    }
}
