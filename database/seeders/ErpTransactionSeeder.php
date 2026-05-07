<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\FinancialYear;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionLine;
use App\Models\Item;
use App\Models\Party;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Illuminate\Database\Seeder;

class ErpTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();
        $year = FinancialYear::query()->first();
        $party = Party::query()->first();
        $item = Item::query()->first();
        $account = Account::query()->first();

        if (! $user || ! $year || ! $party || ! $item || ! $account) {
            return;
        }

        $voucher = Voucher::query()->updateOrCreate(
            ['module' => 'accounts', 'voucher_type' => 'JV', 'voucher_number' => 'JV' . $year->year_code . '00001'],
            [
                'voucher_date' => now()->toDateString(),
                'financial_year_id' => $year->id,
                'party_id' => $party->id,
                'status' => 'draft',
                'total_debit' => 10000,
                'total_credit' => 10000,
                'total_amount' => 10000,
                'created_by' => $user->id,
            ]
        );

        VoucherLine::query()->updateOrCreate(
            ['voucher_id' => $voucher->id, 'account_id' => $account->id, 'description' => 'Seed debit'],
            ['debit' => 10000, 'credit' => 0, 'amount' => 10000]
        );
        VoucherLine::query()->updateOrCreate(
            ['voucher_id' => $voucher->id, 'description' => 'Seed credit'],
            ['account_id' => $account->id, 'debit' => 0, 'credit' => 10000, 'amount' => 10000]
        );

        $inv = InventoryTransaction::query()->updateOrCreate(
            ['module' => 'yarn', 'screen_slug' => 'issuance', 'trans_no' => 'YAR202600001'],
            [
                'trans_date' => now()->toDateString(),
                'party_id' => $party->id,
                'status' => 'draft',
                'total_qty' => 10,
                'total_amount' => 5000,
                'created_by' => $user->id,
            ]
        );

        InventoryTransactionLine::query()->updateOrCreate(
            ['inventory_transaction_id' => $inv->id, 'item_id' => $item->id],
            [
                'description' => 'Seed line',
                'qty' => 10,
                'unit' => $item->unit,
                'rate' => 500,
                'amount' => 5000,
            ]
        );
    }
}

