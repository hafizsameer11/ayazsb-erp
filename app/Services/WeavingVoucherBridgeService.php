<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Voucher;
use App\Models\VoucherLine;
use App\Models\WeavingSet;
use App\Models\WeavingTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WeavingVoucherBridgeService
{
    public function __construct(
        private readonly VoucherNumberService $numberService,
        private readonly WeavingAccountResolver $accounts,
    ) {}

    public function generateForTransaction(WeavingTransaction $transaction): Voucher
    {
        return DB::transaction(function () use ($transaction) {
            $transaction->loadMissing(['lines.item', 'account']);

            $screenConfig = config('weaving_vouchers.screens.' . $transaction->screen_slug);
            if ($screenConfig === null) {
                throw ValidationException::withMessages([
                    'voucher' => "Voucher mapping is not configured for {$transaction->screen_slug}.",
                ]);
            }

            if (! empty($screenConfig['requires_party']) && ! $transaction->account_id) {
                throw ValidationException::withMessages([
                    'account_id' => 'Select a party sub-ledger account before generating a voucher.',
                ]);
            }

            if (! empty($screenConfig['requires_sizing_party'])) {
                $sizingPartyId = (int) ($transaction->meta['sizing_party_account_id'] ?? $transaction->account_id ?? 0);
                if ($sizingPartyId <= 0) {
                    throw ValidationException::withMessages([
                        'account_id' => 'Select a sizing party sub-ledger account before generating a voucher.',
                    ]);
                }
            }

            $voucherLines = $this->buildVoucherLines($transaction, $screenConfig);
            if ($voucherLines === []) {
                throw ValidationException::withMessages([
                    'lines' => 'Transaction amount must be greater than zero to generate a voucher.',
                ]);
            }

            $debitTotal = round(array_sum(array_column($voucherLines, 'debit')), 2);
            $creditTotal = round(array_sum(array_column($voucherLines, 'credit')), 2);
            if (abs($debitTotal - $creditTotal) > 0.01) {
                throw ValidationException::withMessages([
                    'voucher' => 'Generated voucher is not balanced. Check weaving GL account configuration.',
                ]);
            }

            $voucherType = strtoupper((string) ($transaction->meta['voucher_type'] ?? $screenConfig['voucher_type'] ?? 'JV'));
            $fy = $this->accounts->financialYearForDate($transaction->trans_date->format('Y-m-d'));
            $yearCode = $fy?->year_code ?? now()->format('Y');

            $voucher = Voucher::query()->create([
                'module' => 'weaving',
                'voucher_type' => strtoupper($voucherType),
                'voucher_number' => $this->numberService->next('weaving', strtoupper($voucherType), $yearCode),
                'voucher_date' => $transaction->trans_date,
                'financial_year_id' => $fy?->id,
                'status' => 'draft',
                'remarks' => $transaction->remarks ?? "Weaving {$transaction->screen_slug} {$transaction->trans_no}",
                'total_debit' => $debitTotal,
                'total_credit' => $creditTotal,
                'total_amount' => $debitTotal,
                'created_by' => Auth::id(),
            ]);

            foreach ($voucherLines as $line) {
                VoucherLine::query()->create([
                    'voucher_id' => $voucher->id,
                    ...$line,
                ]);
            }

            $transaction->update([
                'voucher_id' => $voucher->id,
                'meta' => array_merge($transaction->meta ?? [], [
                    'voucher_id' => $voucher->id,
                    'voucher_num' => $voucher->voucher_number,
                    'voucher_date' => $voucher->voucher_date?->format('Y-m-d'),
                    'voucher_type' => $voucher->voucher_type,
                    'voucher_posted' => false,
                ]),
            ]);

            return $voucher->fresh('lines.account');
        });
    }

    public function syncExisting(WeavingTransaction $transaction): Voucher
    {
        if (! $transaction->voucher_id) {
            return $this->generateForTransaction($transaction);
        }

        $voucher = Voucher::query()->with('lines')->find($transaction->voucher_id);
        if (! $voucher) {
            return $this->generateForTransaction($transaction);
        }

        $screenConfig = config('weaving_vouchers.screens.' . $transaction->screen_slug, []);
        $voucherLines = $this->buildVoucherLines($transaction->loadMissing(['lines.item', 'account']), $screenConfig);
        $debitTotal = round(array_sum(array_column($voucherLines, 'debit')), 2);
        $creditTotal = round(array_sum(array_column($voucherLines, 'credit')), 2);

        $voucher->update([
            'voucher_date' => $transaction->trans_date,
            'remarks' => $transaction->remarks ?? $voucher->remarks,
            'total_debit' => $debitTotal,
            'total_credit' => $creditTotal,
            'total_amount' => $debitTotal,
        ]);

        $voucher->lines()->delete();
        foreach ($voucherLines as $line) {
            VoucherLine::query()->create([
                'voucher_id' => $voucher->id,
                ...$line,
            ]);
        }

        $transaction->update([
            'meta' => array_merge($transaction->meta ?? [], [
                'voucher_num' => $voucher->voucher_number,
                'voucher_date' => $transaction->trans_date?->format('Y-m-d'),
                'voucher_type' => $voucher->voucher_type,
            ]),
        ]);

        return $voucher->fresh('lines.account');
    }

    public function generateForSet(WeavingSet $set): Voucher
    {
        return DB::transaction(function () use ($set) {
            $set->loadMissing(['sizingParty']);
            $screenConfig = config('weaving_vouchers.screens.set-receipt-details', []);
            $amount = round((float) ($set->meta['bill_amount'] ?? 0), 2);
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'meta.bill_amount' => 'Enter bill amount on the set receipt before generating a voucher.',
                ]);
            }

            if (! $set->sizing_party_account_id) {
                throw ValidationException::withMessages([
                    'sizing_party_account_id' => 'Select a sizing party sub-ledger account before generating a voucher.',
                ]);
            }

            $meta = ['sizing_party_account_id' => $set->sizing_party_account_id];
            $debitAccount = $this->accounts->requirePostable(
                $this->accounts->resolveSource('sizing_expense', null, $meta),
                'Sizing expense'
            );
            $creditAccount = $this->accounts->requirePostable(
                $this->accounts->resolveSource('sizing_party', $set->sizing_party_account_id, $meta),
                'Sizing party'
            );

            $voucherType = strtoupper((string) ($set->meta['voucher_type'] ?? $screenConfig['voucher_type'] ?? 'BPV'));
            $voucherDate = $set->receipt_date ?? $set->entry_date ?? now();
            $fy = $this->accounts->financialYearForDate($voucherDate->format('Y-m-d'));
            $yearCode = $fy?->year_code ?? now()->format('Y');

            $voucher = Voucher::query()->create([
                'module' => 'weaving',
                'voucher_type' => $voucherType,
                'voucher_number' => $this->numberService->next('weaving', $voucherType, $yearCode),
                'voucher_date' => $voucherDate,
                'financial_year_id' => $fy?->id,
                'status' => 'draft',
                'remarks' => "Set receipt {$set->set_no}",
                'total_debit' => $amount,
                'total_credit' => $amount,
                'total_amount' => $amount,
                'created_by' => Auth::id(),
            ]);

            foreach ([[$debitAccount, 'debit'], [$creditAccount, 'credit']] as [$account, $side]) {
                VoucherLine::query()->create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $account->id,
                    'description' => "{$set->set_no} — {$account->code}",
                    'amount' => $amount,
                    'debit' => $side === 'debit' ? $amount : 0,
                    'credit' => $side === 'credit' ? $amount : 0,
                    'tag' => 'set-receipt-details',
                ]);
            }

            $set->update([
                'voucher_id' => $voucher->id,
                'meta' => array_merge($set->meta ?? [], [
                    'voucher_id' => $voucher->id,
                    'voucher_num' => $voucher->voucher_number,
                    'voucher_date' => $voucher->voucher_date?->format('Y-m-d'),
                    'voucher_type' => $voucher->voucher_type,
                ]),
            ]);

            return $voucher->fresh('lines.account');
        });
    }

    public function syncExistingSet(WeavingSet $set): Voucher
    {
        if ($set->voucher_id) {
            $set->voucher?->lines()->delete();
            $set->voucher?->delete();
            $set->update(['voucher_id' => null]);
        }

        return $this->generateForSet($set);
    }

    /**
     * @param  array<string, mixed>  $screenConfig
     * @return list<array<string, mixed>>
     */
    private function buildVoucherLines(WeavingTransaction $transaction, array $screenConfig): array
    {
        $amount = round((float) $transaction->total_amount, 2);
        if ($amount <= 0) {
            return [];
        }

        $entries = $screenConfig['entries'] ?? [];
        $lines = [];
        $lineAmounts = $this->lineAmountBuckets($transaction);
        $lineMeta = array_merge($transaction->meta ?? [], [
            'sizing_party_account_id' => $transaction->meta['sizing_party_account_id'] ?? $transaction->account_id,
        ]);

        foreach ($entries as $entry) {
            $source = (string) ($entry['source'] ?? '');
            $side = (string) ($entry['side'] ?? 'debit');
            $perLine = ! empty($entry['per_line']);

            if ($perLine) {
                foreach ($lineAmounts as $bucket) {
                    if ($bucket['amount'] <= 0) {
                        continue;
                    }
                    $account = $this->accounts->resolveSource(
                        $source,
                        $transaction->account_id,
                        array_merge($lineMeta, $bucket['meta']),
                        $transaction->department_id,
                    );
                    $account = $this->accounts->requirePostable($account, ucfirst(str_replace('_', ' ', $source)));
                    $lines[] = $this->voucherLinePayload($account, $bucket['amount'], $side, $transaction, $bucket['label']);
                }
                continue;
            }

            $account = $this->accounts->resolveSource(
                $source,
                $transaction->account_id,
                $lineMeta,
                $transaction->department_id,
            );
            $account = $this->accounts->requirePostable($account, ucfirst(str_replace('_', ' ', $source)));
            $lines[] = $this->voucherLinePayload($account, $amount, $side, $transaction);
        }

        return $lines;
    }

    /**
     * @return list<array{amount: float, meta: array<string, mixed>, label: string}>
     */
    private function lineAmountBuckets(WeavingTransaction $transaction): array
    {
        $buckets = [];
        foreach ($transaction->lines as $line) {
            $lineAmount = round((float) $line->amount, 2);
            if ($lineAmount <= 0) {
                continue;
            }
            $buckets[] = [
                'amount' => $lineAmount,
                'meta' => $line->meta ?? [],
                'label' => $line->item?->name ?? $line->description ?? $transaction->trans_no,
            ];
        }

        if ($buckets === [] && (float) $transaction->total_amount > 0) {
            $buckets[] = [
                'amount' => round((float) $transaction->total_amount, 2),
                'meta' => [],
                'label' => $transaction->trans_no,
            ];
        }

        return $buckets;
    }

    private function voucherLinePayload(
        Account $account,
        float $amount,
        string $side,
        WeavingTransaction $transaction,
        ?string $label = null,
    ): array {
        $amount = round($amount, 2);
        $description = trim(($label ?? $transaction->trans_no) . ' — ' . $account->code);

        return [
            'account_id' => $account->id,
            'description' => $description,
            'qty' => (float) $transaction->total_qty,
            'rate' => $transaction->total_qty > 0 ? round($amount / (float) $transaction->total_qty, 4) : 0,
            'amount' => $amount,
            'debit' => $side === 'debit' ? $amount : 0,
            'credit' => $side === 'credit' ? $amount : 0,
            'tag' => $transaction->screen_slug,
        ];
    }
}
