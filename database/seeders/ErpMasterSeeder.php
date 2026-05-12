<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\FinancialYear;
use Illuminate\Database\Seeder;

class ErpMasterSeeder extends Seeder
{
    public function run(): void
    {
        FinancialYear::query()->updateOrCreate(
            ['year_code' => '2026'],
            [
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_closed' => false,
                'description' => 'Default financial year',
            ]
        );

        $accounts = [
            ['level' => 'head', 'code' => '01', 'name' => 'Assets'],
            ['level' => 'head', 'code' => '02', 'name' => 'Liabilities'],
            ['level' => 'head', 'code' => '03', 'name' => 'Revenue'],
            ['level' => 'head', 'code' => '04', 'name' => 'Expenses'],
        ];

        foreach ($accounts as $account) {
            Account::query()->updateOrCreate(
                ['level' => 'head', 'name' => $account['name']],
                [
                    'code' => $account['code'],
                    'parent_id' => null,
                    'is_active' => true,
                ]
            );
        }

    }

    protected function seedAccountChain(
        string $headCode,
        string $controlCode,
        string $controlName,
        string $ledgerCode,
        string $ledgerName,
        string $subLedgerCode,
        string $subLedgerName,
    ): void {
        $head = Account::query()->where('level', 'head')->where('code', $headCode)->first();
        if (! $head) {
            return;
        }

        $control = Account::query()->updateOrCreate(
            ['code' => $controlCode],
            [
                'level' => 'control',
                'name' => $controlName,
                'parent_id' => $head->id,
                'is_active' => true,
            ]
        );

        $ledger = Account::query()->updateOrCreate(
            ['code' => $ledgerCode],
            [
                'level' => 'ledger',
                'name' => $ledgerName,
                'parent_id' => $control->id,
                'is_active' => true,
            ]
        );

        Account::query()->updateOrCreate(
            ['code' => $subLedgerCode],
            [
                'level' => 'sub_ledger',
                'name' => $subLedgerName,
                'parent_id' => $ledger->id,
                'is_active' => true,
            ]
        );
    }
}

