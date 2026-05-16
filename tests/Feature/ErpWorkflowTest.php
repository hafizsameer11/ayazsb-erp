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
        $this->createTestFixtures();
    }

    private function createTestFixtures(): void
    {
        $superAdminRole = \App\Models\Role::query()->where('slug', 'super-admin')->firstOrFail();
        $admin = \App\Models\User::factory()->create([
            'name' => 'Test Admin',
            'username' => 'test-admin',
            'email' => 'admin@erp.local',
            'password' => 'admin123',
        ]);
        $admin->roles()->sync([$superAdminRole->id]);

        \App\Models\FinancialYear::query()->create([
            'year_code' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_closed' => false,
        ]);

        $assetHead = \App\Models\Account::query()->create([
            'level' => 'head',
            'code' => '01',
            'name' => 'Assets',
            'is_active' => true,
        ]);
        $liabilityHead = \App\Models\Account::query()->create([
            'level' => 'head',
            'code' => '02',
            'name' => 'Liabilities',
            'is_active' => true,
        ]);

        $customerAccount = $this->createAccountChain($assetHead, '01001', 'Test customer control', '010010001', 'Test customers', '01001000100001', 'Test customer account');
        $supplierAccount = $this->createAccountChain($liabilityHead, '02001', 'Test supplier control', '020010001', 'Test suppliers', '02001000100001', 'Test supplier account');

        $item = \App\Models\Item::query()->create([
            'code' => 'TY001',
            'name' => 'Test yarn',
            'module' => 'yarn',
            'unit' => 'BAGS',
        ]);

        $godown = \App\Models\Godown::query()->create([
            'code' => 'TG001',
            'name' => 'Test yarn godown',
            'module' => 'yarn',
        ]);

        \App\Models\YarnContract::query()->create([
            'contract_no' => 'TEST-PURCHASE-001',
            'contract_code' => 'YPCTEST-PURCHASE-001',
            'direction' => 'purchase',
            'contract_type' => 'PURCHASE',
            'contract_date' => now()->toDateString(),
            'payment_term' => 'cash',
            'account_id' => $supplierAccount->id,
            'item_id' => $item->id,
            'godown_id' => $godown->id,
            'unit' => 'LBS',
            'quantity' => 100,
            'packing_size' => 40,
            'weight_lbs' => 10000,
            'total_kgs' => 4535.97,
            'rate' => 500,
            'total_amount' => 5000000,
            'total_net_amount' => 5000000,
            'status' => 'open',
        ]);

        \App\Models\YarnContract::query()->create([
            'contract_no' => 'TEST-SALE-001',
            'contract_code' => 'YSCTEST-SALE-001',
            'direction' => 'sale',
            'contract_type' => 'SALE',
            'contract_date' => now()->toDateString(),
            'payment_term' => 'cash',
            'account_id' => $customerAccount->id,
            'item_id' => $item->id,
            'godown_id' => $godown->id,
            'unit' => 'LBS',
            'quantity' => 60,
            'packing_size' => 40,
            'weight_lbs' => 6000,
            'total_kgs' => 2721.58,
            'rate' => 520,
            'total_amount' => 3120000,
            'total_net_amount' => 3120000,
            'status' => 'open',
        ]);
    }

    private function createAccountChain(\App\Models\Account $head, string $controlCode, string $controlName, string $ledgerCode, string $ledgerName, string $subLedgerCode, string $subLedgerName): \App\Models\Account
    {
        $control = \App\Models\Account::query()->create([
            'level' => 'control',
            'code' => $controlCode,
            'name' => $controlName,
            'parent_id' => $head->id,
            'is_active' => true,
        ]);

        $ledger = \App\Models\Account::query()->create([
            'level' => 'ledger',
            'code' => $ledgerCode,
            'name' => $ledgerName,
            'parent_id' => $control->id,
            'is_active' => true,
        ]);

        return \App\Models\Account::query()->create([
            'level' => 'sub_ledger',
            'code' => $subLedgerCode,
            'name' => $subLedgerName,
            'parent_id' => $ledger->id,
            'is_active' => true,
        ]);
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
            'account_id' => $contract->account_id,
            'yarn_contract_id' => $contract->id,
            'from_godown_id' => $contract->godown_id,
            'item_id' => $item->id,
            'packing_size' => $contract->packing_size ?: 40,
            'quantity' => 5,
            'no_of_cones' => 0,
            'rate' => 50,
            'submit_action' => 'post',
            'meta' => ['voucher_type' => 'YPV'],
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

    public function test_yarn_contract_edit_loads_form_with_record_data(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $contract = \App\Models\YarnContract::query()->where('direction', 'purchase')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('erp.yarn.screen', [
                'screen' => 'purchase-contract',
                'edit' => $contract->id,
                'history_date' => $contract->contract_date->format('d-m-Y'),
            ]))
            ->assertOk()
            ->assertSee($contract->contract_no)
            ->assertSee('Update', false);
    }

    public function test_yarn_transaction_edit_loads_form_with_record_data(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $contract = \App\Models\YarnContract::query()->where('direction', 'purchase')->firstOrFail();
        $item = $contract->item ?? \App\Models\Item::query()->where('module', 'yarn')->firstOrFail();

        $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'purchase-contract-wise']), [
            'trans_date' => now()->toDateString(),
            'account_id' => $contract->account_id,
            'yarn_contract_id' => $contract->id,
            'from_godown_id' => $contract->godown_id,
            'item_id' => $item->id,
            'packing_size' => $contract->packing_size ?: 40,
            'quantity' => 2,
            'no_of_cones' => 0,
            'rate' => 10,
            'submit_action' => 'post',
            'meta' => ['voucher_type' => 'YPV'],
        ])->assertRedirect();

        $transaction = \App\Models\InventoryTransaction::query()
            ->where('screen_slug', 'purchase-contract-wise')
            ->latest()
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('erp.yarn.screen', [
                'screen' => 'purchase-contract-wise',
                'edit' => $transaction->id,
                'history_date' => \App\Support\ErpDate::display($transaction->trans_date),
            ]))
            ->assertOk()
            ->assertSee($transaction->trans_no)
            ->assertSee('Update', false);
    }

    public function test_yarn_contract_can_be_created_from_contract_screen(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();
        $item = \App\Models\Item::query()->where('module', 'yarn')->firstOrFail();
        $godown = \App\Models\Godown::query()->where('module', 'yarn')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'purchase-contract']), [
            'contract_no' => 'TEST-CNT-001',
            'contract_date' => now()->toDateString(),
            'payment_term' => 'cash',
            'account_id' => $account->id,
            'item_id' => $item->id,
            'packing_size' => $item->pack_size_cones ?: 40,
            'quantity' => 20,
            'no_of_cones' => 0,
            'rate' => 75,
            'status' => 'open',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('yarn_contracts', [
            'contract_no' => 'TEST-CNT-001',
            'direction' => 'purchase',
            'account_id' => $account->id,
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
            'account_id' => $from->account_id,
            'yarn_contract_id' => $from->id,
            'from_godown_id' => $from->godown_id,
            'item_id' => $item->id,
            'packing_size' => $from->packing_size ?: 40,
            'quantity' => 10,
            'no_of_cones' => 0,
            'rate' => 20,
            'submit_action' => 'post',
            'meta' => ['voucher_type' => 'YPV'],
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

    public function test_super_admin_can_soft_delete_voucher_and_it_disappears_from_lists(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $fy = \App\Models\FinancialYear::query()->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();

        $voucher = \App\Models\Voucher::query()->create([
            'module' => 'accounts',
            'voucher_type' => 'JV',
            'voucher_number' => 'JV-DELETE-TEST',
            'voucher_date' => now()->toDateString(),
            'financial_year_id' => $fy->id,
            'status' => 'draft',
            'total_debit' => 500,
            'total_credit' => 500,
            'total_amount' => 500,
            'created_by' => $admin->id,
        ]);

        \App\Models\VoucherLine::query()->create([
            'voucher_id' => $voucher->id,
            'account_id' => $account->id,
            'description' => 'Line',
            'debit' => 500,
            'credit' => 0,
        ]);

        $this->actingAs($admin)
            ->deleteJson(route('erp.accounts.vouchers.destroy', $voucher), [], [
                'Accept' => 'application/json',
            ])
            ->assertOk()
            ->assertJsonPath('redirect', route('erp.accounts.vouchers.jv', [
                'history_date' => \App\Support\ErpDate::display($voucher->voucher_date),
            ]));

        $this->assertSoftDeleted('vouchers', ['id' => $voucher->id]);
        $this->assertNull(\App\Models\Voucher::query()->find($voucher->id));

        $this->actingAs($admin)
            ->get(route('erp.accounts.vouchers.jv', ['edit' => $voucher->id]))
            ->assertNotFound();
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

