<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Voucher;
use App\Models\VoucherLine;
use App\Support\ErpDate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class YarnVoucherBridgeService
{
    public function __construct(
        private readonly VoucherNumberService $numberService,
        private readonly YarnAccountResolver $accounts,
    ) {}

    public function syncForTransaction(InventoryTransaction $transaction): Voucher
    {
        return DB::transaction(function () use ($transaction) {
            $transaction->loadMissing(['lines.item', 'account']);
            $screenConfig = config('yarn_vouchers.screens.' . $transaction->screen_slug);

            if ($screenConfig === null) {
                throw ValidationException::withMessages([
                    'voucher' => "Voucher mapping is not configured for {$transaction->screen_slug}.",
                ]);
            }

            $voucherLines = $this->buildVoucherLines($transaction, $screenConfig);
            if ($voucherLines === []) {
                throw ValidationException::withMessages([
                    'lines' => 'Transaction amount must be greater than zero to post to ledger.',
                ]);
            }

            $debitTotal = round(array_sum(array_column($voucherLines, 'debit')), 2);
            $creditTotal = round(array_sum(array_column($voucherLines, 'credit')), 2);
            if (abs($debitTotal - $creditTotal) > 0.01) {
                throw ValidationException::withMessages([
                    'voucher' => 'Generated yarn voucher is not balanced. Check yarn account mapping.',
                ]);
            }

            $existingVoucherId = (int) ($transaction->meta['voucher_id'] ?? 0);
            $voucher = $existingVoucherId > 0
                ? Voucher::query()->with('lines')->find($existingVoucherId)
                : null;

            $voucherType = strtoupper((string) ($transaction->meta['voucher_type'] ?? $screenConfig['voucher_type'] ?? 'JV'));
            $transDate = $transaction->trans_date?->format('Y-m-d') ?? now()->format('Y-m-d');
            $fy = $this->accounts->financialYearForDate($transDate);
            $yearCode = $fy?->year_code ?? now()->format('Y');

            if ($voucher) {
                $voucher->update([
                    'voucher_date' => $transDate,
                    'financial_year_id' => $fy?->id,
                    'remarks' => $this->voucherRemarks($transaction),
                    'total_debit' => $debitTotal,
                    'total_credit' => $creditTotal,
                    'total_amount' => $debitTotal,
                    'status' => 'posted',
                    'posted_by' => Auth::id(),
                    'posted_at' => now(),
                ]);
                $voucher->lines()->delete();
            } else {
                $voucher = Voucher::query()->create([
                    'module' => 'accounts',
                    'voucher_type' => $voucherType,
                    'voucher_number' => $this->numberService->next('accounts', $voucherType, $yearCode),
                    'voucher_date' => $transDate,
                    'financial_year_id' => $fy?->id,
                    'status' => 'posted',
                    'remarks' => $this->voucherRemarks($transaction),
                    'total_debit' => $debitTotal,
                    'total_credit' => $creditTotal,
                    'total_amount' => $debitTotal,
                    'created_by' => Auth::id(),
                    'posted_by' => Auth::id(),
                    'posted_at' => now(),
                ]);
            }

            foreach ($voucherLines as $line) {
                VoucherLine::query()->create([
                    'voucher_id' => $voucher->id,
                    ...$line,
                ]);
            }

            $transaction->update([
                'meta' => array_merge($transaction->meta ?? [], [
                    'voucher_id' => $voucher->id,
                    'voucher_num' => $voucher->voucher_number,
                    'voucher_date' => ErpDate::toStorage($voucher->voucher_date) ?? $transDate,
                    'voucher_type' => $voucher->voucher_type,
                    'voucher_posted' => true,
                ]),
            ]);

            return $voucher->fresh('lines.account');
        });
    }

    /**
     * @param  array<string, mixed>  $screenConfig
     * @return list<array<string, mixed>>
     */
    private function buildVoucherLines(InventoryTransaction $transaction, array $screenConfig): array
    {
        $amount = round((float) $transaction->total_amount, 2);
        if ($amount <= 0) {
            return [];
        }

        $description = trim((string) ($transaction->remarks ?: $transaction->trans_no));
        $lines = [];

        foreach ($screenConfig['entries'] as $entry) {
            $account = $this->accounts->requirePostable(
                $this->accounts->resolveSource($entry['source'], $transaction->account_id),
                match ($entry['source']) {
                    'party' => 'Party',
                    'yarn_stock' => 'Yarn stock',
                    'yarn_sales' => 'Yarn sales',
                    default => ucfirst(str_replace('_', ' ', $entry['source'])),
                },
            );

            $side = $entry['side'];
            $lines[] = [
                'account_id' => $account->id,
                'description' => $description !== '' ? $description : $transaction->trans_no,
                'amount' => $amount,
                'debit' => $side === 'debit' ? $amount : 0,
                'credit' => $side === 'credit' ? $amount : 0,
                'tag' => $transaction->screen_slug,
            ];
        }

        return $lines;
    }

    private function voucherRemarks(InventoryTransaction $transaction): string
    {
        return trim((string) ($transaction->remarks ?? '')) !== ''
            ? (string) $transaction->remarks
            : "Yarn {$transaction->screen_slug} {$transaction->trans_no}";
    }
}
