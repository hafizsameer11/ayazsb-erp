<?php

namespace App\Services;

class GreyTransactionTotalsService
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, float>
     */
    public function calculate(array $input): array
    {
        $thanQty = (float) ($input['than_qty'] ?? 0);
        $longQty = (float) ($input['long_qty'] ?? 0);
        $shortQty = (float) ($input['short_qty'] ?? 0);
        $netQty = max(0, $thanQty - $longQty + $shortQty);
        if (isset($input['net_qty']) && (float) $input['net_qty'] > 0) {
            $netQty = (float) $input['net_qty'];
        }

        $greyRate = (float) ($input['grey_rate_mtr'] ?? 0);
        $commissionPercent = (float) ($input['commission_percent'] ?? 0);
        $brokeryRate = (float) ($input['brokery_rate'] ?? 0);
        $checkerRate = (float) ($input['checker_rate_mtr'] ?? 0);
        $munshiana = (float) ($input['munshiana'] ?? 0);

        $totalGross = round($netQty * $greyRate, 2);
        $totalCommission = round($totalGross * $commissionPercent / 100, 2);
        $totalBrokery = $brokeryRate > 0 && $brokeryRate < 100
            ? round($totalGross * $brokeryRate / 100, 2)
            : round($brokeryRate, 2);
        $totalCheckary = round($netQty * $checkerRate, 2);
        $totalMunshiana = round($munshiana, 2);
        $netAmount = round($totalGross + $totalCommission + $totalBrokery + $totalCheckary + $totalMunshiana, 2);

        return [
            'net_qty' => $netQty,
            'total_gross_amount' => $totalGross,
            'total_commission' => $totalCommission,
            'total_brokery' => $totalBrokery,
            'total_checkary' => $totalCheckary,
            'total_munshiana' => $totalMunshiana,
            'total_net_amount' => $netAmount,
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, float>
     */
    public function calculateContract(array $input): array
    {
        $qty = (float) ($input['qty_mtr'] ?? 0);
        $perMtr = (float) ($input['per_mtr_rate'] ?? 0);
        $brokeryRate = (float) ($input['brokery_rate'] ?? 0);
        $checkerRate = (float) ($input['checker_rate'] ?? 0);
        $munshiana = (float) ($input['munshiana'] ?? 0);

        $totalAmount = round($qty * $perMtr, 2);
        $totalBrokery = round($totalAmount * $brokeryRate / 100, 2);
        $totalCheckery = round($totalAmount * $checkerRate / 100, 2);
        $totalMunshiana = round($munshiana, 2);
        $net = round($totalAmount + $totalBrokery + $totalCheckery + $totalMunshiana, 2);

        return [
            'total_amount' => $totalAmount,
            'total_brokery' => $totalBrokery,
            'total_checkery' => $totalCheckery,
            'total_munshiana' => $totalMunshiana,
            'total_net_amount' => $net,
        ];
    }
}
