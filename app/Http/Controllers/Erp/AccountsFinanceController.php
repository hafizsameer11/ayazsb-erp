<?php

namespace App\Http\Controllers\Erp;

use App\Http\Concerns\AuthorizesAdminDelete;
use App\Http\Concerns\NormalizesErpDates;
use App\Http\Controllers\Controller;
use App\Rules\ErpDate;
use App\Models\Account;
use App\Models\AccountOpening;
use App\Models\FinancialYear;
use App\Models\Voucher;
use App\Models\VoucherLine;
use App\Services\ChartOfAccountsService;
use App\Services\PostingService;
use App\Services\VoucherNumberService;
use App\Support\RecordHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountsFinanceController extends Controller
{
    use AuthorizesAdminDelete;
    use NormalizesErpDates;

    public function dashboard(): View
    {
        $this->ensurePermission('accounts.dashboard.view');

        return view('erp.accounts.dashboard', [
            ...$this->shared('Accounts & Finance'),
            'permissionPrefix' => 'accounts.dashboard',
        ]);
    }

    public function chartOfAccounts(): View
    {
        $this->ensurePermission('accounts.coa.view');

        $service = ChartOfAccountsService::general();

        return view('erp.accounts.coa', [
            ...$this->withBreadcrumbs('Chart of Accounts', 'erp.accounts.coa', 'accounts.coa'),
            'accounts' => $service->accountsForListing(),
            'coaParentsJson' => $service->parentsJson(),
            'coaTitle' => 'ACCNTS_0003 — Chart of accounts (COA)',
            'coaStoreRoute' => route('erp.accounts.coa.store'),
            'coaUpdateBase' => url('/erp/accounts/coa'),
            'coaPermissionPrefix' => 'accounts.coa',
        ]);
    }

    public function accountsOpening(Request $request): View
    {
        $this->ensurePermission('accounts.opening.view');

        $editingOpening = $this->resolveEditingOpening($request);

        $openingQuery = AccountOpening::query()
            ->with(['account', 'financialYear'])
            ->orderByDesc('voucher_date')
            ->orderByDesc('id');

        return view('erp.accounts.accounts-opening', [
            ...$this->withBreadcrumbs('Accounts Opening', 'erp.accounts.opening', 'accounts.opening'),
            'accounts' => Account::query()->forGeneral()->postable()->orderBy('code')->get(),
            'financialYears' => FinancialYear::query()->orderByDesc('start_date')->get(),
            'editingOpening' => $editingOpening,
            ...RecordHistory::buildForDay($request, $openingQuery, 'voucher_date', 'erp.accounts.opening'),
        ]);
    }

    public function financialYear(): View
    {
        $this->ensurePermission('accounts.financial-year.view');

        return view('erp.accounts.financial-year', [
            ...$this->withBreadcrumbs('Financial Year', 'erp.accounts.financial-year', 'accounts.financial-year'),
            'financialYears' => FinancialYear::query()->orderByDesc('start_date')->get(),
        ]);
    }

    public function journalVoucher(): View
    {
        return $this->voucherView('jv', 'JV', 'Journal Voucher', 'ACCNTS_0006');
    }

    public function cashPaymentVoucher(): View
    {
        return $this->voucherView('cp', 'CP', 'Cash Payment Voucher', 'ACCNTS_0008');
    }

    public function cashReceiptVoucher(): View
    {
        return $this->voucherView('cr', 'CR', 'Cash Receipt Voucher', 'ACCNTS_0007');
    }

    public function bankPaymentVoucher(): View
    {
        return $this->voucherView('bpv', 'BPV', 'Bank Payment Voucher', 'ACCNTS_0009');
    }

    public function bankReceiptVoucher(): View
    {
        return $this->voucherView('brv', 'BRV', 'Bank Receipt Voucher', 'ACCNTS_0010');
    }

    public function cashVoucher(): View
    {
        return $this->voucherView('cv', 'CV', 'Cash Voucher', 'ACCNTS_0011');
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $this->ensurePermission('accounts.coa.create');

        $service = ChartOfAccountsService::general();
        $validated = $request->validate($service->storeValidationRules());
        $service->create($validated);

        return back()->with('status', 'Account created.');
    }

    public function updateAccount(Request $request, Account $account): RedirectResponse
    {
        $this->ensurePermission('accounts.coa.edit');

        $service = ChartOfAccountsService::general();
        $validated = $request->validate($service->updateValidationRules());
        $account = $service->update($account, $validated, $request);

        return back()->with('status', "Account {$account->code} updated.");
    }

    public function storeFinancialYear(Request $request): RedirectResponse
    {
        $this->ensurePermission('accounts.financial-year.create');

        $this->normalizeErpDates($request, ['start_date', 'end_date']);

        $data = $request->validate([
            'year_code' => ['required', 'string', 'max:20', 'unique:financial_years,year_code'],
            'start_date' => ['required', 'date', new ErpDate],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', new ErpDate],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
        $data['is_closed'] = $request->boolean('is_closed');

        FinancialYear::query()->create($data);

        return back()->with('status', 'Financial year added.');
    }

    public function storeOpening(Request $request): RedirectResponse
    {
        $this->ensurePermission('accounts.opening.create');

        $this->normalizeErpDates($request, ['voucher_date']);

        $data = $request->validate([
            'voucher_date' => ['required', 'date', new ErpDate],
            'financial_year_id' => ['required', 'integer', 'exists:financial_years,id'],
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('ledger', Account::LEDGER_GENERAL)->where('level', 'sub_ledger')->where('is_active', true)),
            ],
            'narration' => ['nullable', 'string', 'max:255'],
            'debit' => ['nullable', 'numeric', 'min:0'],
            'credit' => ['nullable', 'numeric', 'min:0'],
        ]);
        $data['created_by'] = Auth::id();
        if ((float) ($data['debit'] ?? 0) > 0 && (float) ($data['credit'] ?? 0) > 0) {
            return back()->with('error', 'Opening line cannot contain both debit and credit.');
        }

        AccountOpening::query()->create($data);

        return $this->voucherSaveResponse(
            $request,
            'opening',
            'Opening entry saved.',
            \App\Support\ErpDate::display($data['voucher_date']),
        );
    }

    public function updateOpening(Request $request, AccountOpening $opening): RedirectResponse|JsonResponse
    {
        $this->ensurePermission('accounts.opening.edit');

        $this->normalizeErpDates($request, ['voucher_date']);

        $data = $request->validate([
            'voucher_date' => ['required', 'date', new ErpDate],
            'financial_year_id' => ['required', 'integer', 'exists:financial_years,id'],
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('ledger', Account::LEDGER_GENERAL)->where('level', 'sub_ledger')->where('is_active', true)),
            ],
            'narration' => ['nullable', 'string', 'max:255'],
            'debit' => ['nullable', 'numeric', 'min:0'],
            'credit' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ((float) ($data['debit'] ?? 0) > 0 && (float) ($data['credit'] ?? 0) > 0) {
            return back()->with('error', 'Opening line cannot contain both debit and credit.');
        }

        $opening->update($data);

        return $this->voucherSaveResponse(
            $request,
            'opening',
            'Opening entry updated.',
            \App\Support\ErpDate::display($opening->voucher_date),
        );
    }

    public function destroyOpening(Request $request, AccountOpening $opening): RedirectResponse|JsonResponse
    {
        $this->ensureAdminCanDeleteRecords();

        $historyDate = \App\Support\ErpDate::display($opening->voucher_date);
        $opening->delete();

        return $this->erpDeleteResponse(
            $request,
            'erp.accounts.opening',
            [],
            'Opening entry deleted.',
            $historyDate,
        );
    }

    public function storeVoucher(Request $request, string $voucherType, VoucherNumberService $numberService): RedirectResponse|JsonResponse
    {
        $permissionPrefix = "accounts.vouchers.{$voucherType}";
        $this->ensurePermission("{$permissionPrefix}.create");
        $this->ensurePermission("{$permissionPrefix}.post");

        $this->normalizeErpDates($request, ['voucher_date']);

        $data = $request->validate([
            'voucher_date' => ['required', 'date', new ErpDate],
            'financial_year_id' => ['required', 'integer', 'exists:financial_years,id'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.account_id' => [
                'nullable',
                'integer',
                Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('ledger', Account::LEDGER_GENERAL)->where('level', 'sub_ledger')->where('is_active', true)),
            ],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.qty' => ['nullable', 'numeric', 'min:0'],
            'lines.*.rate' => ['nullable', 'numeric', 'min:0'],
            'lines.*.amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.tag' => ['nullable', 'string', 'max:80'],
            'lines.*.meta' => ['nullable', 'array'],
            'lines.*.meta.instrument_no' => ['nullable', 'string', 'max:80'],
            'lines.*.meta.instrument_date' => ['nullable', 'string', 'max:20'],
            'lines.*.meta.title' => ['nullable', 'string', 'max:120'],
        ]);
        $totals = $this->prepareVoucherLines($data['lines'], $voucherType);

        $voucher = DB::transaction(function () use ($data, $voucherType, $numberService, $totals) {
            $year = FinancialYear::query()->findOrFail($data['financial_year_id']);
            $voucherNumber = $numberService->next('accounts', strtoupper($voucherType), $year->year_code);

            $voucher = Voucher::query()->create([
                'module' => 'accounts',
                'voucher_type' => strtoupper($voucherType),
                'voucher_number' => $voucherNumber,
                'voucher_date' => $data['voucher_date'],
                'financial_year_id' => $data['financial_year_id'],
                'remarks' => $data['remarks'] ?? null,
                'status' => 'posted',
                'total_debit' => $totals['debit'],
                'total_credit' => $totals['credit'],
                'total_amount' => $totals['amount'],
                'created_by' => Auth::id(),
                'posted_by' => Auth::id(),
                'posted_at' => now(),
            ]);

            foreach ($totals['lines'] as $line) {
                VoucherLine::query()->create([
                    'voucher_id' => $voucher->id,
                    ...$line,
                ]);
            }

            return $voucher;
        });

        $slug = strtolower($voucherType);

        return $this->voucherSaveResponse(
            $request,
            $slug,
            "Voucher {$voucher->voucher_number} posted.",
            \App\Support\ErpDate::display($voucher->voucher_date),
        );
    }

    public function updateVoucher(Request $request, Voucher $voucher): RedirectResponse|JsonResponse
    {
        $slug = strtolower($voucher->voucher_type);
        $this->ensurePermission("accounts.vouchers.{$slug}.edit");
        abort_unless($voucher->module === 'accounts', 404);

        $this->normalizeErpDates($request, ['voucher_date']);

        $data = $request->validate([
            'voucher_date' => ['required', 'date', new ErpDate],
            'financial_year_id' => ['required', 'integer', 'exists:financial_years,id'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.account_id' => [
                'nullable',
                'integer',
                Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('ledger', Account::LEDGER_GENERAL)->where('level', 'sub_ledger')->where('is_active', true)),
            ],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.qty' => ['nullable', 'numeric', 'min:0'],
            'lines.*.rate' => ['nullable', 'numeric', 'min:0'],
            'lines.*.amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.tag' => ['nullable', 'string', 'max:80'],
            'lines.*.meta' => ['nullable', 'array'],
            'lines.*.meta.instrument_no' => ['nullable', 'string', 'max:80'],
            'lines.*.meta.instrument_date' => ['nullable', 'string', 'max:20'],
            'lines.*.meta.title' => ['nullable', 'string', 'max:120'],
        ]);

        $totals = $this->prepareVoucherLines($data['lines'], $slug);

        DB::transaction(function () use ($voucher, $data, $totals): void {
            $voucher->update([
                'voucher_date' => $data['voucher_date'],
                'financial_year_id' => $data['financial_year_id'],
                'remarks' => $data['remarks'] ?? null,
                'total_debit' => $totals['debit'],
                'total_credit' => $totals['credit'],
                'total_amount' => $totals['amount'],
            ]);

            $voucher->lines()->delete();

            foreach ($totals['lines'] as $line) {
                VoucherLine::query()->create([
                    'voucher_id' => $voucher->id,
                    ...$line,
                ]);
            }
        });

        return $this->voucherSaveResponse(
            $request,
            $slug,
            "Voucher {$voucher->voucher_number} updated.",
            \App\Support\ErpDate::display($voucher->fresh()->voucher_date),
        );
    }

    public function destroyVoucher(Request $request, Voucher $voucher): RedirectResponse|JsonResponse
    {
        $this->ensureAdminCanDeleteRecords();
        abort_unless($voucher->module === 'accounts', 404);

        $slug = strtolower($voucher->voucher_type);
        $voucherNumber = $voucher->voucher_number;
        $historyDate = \App\Support\ErpDate::display($voucher->voucher_date);

        $voucher->delete();

        return $this->erpDeleteResponse(
            $request,
            'erp.accounts.vouchers.' . $slug,
            [],
            "Voucher {$voucherNumber} deleted.",
            $historyDate,
        );
    }

    public function postVoucher(Request $request, Voucher $voucher, PostingService $postingService): RedirectResponse
    {
        $map = strtolower($voucher->voucher_type);
        $this->ensurePermission("accounts.vouchers.{$map}.post");
        $voucher->load('lines');

        if ($voucher->status === 'posted') {
            return back()->with('status', "Voucher {$voucher->voucher_number} already posted.");
        }

        if ($voucher->lines->count() === 0) {
            return back()->with('error', 'Voucher cannot be posted without detail lines.');
        }

        $calculatedDebit = (float) $voucher->lines->sum('debit');
        $calculatedCredit = (float) $voucher->lines->sum('credit');
        if ($this->voucherRequiresBalancedSides($map) && ($calculatedDebit <= 0 || $calculatedCredit <= 0)) {
            return back()->with('error', 'Voucher totals must be greater than zero before posting.');
        }

        if (! $this->voucherRequiresBalancedSides($map) && max($calculatedDebit, $calculatedCredit) <= 0) {
            return back()->with('error', 'Voucher total must be greater than zero before posting.');
        }

        if (
            abs($calculatedDebit - (float) $voucher->total_debit) > 0.01 ||
            abs($calculatedCredit - (float) $voucher->total_credit) > 0.01
        ) {
            return back()->with('error', 'Voucher header totals do not match detail lines.');
        }

        if ($this->voucherRequiresBalancedSides($map) && (float) $voucher->total_debit !== (float) $voucher->total_credit) {
            return back()->with('error', 'Debit and credit must be balanced before posting.');
        }

        $postingService->postVoucher($voucher, (int) Auth::id());

        return back()->with('status', "Voucher {$voucher->voucher_number} posted.");
    }

    public function printVoucher(Voucher $voucher): View
    {
        $map = strtolower($voucher->voucher_type);
        $this->ensurePermission("accounts.vouchers.{$map}.print");

        return view('erp.accounts.vouchers.print', [
            ...$this->shared('Print Voucher'),
            'voucher' => $voucher->load('lines.account'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function shared(string $pageTitle): array
    {
        return [
            'activeModule' => 'accounts',
            'pageTitle' => $pageTitle,
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => 'Accounts & Finance', 'route' => 'erp.accounts.dashboard'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function withBreadcrumbs(string $pageTitle, string $routeName, string $permissionPrefix): array
    {
        return [
            ...$this->shared($pageTitle),
            'permissionPrefix' => $permissionPrefix,
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => 'Accounts & Finance', 'route' => 'erp.accounts.dashboard'],
                ['label' => $pageTitle, 'route' => $routeName],
            ],
        ];
    }

    private function ensurePermission(string $permission): void
    {
        $user = Auth::user();
        abort_unless($user instanceof \App\Models\User && $user->hasPermission($permission), 403);
    }

    private function voucherView(string $slug, string $voucherCode, string $title, string $formId): View
    {
        $request = request();
        $this->ensurePermission("accounts.vouchers.{$slug}.view");

        $editingVoucher = $this->resolveEditingVoucher($request, $slug, $voucherCode);

        $voucherQuery = Voucher::query()
            ->where('module', 'accounts')
            ->where('voucher_type', $voucherCode)
            ->orderByDesc('voucher_date')
            ->orderByDesc('id');

        return view("erp.accounts.vouchers." . match ($slug) {
            'jv' => 'journal',
            'cp' => 'cash-payment',
            'cr' => 'cash-receipt',
            'bpv' => 'bank-payment',
            'brv' => 'bank-receipt',
            default => 'cash',
        }, [
            ...$this->withBreadcrumbs($title, "erp.accounts.vouchers.{$slug}", "accounts.vouchers.{$slug}"),
            'voucherCode' => $voucherCode,
            'voucherTitle' => $title,
            'formId' => $formId,
            'accounts' => Account::query()->forGeneral()->postable()->orderBy('code')->get(),
            'financialYears' => FinancialYear::query()->orderByDesc('start_date')->get(),
            'voucherSlug' => $slug,
            'editingVoucher' => $editingVoucher,
            'editingLines' => $editingVoucher?->lines,
            'defaultVoucherDate' => $editingVoucher?->voucher_date ?? RecordHistory::selectedDateStorage($request),
            ...RecordHistory::buildForDay(
                $request,
                $voucherQuery->with('lines.account'),
                'voucher_date',
                'erp.accounts.vouchers.' . $slug,
            ),
        ]);
    }

    private function resolveEditingVoucher(Request $request, string $slug, string $voucherCode): ?Voucher
    {
        $editId = $request->query('edit');
        if ($editId === null || $editId === '') {
            return null;
        }

        $voucher = Voucher::query()
            ->where('module', 'accounts')
            ->where('voucher_type', $voucherCode)
            ->with('lines.account')
            ->find($editId);

        abort_if($voucher === null, 404);
        $this->ensurePermission("accounts.vouchers.{$slug}.edit");

        return $voucher;
    }

    private function resolveEditingOpening(Request $request): ?AccountOpening
    {
        $editId = $request->query('edit');
        if ($editId === null || $editId === '') {
            return null;
        }

        $opening = AccountOpening::query()->with(['account', 'financialYear'])->find($editId);
        abort_if($opening === null, 404);
        $this->ensurePermission('accounts.opening.edit');

        return $opening;
    }

    /**
     * @return array{lines: list<array<string, mixed>>, debit: float, credit: float, amount: float}
     */
    private function prepareVoucherLines(array $lines, string $voucherType): array
    {
        $debit = 0.0;
        $credit = 0.0;
        $amount = 0.0;
        $preparedLines = [];
        $instrumentNumbers = [];

        foreach ($lines as $line) {
            if (
                empty($line['account_id']) &&
                empty($line['description']) &&
                empty($line['debit']) &&
                empty($line['credit']) &&
                empty($line['amount']) &&
                empty($line['meta']['instrument_no'] ?? null) &&
                empty($line['meta']['instrument_date'] ?? null) &&
                empty($line['meta']['title'] ?? null)
            ) {
                continue;
            }

            if (empty($line['account_id'])) {
                continue;
            }

            $lineDebit = (float) ($line['debit'] ?? 0);
            $lineCredit = (float) ($line['credit'] ?? 0);
            $lineAmount = (float) ($line['amount'] ?? 0);
            if ($lineDebit > 0 && $lineCredit > 0) {
                continue;
            }

            if ($lineDebit <= 0 && $lineCredit <= 0 && $lineAmount > 0) {
                if (in_array(strtolower($voucherType), ['cp', 'bpv'], true)) {
                    $lineCredit = $lineAmount;
                } elseif (in_array(strtolower($voucherType), ['cr', 'brv'], true)) {
                    $lineDebit = $lineAmount;
                }
            }

            if ($lineDebit <= 0 && $lineCredit <= 0) {
                continue;
            }

            $instrumentNo = trim((string) ($line['meta']['instrument_no'] ?? ''));
            if ($instrumentNo !== '') {
                $normalizedInstrumentNo = strtolower($instrumentNo);
                if (in_array($normalizedInstrumentNo, $instrumentNumbers, true)) {
                    throw ValidationException::withMessages([
                        'lines' => 'Instrument # must be unique on each voucher line.',
                    ]);
                }
                $instrumentNumbers[] = $normalizedInstrumentNo;
            }

            $debit += $lineDebit;
            $credit += $lineCredit;
            $amount += $lineAmount > 0 ? $lineAmount : max($lineDebit, $lineCredit);
            $preparedLines[] = [
                'account_id' => $line['account_id'] ?? null,
                'description' => $line['description'] ?? null,
                'debit' => $lineDebit,
                'credit' => $lineCredit,
                'qty' => (float) ($line['qty'] ?? 0),
                'rate' => (float) ($line['rate'] ?? 0),
                'amount' => $lineAmount > 0 ? $lineAmount : max($lineDebit, $lineCredit),
                'tag' => $line['tag'] ?? null,
                'meta' => [
                    'instrument_no' => $instrumentNo !== '' ? $instrumentNo : null,
                    'instrument_date' => blank($line['meta']['instrument_date'] ?? null) ? null : (string) $line['meta']['instrument_date'],
                    'title' => blank($line['meta']['title'] ?? null) ? null : trim((string) $line['meta']['title']),
                ],
            ];
        }

        if (count($preparedLines) === 0) {
            throw ValidationException::withMessages([
                'lines' => 'At least one valid voucher line is required.',
            ]);
        }
        if ($this->voucherRequiresBalancedSides($voucherType) && ($debit <= 0 || $credit <= 0)) {
            throw ValidationException::withMessages([
                'lines' => 'Voucher requires both debit and credit values.',
            ]);
        }
        if (! $this->voucherRequiresBalancedSides($voucherType) && max($debit, $credit) <= 0) {
            throw ValidationException::withMessages([
                'lines' => 'Voucher requires at least one amount value.',
            ]);
        }
        if ($this->voucherRequiresBalancedSides($voucherType) && abs($debit - $credit) > 0.009) {
            throw ValidationException::withMessages([
                'lines' => 'Debit and credit must be equal before posting.',
            ]);
        }

        return [
            'lines' => $preparedLines,
            'debit' => $debit,
            'credit' => $credit,
            'amount' => $amount,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function historyQuery(Request $request): array
    {
        return RecordHistory::historyQuery($request);
    }

    private function voucherRequiresBalancedSides(string $voucherType): bool
    {
        return strtolower($voucherType) === 'jv';
    }

    private function voucherSaveResponse(
        Request $request,
        string $slug,
        string $message,
        ?string $historyDateDisplay = null,
    ): RedirectResponse|JsonResponse {
        $params = $historyDateDisplay !== null
            ? ['history_date' => $historyDateDisplay]
            : $this->historyQuery($request);

        $redirect = route('erp.accounts.vouchers.' . $slug, $params);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => $redirect,
            ]);
        }

        return redirect()->to($redirect)->with('status', $message);
    }
}
