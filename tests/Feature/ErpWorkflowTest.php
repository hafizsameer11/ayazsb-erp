<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErpWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_super_admin_can_create_voucher(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $fy = \App\Models\FinancialYear::query()->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();

        $response = $this->actingAs($admin)->post(route('erp.accounts.vouchers.store', ['voucherType' => 'jv']), [
            'voucher_date' => now()->toDateString(),
            'financial_year_id' => $fy->id,
            'remarks' => 'Test voucher',
            'lines' => [
                ['account_id' => $account->id, 'description' => 'Dr', 'debit' => 1000, 'credit' => 0],
                ['account_id' => $account->id, 'description' => 'Cr', 'debit' => 0, 'credit' => 1000],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vouchers', ['module' => 'accounts', 'voucher_type' => 'JV']);
    }

    public function test_reports_export_csv_works(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('erp.reports.export', ['screen' => 'accounts']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
    }

    public function test_posting_is_blocked_for_unbalanced_voucher(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $voucher = \App\Models\Voucher::query()->create([
            'module' => 'accounts',
            'voucher_type' => 'JV',
            'voucher_number' => 'JVTESTU001',
            'voucher_date' => now()->toDateString(),
            'status' => 'draft',
            'total_debit' => 1000,
            'total_credit' => 900,
            'total_amount' => 1000,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('erp.accounts.vouchers.post', ['voucher' => $voucher->id]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'status' => 'draft']);
    }

    public function test_posting_is_blocked_for_voucher_without_lines(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $voucher = \App\Models\Voucher::query()->create([
            'module' => 'accounts',
            'voucher_type' => 'JV',
            'voucher_number' => 'JVTESTN001',
            'voucher_date' => now()->toDateString(),
            'status' => 'draft',
            'total_debit' => 0,
            'total_credit' => 0,
            'total_amount' => 0,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('erp.accounts.vouchers.post', ['voucher' => $voucher->id]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id, 'status' => 'draft']);
    }

    public function test_yarn_transaction_can_be_saved_posted_and_printed(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $contract = \App\Models\YarnContract::query()->where('direction', 'purchase')->firstOrFail();
        $item = $contract->item ?? \App\Models\Item::query()->where('module', 'yarn')->firstOrFail();

        $purchase = $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'purchase-contract-wise']), [
            'trans_date' => now()->toDateString(),
            'yarn_contract_id' => $contract->id,
            'submit_action' => 'post',
            'lines' => [
                ['item_id' => $item->id, 'description' => 'Contract purchase', 'qty' => 5, 'weight_lbs' => 500, 'rate' => 50],
            ],
        ]);
        $purchase->assertRedirect();

        $save = $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'issuance']), [
            'trans_date' => now()->toDateString(),
            'yarn_contract_id' => $contract->id,
            'remarks' => 'Yarn issuance test',
            'lines' => [
                ['item_id' => $item->id, 'description' => 'Issue line', 'qty' => 2, 'weight_lbs' => 200, 'rate' => 50, 'amount' => 10000],
            ],
        ]);
        $save->assertRedirect();

        $transaction = \App\Models\InventoryTransaction::query()
            ->where('module', 'yarn')
            ->where('screen_slug', 'issuance')
            ->latest()
            ->firstOrFail();

        $post = $this->actingAs($admin)->post(route('erp.yarn.screen.post', [
            'screen' => 'issuance',
            'transaction' => $transaction->id,
        ]));
        $post->assertRedirect();
        $this->assertDatabaseHas('inventory_transactions', ['id' => $transaction->id, 'status' => 'posted']);

        $this->actingAs($admin)->get(route('erp.yarn.screen.print', [
            'screen' => 'issuance',
            'transaction' => $transaction->id,
        ]))->assertOk();
    }

    public function test_yarn_contract_can_be_created_from_contract_screen(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $party = \App\Models\Party::query()->firstOrFail();
        $item = \App\Models\Item::query()->where('module', 'yarn')->firstOrFail();
        $godown = \App\Models\Godown::query()->where('module', 'yarn')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'purchase-contract']), [
            'contract_no' => 'TEST-CNT-001',
            'contract_date' => now()->toDateString(),
            'contract_type' => 'BY RATE',
            'party_id' => $party->id,
            'item_id' => $item->id,
            'godown_id' => $godown->id,
            'quantity' => 20,
            'weight_lbs' => 2000,
            'rate' => 75,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('yarn_contracts', [
            'contract_no' => 'TEST-CNT-001',
            'direction' => 'purchase',
            'party_id' => $party->id,
        ]);

        $this->actingAs($admin)
            ->get(route('erp.yarn.screen', ['screen' => 'purchase-contract']))
            ->assertOk();
    }

    public function test_yarn_dedicated_screens_render(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();

        foreach ([
            'purchase-contract',
            'purchase-contract-wise',
            'sale-contract',
            'sale-contract-wise',
            'issuance',
            'issuance-return',
            'issuance-transfer',
            'godown-transfer',
            'gain-shortage',
        ] as $screen) {
            $this->actingAs($admin)
                ->get(route('erp.yarn.screen', ['screen' => $screen]))
                ->assertOk();
        }
    }

    public function test_yarn_contract_balance_tracks_purchase_issue_return_transfer_and_adjustment(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $from = \App\Models\YarnContract::query()->where('direction', 'purchase')->firstOrFail();
        $to = \App\Models\YarnContract::query()->where('direction', 'sale')->firstOrFail();
        $item = $from->item ?? \App\Models\Item::query()->where('module', 'yarn')->firstOrFail();

        $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'purchase-contract-wise']), [
            'trans_date' => now()->toDateString(),
            'yarn_contract_id' => $from->id,
            'submit_action' => 'post',
            'lines' => [
                ['item_id' => $item->id, 'description' => 'Purchase', 'qty' => 10, 'weight_lbs' => 1000, 'rate' => 20],
            ],
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'issuance']), [
            'trans_date' => now()->toDateString(),
            'yarn_contract_id' => $from->id,
            'submit_action' => 'post',
            'lines' => [
                ['item_id' => $item->id, 'description' => 'Issue', 'qty' => 3, 'weight_lbs' => 300, 'rate' => 20, 'meta' => ['yarn_type' => 'WARP']],
            ],
        ])->assertRedirect();

        $issue = \App\Models\InventoryTransaction::query()->where('screen_slug', 'issuance')->where('yarn_contract_id', $from->id)->latest()->firstOrFail();

        $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'issuance-return']), [
            'trans_date' => now()->toDateString(),
            'yarn_contract_id' => $from->id,
            'source_transaction_id' => $issue->id,
            'submit_action' => 'post',
            'lines' => [
                ['item_id' => $item->id, 'description' => 'Return', 'qty' => 1, 'weight_lbs' => 50, 'rate' => 20],
            ],
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'issuance-transfer']), [
            'trans_date' => now()->toDateString(),
            'from_yarn_contract_id' => $from->id,
            'to_yarn_contract_id' => $to->id,
            'submit_action' => 'post',
            'lines' => [
                ['item_id' => $item->id, 'description' => 'Transfer', 'qty' => 1, 'weight_lbs' => 100, 'rate' => 20, 'meta' => ['transfer_rate' => 22]],
            ],
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'gain-shortage']), [
            'trans_date' => now()->toDateString(),
            'yarn_contract_id' => $from->id,
            'source_transaction_id' => $issue->id,
            'submit_action' => 'post',
            'lines' => [
                ['item_id' => $item->id, 'description' => 'Gain', 'qty' => 1, 'weight_lbs' => 25, 'rate' => 20, 'meta' => ['adjustment_type' => 'gain']],
                ['item_id' => $item->id, 'description' => 'Shortage', 'qty' => 1, 'weight_lbs' => 10, 'rate' => 20, 'meta' => ['adjustment_type' => 'shortage']],
            ],
        ])->assertRedirect();

        $snapshot = app(\App\Services\YarnContractBalanceService::class)->snapshot($from->fresh());

        $this->assertEqualsWithDelta(1000, $snapshot['purchased_weight_lbs'], 0.001);
        $this->assertEqualsWithDelta(300, $snapshot['issued_weight_lbs'], 0.001);
        $this->assertEqualsWithDelta(50, $snapshot['returned_weight_lbs'], 0.001);
        $this->assertEqualsWithDelta(100, $snapshot['transferred_out_weight_lbs'], 0.001);
        $this->assertEqualsWithDelta(25, $snapshot['gain_weight_lbs'], 0.001);
        $this->assertEqualsWithDelta(10, $snapshot['shortage_weight_lbs'], 0.001);
        $this->assertEqualsWithDelta(665, $snapshot['available_weight_lbs'], 0.001);
    }

    public function test_reports_view_and_print_work_for_all_modules(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();

        foreach (['accounts', 'yarn', 'grey'] as $screen) {
            $this->actingAs($admin)
                ->get(route('erp.reports.view', ['screen' => $screen, 'from_date' => now()->subDays(30)->toDateString(), 'to_date' => now()->toDateString()]))
                ->assertOk();

            $this->actingAs($admin)
                ->get(route('erp.reports.print', ['screen' => $screen]))
                ->assertOk();

            $this->actingAs($admin)
                ->get(route('erp.reports.export', ['screen' => $screen]))
                ->assertOk();
        }
    }
}

