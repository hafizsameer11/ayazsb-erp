<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\FinancialYear;
use App\Models\Godown;
use App\Models\Item;
use App\Models\Party;
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
            ['level' => 'head', 'code' => '1000', 'name' => 'Assets'],
            ['level' => 'head', 'code' => '2000', 'name' => 'Liabilities'],
            ['level' => 'head', 'code' => '4000', 'name' => 'Revenue'],
            ['level' => 'head', 'code' => '5000', 'name' => 'Expenses'],
        ];

        foreach ($accounts as $account) {
            Account::query()->updateOrCreate(['code' => $account['code']], $account);
        }

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
    }
}

