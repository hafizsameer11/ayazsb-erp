<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FinancialYear;
use App\Models\WeavingAccountSetting;
use App\Models\WeavingDepartment;
use Illuminate\Validation\ValidationException;

class WeavingAccountResolver
{
    public function postableById(?int $id): ?Account
    {
        if (! $id) {
            return null;
        }

        return Account::query()->postable()->find($id);
    }

    /**
     * @param  array<string, mixed>  $lineMeta
     */
    public function resolveSource(string $source, ?int $partyAccountId, array $lineMeta = [], ?int $departmentId = null): ?Account
    {
        $settings = WeavingAccountSetting::current();

        return match ($source) {
            'party' => $this->postableById($partyAccountId),
            'sizing_party' => $this->postableById((int) ($lineMeta['sizing_party_account_id'] ?? $partyAccountId)),
            'expense', 'cc' => $this->resolveCostCenterAccount($lineMeta, $departmentId, $settings),
            default => $this->postableById($settings->accountIdForSource($source)),
        };
    }

    /**
     * @param  array<string, mixed>  $lineMeta
     */
    private function resolveCostCenterAccount(array $lineMeta, ?int $departmentId, WeavingAccountSetting $settings): ?Account
    {
        $ccAccountId = (int) ($lineMeta['cc_account_id'] ?? $lineMeta['expense_account_id'] ?? 0);
        if ($ccAccountId > 0) {
            return $this->postableById($ccAccountId);
        }

        if ($departmentId) {
            $deptAccountId = WeavingDepartment::query()->whereKey($departmentId)->value('expense_account_id');
            if ($deptAccountId) {
                return $this->postableById((int) $deptAccountId);
            }
        }

        return $this->postableById($settings->default_expense_account_id);
    }

    public function financialYearForDate(string $date): ?FinancialYear
    {
        return FinancialYear::query()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('is_closed', false)
            ->orderByDesc('start_date')
            ->first()
            ?? FinancialYear::query()->orderByDesc('start_date')->first();
    }

    public function requirePostable(?Account $account, string $label): Account
    {
        if (! $account instanceof Account) {
            throw ValidationException::withMessages([
                'account_id' => "{$label} sub-ledger is not set. Configure it under Weaving Master Data → Account Mapping, or pick CC / party on the form.",
            ]);
        }

        return $account;
    }
}
