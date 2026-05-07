<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountOpening;
use App\Models\FinancialYear;
use App\Models\Voucher;
use App\Models\VoucherLine;
use App\Services\PostingService;
use App\Services\VoucherNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountsFinanceController extends Controller
{
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

        return view('erp.accounts.coa', [
            ...$this->withBreadcrumbs('Chart of Accounts', 'erp.accounts.coa', 'accounts.coa'),
            'accounts' => Account::query()->orderBy('code')->get(),
        ]);
    }

    public function accountsOpening(): View
    {
        $this->ensurePermission('accounts.opening.view');

        return view('erp.accounts.accounts-opening', [
            ...$this->withBreadcrumbs('Accounts Opening', 'erp.accounts.opening', 'accounts.opening'),
            'accounts' => Account::query()->orderBy('code')->get(),
            'financialYears' => FinancialYear::query()->orderByDesc('start_date')->get(),
            'openings' => AccountOpening::query()->with('account')->latest()->limit(50)->get(),
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

        $data = $request->validate([
            'level' => ['required', 'string'],
            'code' => ['required', 'string', 'max:60', 'unique:accounts,code'],
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:accounts,id'],
        ]);

        Account::query()->create($data);

        return back()->with('status', 'Account created.');
    }

    public function storeFinancialYear(Request $request): RedirectResponse
    {
        $this->ensurePermission('accounts.financial-year.create');

        $data = $request->validate([
            'year_code' => ['required', 'string', 'max:20', 'unique:financial_years,year_code'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
        $data['is_closed'] = $request->boolean('is_closed');

        FinancialYear::query()->create($data);

        return back()->with('status', 'Financial year added.');
    }

    public function storeOpening(Request $request): RedirectResponse
    {
        $this->ensurePermission('accounts.opening.create');

        $data = $request->validate([
            'voucher_date' => ['required', 'date'],
            'financial_year_id' => ['required', 'integer', 'exists:financial_years,id'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'narration' => ['nullable', 'string', 'max:255'],
            'debit' => ['nullable', 'numeric', 'min:0'],
            'credit' => ['nullable', 'numeric', 'min:0'],
        ]);
        $data['created_by'] = Auth::id();
        if ((float) ($data['debit'] ?? 0) > 0 && (float) ($data['credit'] ?? 0) > 0) {
            return back()->with('error', 'Opening line cannot contain both debit and credit.');
        }

        AccountOpening::query()->create($data);

        return back()->with('status', 'Opening entry saved.');
    }

    public function storeVoucher(Request $request, string $voucherType, VoucherNumberService $numberService): RedirectResponse
    {
        $permissionPrefix = "accounts.vouchers.{$voucherType}";
        $this->ensurePermission("{$permissionPrefix}.create");

        $data = $request->validate([
            'voucher_date' => ['required', 'date'],
            'financial_year_id' => ['required', 'integer', 'exists:financial_years,id'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.qty' => ['nullable', 'numeric', 'min:0'],
            'lines.*.rate' => ['nullable', 'numeric', 'min:0'],
            'lines.*.amount' => ['nullable', 'numeric', 'min:0'],
            'lines.*.tag' => ['nullable', 'string', 'max:80'],
        ]);
        $amountOnlyVoucherTypes = ['cp', 'cr', 'bpv', 'brv'];

        $voucher = DB::transaction(function () use ($data, $voucherType, $numberService, $amountOnlyVoucherTypes) {
            $year = FinancialYear::query()->findOrFail($data['financial_year_id']);
            $voucherNumber = $numberService->next('accounts', strtoupper($voucherType), $year->year_code);

            $voucher = Voucher::query()->create([
                'module' => 'accounts',
                'voucher_type' => strtoupper($voucherType),
                'voucher_number' => $voucherNumber,
                'voucher_date' => $data['voucher_date'],
                'financial_year_id' => $data['financial_year_id'],
                'remarks' => $data['remarks'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            $debit = 0.0;
            $credit = 0.0;
            $amount = 0.0;
            $lineCount = 0;

            foreach ($data['lines'] as $line) {
                if (
                    empty($line['account_id']) &&
                    empty($line['description']) &&
                    empty($line['debit']) &&
                    empty($line['credit']) &&
                    empty($line['amount'])
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
                if (in_array(strtolower($voucherType), ['cp', 'bpv'], true)) {
                    $lineCredit = $lineAmount > 0 ? $lineAmount : $lineCredit;
                    $lineDebit = 0;
                }
                if (in_array(strtolower($voucherType), ['cr', 'brv'], true)) {
                    $lineDebit = $lineAmount > 0 ? $lineAmount : $lineDebit;
                    $lineCredit = 0;
                }

                $debit += $lineDebit;
                $credit += $lineCredit;
                $amount += $lineAmount > 0 ? $lineAmount : max($lineDebit, $lineCredit);
                $lineCount++;

                VoucherLine::query()->create([
                    'voucher_id' => $voucher->id,
                    'account_id' => $line['account_id'] ?? null,
                    'description' => $line['description'] ?? null,
                    'debit' => $lineDebit,
                    'credit' => $lineCredit,
                    'qty' => (float) ($line['qty'] ?? 0),
                    'rate' => (float) ($line['rate'] ?? 0),
                    'amount' => $lineAmount,
                    'tag' => $line['tag'] ?? null,
                ]);
            }

            if ($lineCount === 0) {
                abort(422, 'At least one valid voucher line is required.');
            }
            if (! in_array(strtolower($voucherType), $amountOnlyVoucherTypes, true) && $debit <= 0 && $credit <= 0) {
                abort(422, 'Voucher requires debit/credit values.');
            }

            $voucher->update([
                'total_debit' => $debit,
                'total_credit' => $credit,
                'total_amount' => $amount,
            ]);

            return $voucher;
        });

        return back()->with('status', "Voucher {$voucher->voucher_number} saved.");
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
        if ($calculatedDebit <= 0 || $calculatedCredit <= 0) {
            return back()->with('error', 'Voucher totals must be greater than zero before posting.');
        }

        if (
            abs($calculatedDebit - (float) $voucher->total_debit) > 0.01 ||
            abs($calculatedCredit - (float) $voucher->total_credit) > 0.01
        ) {
            return back()->with('error', 'Voucher header totals do not match detail lines.');
        }

        if ((float) $voucher->total_debit !== (float) $voucher->total_credit) {
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
        $this->ensurePermission("accounts.vouchers.{$slug}.view");

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
            'accounts' => Account::query()->orderBy('code')->get(),
            'financialYears' => FinancialYear::query()->orderByDesc('start_date')->get(),
            'recentVouchers' => Voucher::query()
                ->where('module', 'accounts')
                ->where('voucher_type', $voucherCode)
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }
}
