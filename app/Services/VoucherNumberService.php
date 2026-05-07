<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Voucher;
use App\Models\VoucherSequence;

class VoucherNumberService
{
    public function next(string $module, string $voucherType, string $yearCode): string
    {
        $sequence = VoucherSequence::query()->firstOrCreate(
            [
                'module' => $module,
                'voucher_type' => $voucherType,
                'year_code' => $yearCode,
            ],
            ['next_number' => 1]
        );

        do {
            $number = str_pad((string) $sequence->next_number, 5, '0', STR_PAD_LEFT);
            $candidate = strtoupper(substr($voucherType, 0, 3)) . $yearCode . $number;
            $exists = Voucher::query()
                ->where('module', $module)
                ->where('voucher_type', strtoupper(substr($voucherType, 0, 3)))
                ->where('voucher_number', $candidate)
                ->exists();
            $sequence->increment('next_number');
            $sequence->refresh();
        } while ($exists);

        return $candidate;
    }

    public function nextTransaction(string $module, string $screenSlug): string
    {
        $sequence = VoucherSequence::query()->firstOrCreate(
            [
                'module' => $module,
                'voucher_type' => strtoupper(substr($screenSlug, 0, 3)),
                'year_code' => now()->format('Y'),
            ],
            ['next_number' => 1]
        );

        do {
            $number = str_pad((string) $sequence->next_number, 5, '0', STR_PAD_LEFT);
            $candidate = strtoupper(substr($module, 0, 3)) . now()->format('Y') . $number;
            $exists = InventoryTransaction::query()
                ->where('module', $module)
                ->where('trans_no', $candidate)
                ->exists();
            $sequence->increment('next_number');
            $sequence->refresh();
        } while ($exists);

        return $candidate;
    }
}

