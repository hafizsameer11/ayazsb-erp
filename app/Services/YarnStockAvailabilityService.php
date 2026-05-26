<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionLine;
use App\Models\Item;

class YarnStockAvailabilityService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function yarnItemsPayload(): array
    {
        $inScreens = [
            'purchase-contract-wise',
            'purchase-without-contract',
            'opening',
            'receipt-processed',
            'receipt-processed-auto',
            'gain-shortage',
        ];
        $outScreens = ['issuance', 'sale-contract-wise', 'sale-without-contract', 'godown-transfer'];

        $items = Item::query()->where('module', 'yarn')->where('is_active', true)->orderBy('code')->get();
        $payload = [];

        foreach ($items as $item) {
            $inLbs = (float) InventoryTransactionLine::query()
                ->where('item_id', $item->id)
                ->whereHas('transaction', fn ($q) => $q->where('module', 'yarn')->whereIn('screen_slug', $inScreens))
                ->sum('weight_lbs');

            $outLbs = (float) InventoryTransactionLine::query()
                ->where('item_id', $item->id)
                ->whereHas('transaction', fn ($q) => $q->where('module', 'yarn')->whereIn('screen_slug', $outScreens))
                ->sum('weight_lbs');

            $availableLbs = max(0, $inLbs - $outLbs);
            $packingSize = (float) ($item->pack_size_cones ?: 0);
            $availableBags = $packingSize > 0 ? $availableLbs / 100 : $availableLbs / 100;
            $availableCones = $packingSize > 0 ? $availableLbs / (100 / $packingSize) : 0;

            $lastPurchaseRate = (float) InventoryTransactionLine::query()
                ->where('item_id', $item->id)
                ->whereHas('transaction', fn ($q) => $q->where('module', 'yarn')->whereIn('screen_slug', ['purchase-contract-wise', 'purchase-without-contract']))
                ->orderByDesc('id')
                ->value('rate');

            $payload[] = [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'pack_size_cones' => $item->pack_size_cones,
                'packing_weight' => $item->packing_weight,
                'available_bags' => round($availableBags, 4),
                'available_cones' => round($availableCones, 4),
                'purchase_rate' => $lastPurchaseRate,
                'lov_label' => implode(' | ', array_filter([
                    $item->code,
                    'Bags: ' . number_format($availableBags, 2),
                    'Cones: ' . number_format($availableCones, 2),
                    $lastPurchaseRate > 0 ? 'Rate: ' . number_format($lastPurchaseRate, 2) : null,
                ])),
            ];
        }

        return $payload;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function issuanceOptionsPayload(): array
    {
        return InventoryTransaction::query()
            ->where('module', 'yarn')
            ->where('screen_slug', 'issuance')
            ->with(['account', 'greyConversionContract.quality', 'lines.item'])
            ->orderByDesc('trans_date')
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->map(fn (InventoryTransaction $t) => [
                'id' => $t->id,
                'trans_no' => $t->trans_no,
                'trans_date' => $t->trans_date?->format('Y-m-d'),
                'account_id' => $t->account_id,
                'account_name' => $t->account?->name,
                'grey_conversion_contract_id' => $t->grey_conversion_contract_id,
                'contract_code' => $t->greyConversionContract?->contract_code ?? $t->greyConversionContract?->contract_no,
                'grey_quality_no' => $t->greyConversionContract?->quality?->quality_no,
                'lines' => $t->lines->map(fn ($line) => [
                    'item_id' => $line->item_id,
                    'item_code' => $line->item?->code,
                    'item_name' => $line->item?->name,
                    'yarn_type' => $line->meta['yarn_type'] ?? 'any',
                    'packing_size' => $line->meta['packing_size'] ?? $line->item?->pack_size_cones,
                    'qty' => $line->qty,
                    'cones' => $line->meta['no_of_cones'] ?? $line->meta['cones'] ?? 0,
                    'weight_lbs' => $line->weight_lbs,
                    'rate' => $line->rate,
                    'amount' => $line->amount,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Account IDs that have at least one yarn issuance.
     *
     * @return list<int>
     */
    public function issuancePartyAccountIds(): array
    {
        return InventoryTransaction::query()
            ->where('module', 'yarn')
            ->where('screen_slug', 'issuance')
            ->whereNotNull('account_id')
            ->distinct()
            ->pluck('account_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }
}
