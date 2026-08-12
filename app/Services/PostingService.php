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
        if ($transaction->status !== 'posted') {
            $transaction->update([
                'status' => 'posted',
            ]);
        }

        $transaction = $transaction->fresh(['lines.item', 'account']);
        $this->syncInventorySideEffects($transaction);

        return $transaction->fresh();
    }

    private function syncInventorySideEffects(InventoryTransaction $transaction): void
    {
        if ($transaction->module !== 'yarn') {
            return;
        }

        if (config('yarn_vouchers.screens.' . $transaction->screen_slug) === null) {
            return;
        }

        app(YarnVoucherBridgeService::class)->syncForTransaction($transaction);
    }
}
