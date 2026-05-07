<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Voucher;

class PostingService
{
    public function postVoucher(Voucher $voucher, int $userId): Voucher
    {
        $voucher->update([
            'status' => 'posted',
            'posted_by' => $userId,
            'posted_at' => now(),
        ]);

        return $voucher->fresh();
    }

    public function postInventoryTransaction(InventoryTransaction $transaction): InventoryTransaction
    {
        $transaction->update([
            'status' => 'posted',
        ]);

        return $transaction->fresh();
    }
}

