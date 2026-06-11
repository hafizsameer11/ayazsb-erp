<?php

namespace App\Services;

class WeavingTransactionTotalsService
{
    /**
     * @param list<array{qty?: mixed, rate?: mixed, amount?: mixed, meta?: array}> $lines
     * @return array{total_qty: float, total_amount: float, lines: list<array<string, mixed>>}
     */
    public function calculateLines(array $lines): array
    {
        $totalQty = 0.0;
        $totalAmount = 0.0;
        $normalized = [];

        foreach ($lines as $i => $line) {
            if (empty($line['item_id']) && empty($line['description']) && empty($line['qty']) && empty($line['amount'])) {
                continue;
            }

            $qty = (float) ($line['qty'] ?? 0);
            $rate = (float) ($line['rate'] ?? 0);
            $amount = (float) ($line['amount'] ?? ($qty * $rate));
            $totalQty += $qty;
            $totalAmount += $amount;

            $normalized[] = array_merge($line, [
                'line_no' => $i,
                'qty' => $qty,
                'rate' => $rate,
                'amount' => round($amount, 4),
            ]);
        }

        return [
            'total_qty' => round($totalQty, 4),
            'total_amount' => round($totalAmount, 4),
            'lines' => $normalized,
        ];
    }
}
