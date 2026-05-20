<?php

namespace App\Http\Controllers\Erp;

use App\Http\Concerns\NormalizesErpDates;
use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Voucher;
use App\Models\YarnContract;
use App\Services\AccountsLedgerService;
use App\Support\ErpDate;
use App\Support\ReportFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use NormalizesErpDates;

    private const ALLOWED_SCREENS = ['accounts', 'yarn', 'grey'];

    public function view(Request $request, string $screen): View
    {
        $this->ensurePermission($screen, 'view');
        $this->normalizeErpDates($request, ['from_date', 'to_date']);

        $filters = ReportFilters::parse($request, $screen);
        $payload = $this->buildPayload($request, $screen, $filters);

        return view($payload['view'], [
            'activeModule' => 'reports',
            'pageTitle' => $payload['title'],
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => 'Reports', 'route' => 'erp.reports.dashboard'],
                ['label' => $payload['title']],
            ],
            'title' => $payload['title'],
            'screen' => $screen,
            'report' => $filters['report'],
            'filters' => ReportFilters::query($filters),
            'filterMeta' => $filters,
            ...$payload['data'],
        ]);
    }

    public function export(Request $request, string $screen): StreamedResponse
    {
        $this->ensurePermission($screen, 'print');
        $this->normalizeErpDates($request, ['from_date', 'to_date']);

        $filters = ReportFilters::parse($request, $screen);
        $payload = $this->buildPayload($request, $screen, $filters);

        $filename = str_replace(' ', '_', strtolower($payload['title'])) . '.csv';

        return response()->stream(function () use ($payload): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, $payload['csv_headers']);
            foreach ($payload['csv_rows'] as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function print(Request $request, string $screen): View
    {
        $this->ensurePermission($screen, 'print');
        $this->normalizeErpDates($request, ['from_date', 'to_date']);

        $filters = ReportFilters::parse($request, $screen);
        $payload = $this->buildPayload($request, $screen, $filters);

        return view('erp.reports.print-layout', array_merge([
            'title' => $payload['title'],
            'printView' => $payload['print_view'],
            'filterMeta' => $filters,
        ], $payload['data']));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     title: string,
     *     view: string,
     *     print_view: string,
     *     csv_headers: list<string>,
     *     csv_rows: list<list<string|float|null>>,
     *     data: array<string, mixed>
     * }
     */
    private function buildPayload(Request $request, string $screen, array $filters): array
    {
        if ($screen === 'accounts') {
            return $this->buildAccountsPayload($filters);
        }

        return $this->buildInventoryPayload($screen, $filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function buildAccountsPayload(array $filters): array
    {
        $ledger = app(AccountsLedgerService::class);

        return match ($filters['report']) {
            'trial-balance' => (function () use ($ledger, $filters): array {
                $dataset = $ledger->trialBalance($filters['from_date'], $filters['to_date']);

                return $this->wrapAccountsReport(
                    'Trial Balance',
                    'erp.reports.accounts-trial-balance',
                    'erp.reports.partials.accounts-trial-balance-table',
                    $dataset,
                    ['Account Code', 'Account Name', 'Opening Debit', 'Opening Credit', 'Debit', 'Credit', 'Closing Debit', 'Closing Credit'],
                    collect($dataset['rows'])->map(static fn (array $row): array => [
                        $row['account_code'],
                        $row['account_name'],
                        $row['opening_debit'],
                        $row['opening_credit'],
                        $row['debit'],
                        $row['credit'],
                        $row['closing_debit'],
                        $row['closing_credit'],
                    ])->all(),
                );
            })(),
            'ledger' => (function () use ($ledger, $filters): array {
                $dataset = $ledger->generalLedger($filters['account'], $filters['from_date'], $filters['to_date']);

                return $this->wrapAccountsReport(
                    'General Ledger',
                    'erp.reports.accounts-ledger',
                    'erp.reports.partials.accounts-ledger-table',
                    $dataset,
                    ['Date', 'Account', 'Type', 'Voucher #', 'Narration', 'Cost Center', 'Debit', 'Credit'],
                    collect($dataset['rows'])->map(static fn (array $row): array => [
                        $row['date'],
                        $row['account_code'] . ' — ' . $row['account_name'],
                        $row['type'],
                        $row['voucher_no'],
                        $row['narration'],
                        $row['contract_no'],
                        $row['debit'],
                        $row['credit'],
                    ])->all(),
                );
            })(),
            'voucher-register' => $this->buildVoucherRegisterPayload($filters),
            default => $this->buildAccountStatementPayload($filters, $ledger),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function buildAccountStatementPayload(array $filters, AccountsLedgerService $ledger): array
    {
        if (! $filters['account'] instanceof \App\Models\Account) {
            return [
                'title' => 'Account Statement',
                'view' => 'erp.reports.accounts-statement',
                'print_view' => 'erp.reports.partials.accounts-statement-table',
                'csv_headers' => ['Date', 'Type', 'Voucher #', 'Narration', 'Debit', 'Credit', 'Balance', 'Dr/Cr', 'Cost Center'],
                'csv_rows' => [],
                'data' => [
                    'statement' => null,
                    'notice' => 'Select a sub-ledger account (code or name) to generate the account statement.',
                ],
            ];
        }

        $statement = $ledger->accountStatement(
            $filters['account'],
            $filters['from_date'],
            $filters['to_date'],
        );

        return [
            'title' => 'Account Statement',
            'view' => 'erp.reports.accounts-statement',
            'print_view' => 'erp.reports.partials.accounts-statement-table',
            'csv_headers' => ['Date', 'Type', 'Voucher #', 'Narration', 'Debit', 'Credit', 'Balance', 'Dr/Cr', 'Cost Center'],
            'csv_rows' => collect($statement['rows'])->map(static fn (array $row): array => [
                $row['date'],
                $row['type'],
                $row['voucher_no'],
                $row['narration'],
                $row['debit'],
                $row['credit'],
                $row['balance'],
                $row['balance_side'],
                $row['contract_no'],
            ])->all(),
            'data' => [
                'statement' => $statement,
                'notice' => null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function buildVoucherRegisterPayload(array $filters): array
    {
        $query = Voucher::query()
            ->with(['party', 'lines.account'])
            ->where('module', 'accounts');

        if ($filters['from_date']) {
            $query->whereDate('voucher_date', '>=', $filters['from_date']);
        }
        if ($filters['to_date']) {
            $query->whereDate('voucher_date', '<=', $filters['to_date']);
        }
        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
        if ($filters['account']) {
            $accountId = $filters['account']->id;
            $query->whereHas('lines', static fn ($lineQuery) => $lineQuery->where('account_id', $accountId));
        }

        $vouchers = $query
            ->orderBy('voucher_date')
            ->orderBy('id')
            ->get();

        $rows = $vouchers->map(static fn (Voucher $voucher): array => [
            'date' => ErpDate::display($voucher->voucher_date),
            'type' => strtoupper($voucher->voucher_type),
            'reference' => $voucher->voucher_number,
            'party' => $voucher->party?->name ?? '-',
            'status' => strtoupper($voucher->status),
            'debit' => (float) $voucher->total_debit,
            'credit' => (float) $voucher->total_credit,
            'amount' => (float) $voucher->total_amount,
        ])->all();

        return [
            'title' => 'Voucher Register',
            'view' => 'erp.reports.accounts-voucher-register',
            'print_view' => 'erp.reports.partials.accounts-voucher-register-table',
            'csv_headers' => ['Date', 'Type', 'Voucher #', 'Party', 'Status', 'Debit', 'Credit', 'Amount'],
            'csv_rows' => collect($rows)->map(static fn (array $row): array => [
                $row['date'],
                $row['type'],
                $row['reference'],
                $row['party'],
                $row['status'],
                $row['debit'],
                $row['credit'],
                $row['amount'],
            ])->all(),
            'data' => [
                'rows' => $rows,
                'totals' => [
                    'debit' => array_sum(array_column($rows, 'debit')),
                    'credit' => array_sum(array_column($rows, 'credit')),
                    'amount' => array_sum(array_column($rows, 'amount')),
                    'count' => count($rows),
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $dataset
     * @param  list<string>  $csvHeaders
     * @param  list<list<string|float|null>>  $csvRows
     * @return array<string, mixed>
     */
    private function wrapAccountsReport(
        string $title,
        string $view,
        string $printView,
        array $dataset,
        array $csvHeaders,
        array $csvRows,
    ): array {
        return [
            'title' => $title,
            'view' => $view,
            'print_view' => $printView,
            'csv_headers' => $csvHeaders,
            'csv_rows' => $csvRows,
            'data' => $dataset,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function buildInventoryPayload(string $screen, array $filters): array
    {
        $module = $screen === 'grey' ? 'grey' : 'yarn';

        $query = InventoryTransaction::query()
            ->where('module', $module)
            ->with(['party', 'account', 'yarnContract', 'lines']);

        if ($filters['from_date']) {
            $query->whereDate('trans_date', '>=', $filters['from_date']);
        }
        if ($filters['to_date']) {
            $query->whereDate('trans_date', '<=', $filters['to_date']);
        }
        if ($filters['screen_slug'] !== '') {
            $query->where('screen_slug', $filters['screen_slug']);
        }
        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }
        if ($filters['account']) {
            $query->where(function ($builder) use ($filters): void {
                $builder
                    ->where('account_id', $filters['account']->id)
                    ->orWhereHas('yarnContract', fn ($contractQuery) => $contractQuery->where('account_id', $filters['account']->id));
            });
        }
        if ($filters['contract_query'] !== '') {
            $contractQuery = $filters['contract_query'];
            $query->where(function ($builder) use ($contractQuery): void {
                $builder
                    ->whereHas('yarnContract', fn ($q) => $q->where('contract_no', 'like', '%' . $contractQuery . '%'))
                    ->orWhereHas('fromYarnContract', fn ($q) => $q->where('contract_no', 'like', '%' . $contractQuery . '%'))
                    ->orWhereHas('toYarnContract', fn ($q) => $q->where('contract_no', 'like', '%' . $contractQuery . '%'));
            });
        }

        $transactions = $query
            ->orderBy('trans_date')
            ->orderBy('id')
            ->get();

        $rows = $transactions->map(static function (InventoryTransaction $transaction): array {
            return [
                'date' => ErpDate::display($transaction->trans_date),
                'screen' => $transaction->screen_slug,
                'reference' => $transaction->trans_no,
                'party' => $transaction->party?->name
                    ?? $transaction->account?->name
                    ?? $transaction->yarnContract?->account?->name
                    ?? '-',
                'contract' => $transaction->yarnContract?->contract_no
                    ?? $transaction->fromYarnContract?->contract_no
                    ?? $transaction->toYarnContract?->contract_no
                    ?? '-',
                'status' => strtoupper($transaction->status),
                'qty' => (float) $transaction->total_qty,
                'amount' => (float) $transaction->total_amount,
            ];
        })->all();

        $title = ucfirst($module) . ' Transactions Report';

        return [
            'title' => $title,
            'view' => 'erp.reports.inventory-summary',
            'print_view' => 'erp.reports.partials.inventory-summary-table',
            'csv_headers' => ['Date', 'Screen', 'Reference', 'Party / Account', 'Contract', 'Status', 'Qty', 'Amount'],
            'csv_rows' => collect($rows)->map(static fn (array $row): array => [
                $row['date'],
                $row['screen'],
                $row['reference'],
                $row['party'],
                $row['contract'],
                $row['status'],
                $row['qty'],
                $row['amount'],
            ])->all(),
            'data' => [
                'rows' => $rows,
                'totals' => [
                    'qty' => array_sum(array_column($rows, 'qty')),
                    'amount' => array_sum(array_column($rows, 'amount')),
                    'count' => count($rows),
                ],
                'from_display' => $filters['from_display'] ?: 'Beginning',
                'to_display' => $filters['to_display'] ?: ErpDate::todayDisplay(),
            ],
        ];
    }

    private function ensurePermission(string $screen, string $action): void
    {
        abort_unless(in_array($screen, self::ALLOWED_SCREENS, true), 404);
        $user = Auth::user();
        abort_unless($user instanceof \App\Models\User && $user->hasPermission("reports.{$screen}.{$action}"), 403);
    }
}
