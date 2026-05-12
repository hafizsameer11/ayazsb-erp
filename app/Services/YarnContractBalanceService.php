<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionLine;
use App\Models\YarnContract;
use Illuminate\Database\Eloquent\Builder;

class YarnContractBalanceService
{
    /**
     * @return array<string, float>
     */
    public function snapshot(YarnContract $contract): array
    {
        $purchased = $this->sumWeight($this->contractTransactions($contract, ['purchase-contract-wise']));
        $sold = $this->sumWeight($this->contractTransactions($contract, ['sale-contract-wise']));
        $issued = $this->sumWeight($this->contractTransactions($contract, ['issuance']));
        $returned = $this->sumWeight($this->contractTransactions($contract, ['issuance-return']));
        $transferredOut = $this->sumWeight(
            $this->postedYarnTransactions()
                ->where('screen_slug', 'issuance-transfer')
                ->where('from_yarn_contract_id', $contract->id)
        );
        $transferredIn = $this->sumWeight(
            $this->postedYarnTransactions()
                ->where('screen_slug', 'issuance-transfer')
                ->where('to_yarn_contract_id', $contract->id)
        );
        $gain = $this->sumGainShortage($contract, 'gain');
        $shortage = $this->sumGainShortage($contract, 'shortage');

        $stockIn = $purchased + $returned + $transferredIn + $gain;
        $stockOut = $sold + $issued + $transferredOut + $shortage;

        return [
            'contracted_weight_lbs' => (float) $contract->weight_lbs,
            'purchased_weight_lbs' => $purchased,
            'sold_weight_lbs' => $sold,
            'issued_weight_lbs' => $issued,
            'returned_weight_lbs' => $returned,
            'transferred_in_weight_lbs' => $transferredIn,
            'transferred_out_weight_lbs' => $transferredOut,
            'gain_weight_lbs' => $gain,
            'shortage_weight_lbs' => $shortage,
            'stock_in_weight_lbs' => $stockIn,
            'stock_out_weight_lbs' => $stockOut,
            'available_weight_lbs' => $stockIn - $stockOut,
            'warp_issued_weight_lbs' => $this->sumWeight($this->contractTransactions($contract, ['issuance']), 'WARP'),
            'weft_issued_weight_lbs' => $this->sumWeight($this->contractTransactions($contract, ['issuance']), 'WEFT'),
        ];
    }

    public function availableWeight(YarnContract $contract): float
    {
        return $this->snapshot($contract)['available_weight_lbs'];
    }

    public function transactionWeight(array $lines): float
    {
        return array_reduce($lines, function (float $total, array $line): float {
            $weight = (float) ($line['weight_lbs'] ?? 0);
            if ($weight <= 0) {
                $weight = (float) ($line['qty'] ?? 0);
            }

            return $total + $weight;
        }, 0.0);
    }

    /**
     * @param list<string> $screens
     */
    private function contractTransactions(YarnContract $contract, array $screens): Builder
    {
        return $this->postedYarnTransactions()
            ->whereIn('screen_slug', $screens)
            ->where('yarn_contract_id', $contract->id);
    }

    private function postedYarnTransactions(): Builder
    {
        return InventoryTransaction::query()
            ->with('lines')
            ->where('module', 'yarn')
            ->where('status', 'posted');
    }

    private function sumGainShortage(YarnContract $contract, string $type): float
    {
        return $this->sumWeight(
            $this->contractTransactions($contract, ['gain-shortage']),
            null,
            static fn (InventoryTransactionLine $line): bool => strtolower((string) data_get($line->meta, 'adjustment_type')) === $type
        );
    }

    private function sumWeight(Builder $query, ?string $yarnType = null, ?callable $filter = null): float
    {
        return $query->get()->sum(function (InventoryTransaction $transaction) use ($yarnType, $filter): float {
            return $transaction->lines->sum(function (InventoryTransactionLine $line) use ($yarnType, $filter): float {
                if ($yarnType !== null && strtoupper((string) data_get($line->meta, 'yarn_type')) !== $yarnType) {
                    return 0.0;
                }

                if ($filter !== null && ! $filter($line)) {
                    return 0.0;
                }

                $weight = (float) $line->weight_lbs;

                return $weight > 0 ? $weight : (float) $line->qty;
            });
        });
    }
}
