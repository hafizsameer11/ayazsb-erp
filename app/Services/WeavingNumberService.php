<?php

namespace App\Services;

use App\Models\WeavingProductionEntry;
use App\Models\WeavingSet;
use App\Models\WeavingTransaction;
use App\Models\VoucherSequence;

class WeavingNumberService
{
    public function nextTransaction(string $screenSlug): string
    {
        $sequence = VoucherSequence::query()->firstOrCreate(
            [
                'module' => 'weaving',
                'voucher_type' => strtoupper(substr(str_replace('-', '', $screenSlug), 0, 3)),
                'year_code' => now()->format('Y'),
            ],
            ['next_number' => 1]
        );

        do {
            $number = str_pad((string) $sequence->next_number, 5, '0', STR_PAD_LEFT);
            $candidate = 'WEA' . now()->format('Y') . $number;
            $exists = WeavingTransaction::query()->where('trans_no', $candidate)->exists();
            $sequence->increment('next_number');
            $sequence->refresh();
        } while ($exists);

        return $candidate;
    }

    public function nextSetNo(): string
    {
        $next = (int) WeavingSet::query()->withTrashed()->count() + 1;

        return 'SET' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    public function nextProductionDocNo(): string
    {
        $next = (int) WeavingProductionEntry::query()->count() + 1;

        return 'PDE' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
