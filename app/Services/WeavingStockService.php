<?php

namespace App\Services;

use App\Models\WeavingStockBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WeavingStockService
{
    public function available(string $stockPool, int $itemId): float
    {
        return (float) WeavingStockBalance::query()
            ->where('stock_pool', $stockPool)
            ->where('item_id', $itemId)
            ->value('qty');
    }

    public function applyMovement(string $stockPool, int $itemId, float $delta): void
    {
        if ($delta == 0.0) {
            return;
        }

        DB::transaction(function () use ($stockPool, $itemId, $delta) {
            $balance = WeavingStockBalance::query()->firstOrCreate(
                ['stock_pool' => $stockPool, 'item_id' => $itemId],
                ['qty' => 0]
            );

            $newQty = round((float) $balance->qty + $delta, 4);
            if ($newQty < -0.0001) {
                throw ValidationException::withMessages([
                    'lines' => "Insufficient stock in {$stockPool} for item #{$itemId}.",
                ]);
            }

            $balance->update(['qty' => max(0, $newQty)]);
        });
    }

    /**
     * @param list<array{item_id: int, qty: float}> $lines
     */
    public function applyStoreIssue(array $lines): void
    {
        foreach ($lines as $line) {
            $this->applyMovement('store', (int) $line['item_id'], -abs((float) $line['qty']));
        }
    }

    /**
     * @param list<array{item_id: int, qty: float}> $lines
     */
    public function applyStoreReceipt(array $lines): void
    {
        foreach ($lines as $line) {
            $this->applyMovement('store', (int) $line['item_id'], abs((float) $line['qty']));
        }
    }

    /**
     * @param list<array{item_id: int, qty: float}> $lines
     */
    public function applyYarnMovement(string $screenSlug, array $lines, bool $reverse = false): void
    {
        $movement = \App\Support\WeavingModule::yarnMovementForScreen($screenSlug);
        $sign = $reverse ? -1 : 1;

        foreach ($lines as $line) {
            $qty = abs((float) ($line['qty'] ?? 0));
            if ($qty <= 0) {
                continue;
            }
            if ($movement['out']) {
                $this->applyMovement($movement['out'], (int) $line['item_id'], -$sign * $qty);
            }
            if ($movement['in']) {
                $this->applyMovement($movement['in'], (int) $line['item_id'], $sign * $qty);
            }
        }
    }

    /**
     * @param list<array{item_id: int, qty: float}> $lines
     */
    public function applyYarnAdjustment(string $stockPool, array $lines): void
    {
        foreach ($lines as $line) {
            $this->applyMovement($stockPool, (int) $line['item_id'], (float) $line['qty']);
        }
    }

    /**
     * @return array<int, float>
     */
    public function storeStockMap(): array
    {
        return WeavingStockBalance::query()
            ->where('stock_pool', 'store')
            ->pluck('qty', 'item_id')
            ->map(fn ($q) => (float) $q)
            ->all();
    }

    /**
     * @return array<int, float>
     */
    public function yarnStockMap(string $pool): array
    {
        return WeavingStockBalance::query()
            ->where('stock_pool', $pool)
            ->pluck('qty', 'item_id')
            ->map(fn ($q) => (float) $q)
            ->all();
    }
}
