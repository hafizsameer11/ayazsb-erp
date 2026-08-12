<?php

namespace App\Services;

use App\Models\Account;
use App\Models\FinancialYear;
use App\Models\YarnAccountSetting;
use Illuminate\Validation\ValidationException;

class YarnAccountResolver
{
    public function postableById(?int $id): ?Account
    {
        if (! $id) {
            return null;
        }

        return Account::query()->postable()->find($id);
    }

    public function resolveSource(string $source, ?int $partyAccountId): ?Account
    {
        if ($source === 'party') {
            return $this->postableById($partyAccountId);
        }

        $settings = YarnAccountSetting::current();

        return $this->postableById($settings->accountIdForSource($source));
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
                'account_id' => "{$label} sub-ledger is not configured. Set it under Yarn Master Data → Account Mapping.",
            ]);
        }

        return $account;
    }
}
