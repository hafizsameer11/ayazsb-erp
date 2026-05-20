<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountOpening;
use App\Models\VoucherLine;
use App\Support\ErpDate;
use Illuminate\Support\Collection;

class AccountsLedgerService
{
    /** Legacy suspense / control account excluded from customer-facing ledger (Oracle ledger view). */
    public const EXCLUDED_ACCOUNT_CODES = ['010010001'];

    public function findAccount(?int $accountId, ?string $accountQuery): ?Account
    {
        if ($accountId) {
            return Account::query()
                ->postable()
                ->whereNotIn('code', self::EXCLUDED_ACCOUNT_CODES)
                ->find($accountId);
        }

        $query = trim((string) $accountQuery);
        if ($query === '') {
            return null;
        }

        return Account::query()
            ->postable()
            ->whereNotIn('code', self::EXCLUDED_ACCOUNT_CODES)
            ->where(function ($builder) use ($query): void {
                $builder
                    ->where('code', $query)
                    ->orWhere('code', 'like', $query . '%')
                    ->orWhere('name', 'like', '%' . strtoupper($query) . '%');
            })
            ->orderBy('code')
            ->first();
    }

    /**
     * @return array{
     *     account: Account,
     *     from_date: ?string,
     *     to_date: ?string,
     *     from_display: string,
     *     to_display: string,
     *     opening_balance: float,
     *     opening_side: string,
     *     closing_balance: float,
     *     closing_side: string,
     *     rows: list<array<string, mixed>>,
     *     totals: array{debit: float, credit: float, count: int}
     * }
     */
    public function accountStatement(Account $account, ?string $fromDate, ?string $toDate): array
    {
        $from = ErpDate::parse($fromDate);
        $to = ErpDate::parse($toDate);

        $opening = $from
            ? $this->netBalanceBefore($account->id, $from->format(ErpDate::STORAGE_FORMAT))
            : 0.0;

        $entries = $this->collectEntries($account->id, $from?->format(ErpDate::STORAGE_FORMAT), $to?->format(ErpDate::STORAGE_FORMAT));

        $rows = [];
        $running = $opening;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        $rows[] = $this->formatRunningRow([
            'date' => $from?->format(ErpDate::STORAGE_FORMAT) ?? $entries->first()['date'] ?? now()->format(ErpDate::STORAGE_FORMAT),
            'type' => 'OBV',
            'voucher_no' => '',
            'narration' => 'OPENING BALANCE',
            'debit' => $opening > 0 ? $opening : 0.0,
            'credit' => $opening < 0 ? abs($opening) : 0.0,
            'contract_no' => '',
        ], $running, true);

        foreach ($entries as $entry) {
            $debit = (float) ($entry['debit'] ?? 0);
            $credit = (float) ($entry['credit'] ?? 0);
            $running += $debit - $credit;
            $totalDebit += $debit;
            $totalCredit += $credit;
            $rows[] = $this->formatRunningRow($entry, $running, false);
        }

        return [
            'account' => $account,
            'from_date' => $from?->format(ErpDate::STORAGE_FORMAT),
            'to_date' => $to?->format(ErpDate::STORAGE_FORMAT),
            'from_display' => $from ? ErpDate::display($from) : 'Beginning',
            'to_display' => $to ? ErpDate::display($to) : ErpDate::todayDisplay(),
            'opening_balance' => abs($opening),
            'opening_side' => $this->balanceSide($opening),
            'closing_balance' => abs($running),
            'closing_side' => $this->balanceSide($running),
            'rows' => $rows,
            'totals' => [
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'count' => max(0, count($rows) - 1),
            ],
        ];
    }

