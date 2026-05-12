<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\FinancialYear;
use App\Models\Godown;
use App\Models\Item;
use App\Models\Party;
use App\Models\YarnContract;
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

        $this->seedDemoPostableAccounts();

        $parties = [
            ['code' => 'P001', 'name' => 'ATF Weaving', 'type' => 'both'],
            ['code' => 'P002', 'name' => 'Master Younas Weaving', 'type' => 'supplier'],
            ['code' => 'P003', 'name' => 'Saqib Manzoor', 'type' => 'customer'],
        ];
        foreach ($parties as $party) {
            Party::query()->updateOrCreate(['code' => $party['code']], $party);
        }

        $godowns = [
            ['code' => 'G001', 'name' => 'Grey Godown', 'module' => 'grey'],
            ['code' => 'G002', 'name' => 'DO Stock', 'module' => 'yarn'],
        ];
        foreach ($godowns as $godown) {
            Godown::query()->updateOrCreate(['code' => $godown['code']], $godown);
        }

        $items = [
            ['code' => 'Y001', 'name' => 'Staple 42/1', 'module' => 'yarn', 'unit' => 'BAGS'],
            ['code' => 'Y002', 'name' => 'Cotton 40/1', 'module' => 'yarn', 'unit' => 'BAGS'],
            ['code' => 'GQ001', 'name' => 'Grey Dora 58x46', 'module' => 'grey', 'unit' => 'MTR'],
        ];
        foreach ($items as $item) {
            Item::query()->updateOrCreate(['code' => $item['code']], $item);
        }

        $this->seedYarnContracts();
    }

    /**
     * Sample control → ledger → sub-ledger chains for vouchers and openings (posting uses leaf codes only).
     */
    protected function seedDemoPostableAccounts(): void
    {
        $this->seedAccountChain(
            '01',
            '01001',
            'Current assets',
            '010010001',
            'Bank balances',
            '01001000100001',
            'Main bank — operating'
        );
        $this->seedAccountChain(
            '02',
            '02001',
            'Current liabilities',
            '020010001',
            'Payables',
            '02001000100001',
            'Trade creditors'
        );
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

    protected function seedYarnContracts(): void
    {
        $supplier = Party::query()->where('code', 'P002')->first();
        $customer = Party::query()->where('code', 'P003')->first();
        $item = Item::query()->where('code', 'Y001')->first();
        $godown = Godown::query()->where('code', 'G002')->first();

        if (! $supplier || ! $customer || ! $item || ! $godown) {
            return;
        }

        YarnContract::query()->updateOrCreate(
            ['contract_no' => 'YC-P-0001', 'direction' => 'purchase'],
            [
                'contract_type' => 'BY RATE',
                'contract_date' => now()->toDateString(),
                'party_id' => $supplier->id,
                'item_id' => $item->id,
                'godown_id' => $godown->id,
                'yarn_tag' => 'FRESH',
                'condition' => 'GOOD',
                'unit' => 'LBS',
                'quantity' => 100,
                'weight_lbs' => 10000,
                'packing_size' => 100,
                'packing_weight' => 100,
                'rate' => 500,
                'sale_rate' => 525,
                'status' => 'open',
            ]
        );

        YarnContract::query()->updateOrCreate(
            ['contract_no' => 'YC-S-0001', 'direction' => 'sale'],
            [
                'contract_type' => 'EMANI',
                'contract_date' => now()->toDateString(),
                'party_id' => $customer->id,
                'item_id' => $item->id,
                'godown_id' => $godown->id,
                'yarn_tag' => 'EMANI',
                'condition' => 'GOOD',
                'unit' => 'LBS',
                'quantity' => 60,
                'weight_lbs' => 6000,
                'packing_size' => 100,
                'packing_weight' => 100,
                'rate' => 520,
                'status' => 'open',
            ]
        );
    }
}

