<?php

namespace App\Support;

use App\Models\Account;
use App\Services\AccountsLedgerService;
use Illuminate\Http\Request;

class ReportFilters
{
    public const ACCOUNTS_REPORTS = [
        'account-statement' => 'Account Statement',
        'trial-balance' => 'Trial Balance',
        'ledger' => 'General Ledger',
        'voucher-register' => 'Voucher Register',
    ];

    /**
     * @return array{
     *     report: string,
     *     from_date: ?string,
     *     to_date: ?string,
     *     from_display: string,
     *     to_display: string,
     *     account_id: ?int,
     *     account_query: string,
     *     account: ?Account,
     *     screen_slug: string,
     *     contract_query: string,
     *     status: string
     * }
     */
    public static function parse(Request $request, string $screen): array
    {
        $fromStorage = ErpDate::toStorage($request->input('from_date'));
        $toStorage = ErpDate::toStorage($request->input('to_date'));

        $accountId = $request->filled('account_id') ? (int) $request->input('account_id') : null;
        $accountQuery = trim((string) $request->input('account_query', $request->input('account_search', '')));

        $account = $screen === 'accounts'
            ? app(AccountsLedgerService::class)->findAccount($accountId, $accountQuery)
            : ($accountId ? Account::query()->postable()->find($accountId) : null);

        $report = (string) $request->input('report', $screen === 'accounts' ? 'account-statement' : 'summary');
        if ($screen === 'accounts' && ! array_key_exists($report, self::ACCOUNTS_REPORTS)) {
            $report = 'account-statement';
        }

        return [
            'report' => $report,
            'from_date' => $fromStorage,
            'to_date' => $toStorage,
            'from_display' => $fromStorage ? ErpDate::display($fromStorage) : '',
            'to_display' => $toStorage ? ErpDate::display($toStorage) : '',
            'account_id' => $account?->id,
            'account_query' => $accountQuery,
            'account' => $account,
            'screen_slug' => trim((string) $request->input('screen_slug', '')),
            'contract_query' => trim((string) $request->input('contract_query', '')),
            'status' => trim((string) $request->input('status', '')),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public static function query(array $filters): array
    {
        return array_filter([
            'report' => $filters['report'] ?? null,
            'from_date' => $filters['from_display'] ?: $filters['from_date'] ?? null,
            'to_date' => $filters['to_display'] ?: $filters['to_date'] ?? null,
            'account_id' => $filters['account_id'] ?? null,
            'account_query' => $filters['account_query'] ?? null,
            'screen_slug' => $filters['screen_slug'] ?? null,
            'contract_query' => $filters['contract_query'] ?? null,
            'status' => $filters['status'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');
    }
}
