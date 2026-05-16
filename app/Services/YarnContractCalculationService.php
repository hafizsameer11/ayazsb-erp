<?php

namespace App\Services;

class YarnContractCalculationService
{
    private const LBS_PER_BAG = 100;

    private const LBS_PER_KG = 2.20462;

    /**
     * @param array<string, mixed> $input
     * @return array<string, float>
     */
    public function calculate(array $input): array
    {
        $bags = (float) ($input['no_of_bags'] ?? $input['quantity'] ?? 0);
        $cones = (float) ($input['no_of_cones'] ?? 0);
        $packingSize = (float) ($input['packing_size'] ?? 0);
        $rate = (float) ($input['rate'] ?? 0);
        $commissionPercent = (float) ($input['commission_percent'] ?? 0);
        $brokeryPercent = (float) ($input['brokery_percent'] ?? 0);

        $totalLbs = $bags * self::LBS_PER_BAG;
        if ($cones > 0 && $packingSize > 0) {
            $totalLbs += $cones * (self::LBS_PER_BAG / $packingSize);
        }

        $totalKgs = $totalLbs > 0 ? round($totalLbs / self::LBS_PER_KG, 4) : 0.0;
        $totalAmount = round($rate * $totalLbs, 2);
        $totalCommission = round($totalAmount * $commissionPercent / 100, 2);
        $totalBrokery = round($totalAmount * $brokeryPercent / 100, 2);
        $totalNetAmount = round($totalAmount + $totalBrokery + $totalCommission, 2);

        return [
            'weight_lbs' => round($totalLbs, 4),
            'total_kgs' => $totalKgs,
            'total_amount' => $totalAmount,
            'total_commission' => $totalCommission,
            'total_brokery' => $totalBrokery,
            'total_net_amount' => $totalNetAmount,
        ];
    }

    public function contractCodePrefix(string $direction): string
    {
        return $direction === 'sale' ? 'YSC' : 'YPC';
    }

    public function buildContractCode(string $direction, string $contractNo): string
    {
        return $this->contractCodePrefix($direction) . $contractNo;
    }
}