    /**
     * @return array{
     *     from_display: string,
     *     to_display: string,
     *     rows: list<array<string, mixed>>,
     *     totals: array{opening_debit: float, opening_credit: float, debit: float, credit: float, closing_debit: float, closing_credit: float}
     * }
     */
    public function trialBalance(?string $fromDate, ?string $toDate): array
    {
        $from = ErpDate::parse($fromDate);
        $to = ErpDate::parse($toDate);
        $fromStorage = $from?->format(ErpDate::STORAGE_FORMAT);
        $toStorage = $to?->format(ErpDate::STORAGE_FORMAT);

        $accounts = Account::query()
            ->postable()
            ->whereNotIn('code', self::EXCLUDED_ACCOUNT_CODES)
            ->orderBy('code')
            ->get();

        $rows = [];
        $totals = [
            'opening_debit' => 0.0,
            'opening_credit' => 0.0,
            'debit' => 0.0,
            'credit' => 0.0,
            'closing_debit' => 0.0,
            'closing_credit' => 0.0,
        ];

        foreach ($accounts as $account) {
            $openingNet = $fromStorage
                ? $this->netBalanceBefore($account->id, $fromStorage)
                : 0.0;
            [$periodDebit, $periodCredit] = $this->periodTotals($account->id, $fromStorage, $toStorage);
            $closingNet = $openingNet + $periodDebit - $periodCredit;

            if (
                abs($openingNet) < 0.009
                && abs($periodDebit) < 0.009
                && abs($periodCredit) < 0.009
                && abs($closingNet) < 0.009
            ) {
                continue;
            }

            $openingDebit = $openingNet > 0 ? $openingNet : 0.0;
            $openingCredit = $openingNet < 0 ? abs($openingNet) : 0.0;
            $closingDebit = $closingNet > 0 ? $closingNet : 0.0;
            $closingCredit = $closingNet < 0 ? abs($closingNet) : 0.0;

            $rows[] = [
                'account_code' => $account->code,
                'account_name' => $account->name,
                'opening_debit' => $openingDebit,
                'opening_credit' => $openingCredit,
                'debit' => $periodDebit,
                'credit' => $periodCredit,
                'closing_debit' => $closingDebit,
                'closing_credit' => $closingCredit,
            ];

            $totals['opening_debit'] += $openingDebit;
            $totals['opening_credit'] += $openingCredit;
            $totals['debit'] += $periodDebit;
            $totals['credit'] += $periodCredit;
            $totals['closing_debit'] += $closingDebit;
            $totals['closing_credit'] += $closingCredit;
        }

        return [
            'from_display' => $from ? ErpDate::display($from) : 'Beginning',
            'to_display' => $to ? ErpDate::display($to) : ErpDate::todayDisplay(),
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    /**
     * @return array{
     *     account: ?Account,
     *     from_display: string,
     *     to_display: string,
     *     rows: list<array<string, mixed>>,
     *     totals: array{debit: float, credit: float, count: int}
     * }
     */
    public function generalLedger(?Account $account, ?string $fromDate, ?string $toDate): array
    {
        $from = ErpDate::parse($fromDate);
        $to = ErpDate::parse($toDate);

        $entries = $this->collectEntries(
            $account?->id,
            $from?->format(ErpDate::STORAGE_FORMAT),
            $to?->format(ErpDate::STORAGE_FORMAT),
        );

        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($entries as $entry) {
            $debit = (float) ($entry['debit'] ?? 0);
            $credit = (float) ($entry['credit'] ?? 0);
            $totalDebit += $debit;
            $totalCredit += $credit;
            $rows[] = [
                'date' => ErpDate::display($entry['date']),
                'account_code' => $entry['account_code'],
                'account_name' => $entry['account_name'],
                'type' => $entry['type'],
                'voucher_no' => $entry['voucher_no'],
                'narration' => $entry['narration'],
                'contract_no' => $entry['contract_no'],
                'debit' => $debit > 0 ? $debit : null,
                'credit' => $credit > 0 ? $credit : null,
            ];
        }

        return [
            'account' => $account,
            'from_display' => $from ? ErpDate::display($from) : 'Beginning',
            'to_display' => $to ? ErpDate::display($to) : ErpDate::todayDisplay(),
            'rows' => $rows,
            'totals' => [
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'count' => count($rows),
            ],
        ];
    }

    public function netBalanceBefore(int $accountId, string $beforeDate): float
    {
        [$debit, $credit] = $this->sumBeforeDate($accountId, $beforeDate);

        return $debit - $credit;
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function periodTotals(int $accountId, ?string $fromDate, ?string $toDate): array
    {
        $entries = $this->collectEntries($accountId, $fromDate, $toDate);
        $debit = 0.0;
        $credit = 0.0;

        foreach ($entries as $entry) {
            $debit += (float) ($entry['debit'] ?? 0);
            $credit += (float) ($entry['credit'] ?? 0);
        }

        return [$debit, $credit];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function sumBeforeDate(int $accountId, string $beforeDate): array
    {
        $voucherTotals = VoucherLine::query()
            ->join('vouchers', 'vouchers.id', '=', 'voucher_lines.voucher_id')
            ->join('accounts', 'accounts.id', '=', 'voucher_lines.account_id')
            ->whereNull('vouchers.deleted_at')
            ->where('vouchers.module', 'accounts')
            ->where('vouchers.status', 'posted')
            ->where('voucher_lines.account_id', $accountId)
            ->whereDate('vouchers.voucher_date', '<', $beforeDate)
            ->whereNotIn('accounts.code', self::EXCLUDED_ACCOUNT_CODES)
            ->selectRaw('COALESCE(SUM(voucher_lines.debit), 0) as total_debit, COALESCE(SUM(voucher_lines.credit), 0) as total_credit')
            ->first();

        $openingTotals = AccountOpening::query()
            ->join('accounts', 'accounts.id', '=', 'account_openings.account_id')
            ->whereNull('account_openings.deleted_at')
            ->where('account_openings.account_id', $accountId)
            ->whereDate('account_openings.voucher_date', '<', $beforeDate)
            ->whereNotIn('accounts.code', self::EXCLUDED_ACCOUNT_CODES)
            ->selectRaw('COALESCE(SUM(account_openings.debit), 0) as total_debit, COALESCE(SUM(account_openings.credit), 0) as total_credit')
            ->first();

        $debit = (float) ($voucherTotals->total_debit ?? 0) + (float) ($openingTotals->total_debit ?? 0);
        $credit = (float) ($voucherTotals->total_credit ?? 0) + (float) ($openingTotals->total_credit ?? 0);

        return [$debit, $credit];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function collectEntries(?int $accountId, ?string $fromDate, ?string $toDate): Collection
    {
        $voucherLines = VoucherLine::query()
            ->join('vouchers', 'vouchers.id', '=', 'voucher_lines.voucher_id')
            ->join('accounts', 'accounts.id', '=', 'voucher_lines.account_id')
            ->whereNull('vouchers.deleted_at')
            ->where('vouchers.module', 'accounts')
            ->where('vouchers.status', 'posted')
            ->whereNotIn('accounts.code', self::EXCLUDED_ACCOUNT_CODES)
            ->when($accountId, fn ($query) => $query->where('voucher_lines.account_id', $accountId))
            ->when($fromDate, fn ($query) => $query->whereDate('vouchers.voucher_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('vouchers.voucher_date', '<=', $toDate))
            ->orderBy('vouchers.voucher_date')
            ->orderBy('vouchers.id')
            ->orderBy('voucher_lines.id')
            ->get([
                'voucher_lines.id',
                'voucher_lines.account_id',
                'voucher_lines.description',
                'voucher_lines.debit',
                'voucher_lines.credit',
                'voucher_lines.tag',
                'accounts.code as account_code',
                'accounts.name as account_name',
                'vouchers.voucher_date',
                'vouchers.voucher_type',
                'vouchers.voucher_number',
                'vouchers.remarks',
            ]);

        $openings = AccountOpening::query()
            ->join('accounts', 'accounts.id', '=', 'account_openings.account_id')
            ->whereNull('account_openings.deleted_at')
            ->whereNotIn('accounts.code', self::EXCLUDED_ACCOUNT_CODES)
            ->when($accountId, fn ($query) => $query->where('account_openings.account_id', $accountId))
            ->when($fromDate, fn ($query) => $query->whereDate('account_openings.voucher_date', '>=', $fromDate))
            ->when($toDate, fn ($query) => $query->whereDate('account_openings.voucher_date', '<=', $toDate))
            ->orderBy('account_openings.voucher_date')
            ->orderBy('account_openings.id')
            ->get([
                'account_openings.id',
                'account_openings.account_id',
                'account_openings.narration',
                'account_openings.debit',
                'account_openings.credit',
                'account_openings.voucher_date',
                'accounts.code as account_code',
                'accounts.name as account_name',
            ]);

        $entries = collect();

        foreach ($voucherLines as $line) {
            $detail = trim((string) ($line->description ?: $line->remarks));
            $contract = trim((string) ($line->tag ?? ''));
            if ($contract !== '') {
                $detail = $detail === '' ? 'CNT # ' . $contract : $detail . ' CNT # ' . $contract;
            }

            $entries->push([
                'sort_date' => $line->voucher_date,
                'sort_id' => (int) $line->id,
                'date' => $line->voucher_date,
                'type' => strtoupper((string) $line->voucher_type),
                'voucher_no' => (string) $line->voucher_number,
                'narration' => $detail !== '' ? $detail : '.',
                'debit' => (float) $line->debit,
                'credit' => (float) $line->credit,
                'contract_no' => $contract,
                'account_code' => $line->account_code,
                'account_name' => $line->account_name,
            ]);
        }

        foreach ($openings as $opening) {
            $detail = trim((string) ($opening->narration ?? ''));
            $entries->push([
                'sort_date' => $opening->voucher_date,
                'sort_id' => 1000000000 + (int) $opening->id,
                'date' => $opening->voucher_date,
                'type' => 'OBV',
                'voucher_no' => (string) $opening->id,
                'narration' => $detail !== '' ? $detail : 'OPENING BALANCE',
                'debit' => (float) $opening->debit,
                'credit' => (float) $opening->credit,
                'contract_no' => '',
                'account_code' => $opening->account_code,
                'account_name' => $opening->account_name,
            ]);
        }

        return $entries
            ->sortBy([
                ['sort_date', 'asc'],
                ['sort_id', 'asc'],
            ])
            ->values();
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    private function formatRunningRow(array $entry, float $running, bool $isOpeningRow): array
    {
        $debit = (float) ($entry['debit'] ?? 0);
        $credit = (float) ($entry['credit'] ?? 0);

        if ($isOpeningRow) {
            $balance = abs($running);
        } else {
            $balance = abs($running);
        }

        return [
            'date' => ErpDate::display($entry['date']),
            'type' => $entry['type'] ?? '',
            'voucher_no' => $entry['voucher_no'] ?? '',
            'narration' => $entry['narration'] ?? '',
            'debit' => $debit > 0 ? $debit : null,
            'credit' => $credit > 0 ? $credit : null,
            'balance' => $balance,
            'balance_side' => $this->balanceSide($running),
            'contract_no' => $entry['contract_no'] ?? '',
        ];
    }

    private function balanceSide(float $amount): string
    {
        if ($amount > 0.009) {
            return 'Dr';
        }

        if ($amount < -0.009) {
            return 'Cr';
        }

        return '';
    }
}
