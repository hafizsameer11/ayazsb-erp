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
        $yarnStockAccount = $this->createAccountChain($assetHead, '01002', 'Yarn stock control', '010020001', 'Yarn stock ledger', '01002000100001', 'Yarn stock account');
        $yarnSalesAccount = $this->createAccountChain($assetHead, '01003', 'Yarn sales control', '010030001', 'Yarn sales ledger', '01003000100001', 'Yarn sales account');

        \App\Models\YarnAccountSetting::current()->update([
            'yarn_stock_account_id' => $yarnStockAccount->id,
            'yarn_sales_account_id' => $yarnSalesAccount->id,
        ]);

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

        $greyQuality = \App\Models\GreyQuality::query()->create([
            'quality_no' => '100900',
            'quality_name' => '64 X 60 TEST GREY',
            'is_active' => true,
        ]);

        \App\Models\GreyConversionContract::query()->create([
            'contract_no' => 'GREY-CONV-001',
            'contract_code' => 'GCC001',
            'contract_type' => 'CONV',
            'contract_date' => now()->toDateString(),
            'status' => 'running',
            'account_id' => $supplierAccount->id,
            'grey_quality_id' => $greyQuality->id,
            'qty_mtr' => 5000,
            'required_bags' => 100,
            'per_mtr_rate' => 10,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function yarnIssuancePayload(\App\Models\YarnContract $contract, \App\Models\Item $item, array $lineOverrides = []): array
    {
        $greyContract = \App\Models\GreyConversionContract::query()->firstOrFail();

        return [
            'trans_date' => now()->toDateString(),
            'account_id' => $contract->account_id,
            'grey_conversion_contract_id' => $greyContract->id,
            'meta' => ['yarn_contract_id' => $contract->id],
            'from_godown_id' => $contract->godown_id,
            'submit_action' => 'post',
            'lines' => [
                array_merge([
                    'item_id' => $item->id,
                    'description' => 'Issue line',
                    'qty' => 2,
                    'meta' => [
                        'yarn_type' => 'any',
                        'packing_size' => $contract->packing_size ?: 40,
                        'no_of_cones' => 0,
                    ],
                    'rate' => 50,
                ], $lineOverrides),
            ],
        ];
    }

    /**
     * Simulates AJAX/form submit where the hidden item_id field is empty but contract is selected.
     *
     * @return array<string, mixed>
     */
    private function yarnContractWisePayload(\App\Models\YarnContract $contract, string $screen, array $overrides = []): array
    {
        return array_merge([
            'trans_date' => now()->toDateString(),
            'account_id' => $contract->account_id,
            'yarn_contract_id' => $contract->id,
            'from_godown_id' => $contract->godown_id,
            'item_id' => '',
            'packing_size' => $contract->packing_size ?: 40,
            'quantity' => 2,
            'no_of_cones' => 0,
            'rate' => $screen === 'sale-contract-wise' ? 150 : 50,
            'submit_action' => 'post',
            'meta' => ['voucher_type' => $screen === 'sale-contract-wise' ? 'YSV' : 'YPV'],
        ], $overrides);
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

    public function test_cash_payment_voucher_can_save_without_balanced_sides(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $fy = \App\Models\FinancialYear::query()->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();

        $response = $this->actingAs($admin)->post(route('erp.accounts.vouchers.store', ['voucherType' => 'cp']), [
            'voucher_date' => now()->toDateString(),
            'financial_year_id' => $fy->id,
            'remarks' => 'Unbalanced CP voucher',
            'lines' => [
                ['account_id' => $account->id, 'description' => 'Cash payment line', 'debit' => 0, 'credit' => 1000],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vouchers', [
            'module' => 'accounts',
            'voucher_type' => 'CP',
            'total_debit' => 0,
            'total_credit' => 1000,
            'status' => 'posted',
        ]);
    }

    public function test_cash_voucher_can_save_without_balanced_sides(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $fy = \App\Models\FinancialYear::query()->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();

        $response = $this->actingAs($admin)->post(route('erp.accounts.vouchers.store', ['voucherType' => 'cv']), [
            'voucher_date' => now()->toDateString(),
            'financial_year_id' => $fy->id,
            'remarks' => 'Unbalanced CV voucher',
            'lines' => [
                ['account_id' => $account->id, 'description' => 'Cash voucher line', 'debit' => 1500, 'credit' => 0],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vouchers', [
            'module' => 'accounts',
            'voucher_type' => 'CV',
            'total_debit' => 1500,
            'total_credit' => 0,
            'status' => 'posted',
        ]);
    }

    public function test_bank_payment_voucher_saves_instrument_meta(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $fy = \App\Models\FinancialYear::query()->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();

        $response = $this->actingAs($admin)->post(route('erp.accounts.vouchers.store', ['voucherType' => 'bpv']), [
            'voucher_date' => now()->toDateString(),
            'financial_year_id' => $fy->id,
            'remarks' => 'BPV with instrument',
            'lines' => [
                [
                    'account_id' => $account->id,
                    'description' => 'Bank payment line',
                    'debit' => 1000,
                    'credit' => 0,
                    'meta' => [
                        'instrument_no' => 'CHK-1001',
                        'instrument_date' => '06-07-2026',
                        'title' => 'OFFICE',
                    ],
                ],
                [
                    'account_id' => $account->id,
                    'description' => 'Balancing line',
                    'debit' => 0,
                    'credit' => 1000,
                    'meta' => [
                        'instrument_no' => 'CHK-1002',
                        'instrument_date' => '06-07-2026',
                        'title' => 'OFFICE',
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();

        $voucher = \App\Models\Voucher::query()
            ->where('module', 'accounts')
            ->where('voucher_type', 'BPV')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('CHK-1001', $voucher->lines()->orderBy('id')->firstOrFail()->meta['instrument_no'] ?? null);
    }

    public function test_bank_payment_voucher_can_save_without_balanced_sides(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $fy = \App\Models\FinancialYear::query()->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();

        $response = $this->actingAs($admin)->post(route('erp.accounts.vouchers.store', ['voucherType' => 'bpv']), [
            'voucher_date' => now()->toDateString(),
            'financial_year_id' => $fy->id,
            'remarks' => 'Unbalanced BPV voucher',
            'lines' => [
                [
                    'account_id' => $account->id,
                    'description' => 'Bank payment line',
                    'debit' => 0,
                    'credit' => 2000,
                    'meta' => [
                        'instrument_no' => 'CHK-1101',
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vouchers', [
            'module' => 'accounts',
            'voucher_type' => 'BPV',
            'total_debit' => 0,
            'total_credit' => 2000,
            'status' => 'posted',
        ]);
    }

    public function test_cash_receipt_and_bank_receipt_can_save_without_balanced_sides(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $fy = \App\Models\FinancialYear::query()->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();

        $this->actingAs($admin)->post(route('erp.accounts.vouchers.store', ['voucherType' => 'cr']), [
            'voucher_date' => now()->toDateString(),
            'financial_year_id' => $fy->id,
            'remarks' => 'Unbalanced CR voucher',
            'lines' => [
                ['account_id' => $account->id, 'description' => 'Cash receipt line', 'debit' => 900, 'credit' => 0],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('vouchers', [
            'module' => 'accounts',
            'voucher_type' => 'CR',
            'total_debit' => 900,
            'total_credit' => 0,
            'status' => 'posted',
        ]);

        $this->actingAs($admin)->post(route('erp.accounts.vouchers.store', ['voucherType' => 'brv']), [
            'voucher_date' => now()->toDateString(),
            'financial_year_id' => $fy->id,
            'remarks' => 'Unbalanced BRV voucher',
            'lines' => [
                [
                    'account_id' => $account->id,
                    'description' => 'Bank receipt line',
                    'debit' => 1100,
                    'credit' => 0,
                    'meta' => ['instrument_no' => 'CHK-1201'],
                ],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('vouchers', [
            'module' => 'accounts',
            'voucher_type' => 'BRV',
            'total_debit' => 1100,
            'total_credit' => 0,
            'status' => 'posted',
        ]);
    }

    public function test_journal_voucher_still_requires_balanced_sides(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $fy = \App\Models\FinancialYear::query()->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();

        $response = $this->from(route('erp.accounts.vouchers.jv'))
            ->actingAs($admin)
            ->post(route('erp.accounts.vouchers.store', ['voucherType' => 'jv']), [
                'voucher_date' => now()->toDateString(),
                'financial_year_id' => $fy->id,
                'remarks' => 'Unbalanced JV must fail',
                'lines' => [
                    ['account_id' => $account->id, 'description' => 'Dr only', 'debit' => 1000, 'credit' => 0],
                ],
            ]);

        $response->assertRedirect(route('erp.accounts.vouchers.jv'));
        $response->assertSessionHasErrors('lines');
    }

    public function test_bank_payment_voucher_rejects_duplicate_instrument_numbers(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $fy = \App\Models\FinancialYear::query()->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();

        $response = $this->from(route('erp.accounts.vouchers.bpv'))
            ->actingAs($admin)
            ->post(route('erp.accounts.vouchers.store', ['voucherType' => 'bpv']), [
                'voucher_date' => now()->toDateString(),
                'financial_year_id' => $fy->id,
                'remarks' => 'Duplicate instrument BPV',
                'lines' => [
                    [
                        'account_id' => $account->id,
                        'description' => 'Line 1',
                        'debit' => 1000,
                        'credit' => 0,
                        'meta' => ['instrument_no' => 'CHK-2001'],
                    ],
                    [
                        'account_id' => $account->id,
                        'description' => 'Line 2',
                        'debit' => 0,
                        'credit' => 1000,
                        'meta' => ['instrument_no' => 'CHK-2001'],
                    ],
                ],
            ]);

        $response->assertRedirect(route('erp.accounts.vouchers.bpv'));
        $response->assertSessionHasErrors('lines');
    }

    public function test_bank_payment_voucher_rejects_instrument_number_used_on_another_voucher(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $fy = \App\Models\FinancialYear::query()->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();

        $this->actingAs($admin)->post(route('erp.accounts.vouchers.store', ['voucherType' => 'bpv']), [
            'voucher_date' => now()->toDateString(),
            'financial_year_id' => $fy->id,
            'remarks' => 'First BPV',
            'lines' => [
                [
                    'account_id' => $account->id,
                    'description' => 'Line 1',
                    'debit' => 0,
                    'credit' => 1500,
                    'meta' => ['instrument_no' => 'CHK-3001'],
                ],
            ],
        ])->assertRedirect();

        $response = $this->from(route('erp.accounts.vouchers.bpv'))
            ->actingAs($admin)
            ->post(route('erp.accounts.vouchers.store', ['voucherType' => 'bpv']), [
                'voucher_date' => now()->addDay()->toDateString(),
                'financial_year_id' => $fy->id,
                'remarks' => 'Duplicate instrument on another date',
                'lines' => [
                    [
                        'account_id' => $account->id,
                        'description' => 'Line 1',
                        'debit' => 0,
                        'credit' => 2500,
                        'meta' => ['instrument_no' => 'CHK-3001'],
                    ],
                ],
            ]);

        $response->assertRedirect(route('erp.accounts.vouchers.bpv'));
        $response->assertSessionHasErrors('lines');
    }

    public function test_bank_payment_voucher_update_allows_same_instrument_on_same_voucher(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $fy = \App\Models\FinancialYear::query()->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();

        $this->actingAs($admin)->post(route('erp.accounts.vouchers.store', ['voucherType' => 'bpv']), [
            'voucher_date' => now()->toDateString(),
            'financial_year_id' => $fy->id,
            'remarks' => 'Editable BPV',
            'lines' => [
                [
                    'account_id' => $account->id,
                    'description' => 'Line 1',
                    'debit' => 0,
                    'credit' => 1800,
                    'meta' => ['instrument_no' => 'CHK-3101'],
                ],
            ],
        ])->assertRedirect();

        $voucher = \App\Models\Voucher::query()
            ->where('module', 'accounts')
            ->where('voucher_type', 'BPV')
            ->where('remarks', 'Editable BPV')
            ->firstOrFail();

        $response = $this->actingAs($admin)->patch(route('erp.accounts.vouchers.update', $voucher), [
            'voucher_date' => now()->addDays(2)->toDateString(),
            'financial_year_id' => $fy->id,
            'remarks' => 'Editable BPV updated',
            'lines' => [
                [
                    'account_id' => $account->id,
                    'description' => 'Line 1 updated',
                    'debit' => 0,
                    'credit' => 1900,
                    'meta' => ['instrument_no' => 'CHK-3101'],
                ],
            ],
        ]);

        $response->assertRedirect();
        $voucher->refresh();
        $this->assertSame('Editable BPV updated', $voucher->remarks);
        $this->assertSame('CHK-3101', $voucher->lines()->firstOrFail()->meta['instrument_no'] ?? null);
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

        $fallbackPurchase = $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'purchase-contract-wise']), [
            'trans_date' => now()->toDateString(),
            'account_id' => $contract->account_id,
            'yarn_contract_id' => $contract->id,
            'from_godown_id' => $contract->godown_id,
            'packing_size' => $contract->packing_size ?: 40,
            'quantity' => 3,
            'no_of_cones' => 0,
            'rate' => 50,
            'submit_action' => 'post',
            'meta' => ['voucher_type' => 'YPV'],
        ]);
        $fallbackPurchase->assertRedirect();

        $this->assertDatabaseHas('inventory_transaction_lines', [
            'item_id' => $item->id,
            'qty' => 3,
        ]);

        $saleContract = \App\Models\YarnContract::query()->where('direction', 'sale')->firstOrFail();

        $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'sale-contract-wise']), [
            'trans_date' => now()->toDateString(),
            'account_id' => $saleContract->account_id,
            'yarn_contract_id' => $saleContract->id,
            'from_godown_id' => $saleContract->godown_id,
            'packing_size' => $saleContract->packing_size ?: 40,
            'quantity' => 2,
            'no_of_cones' => 0,
            'rate' => 150,
            'submit_action' => 'post',
            'meta' => ['voucher_type' => 'YSV'],
        ])->assertRedirect();

        $save = $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'issuance']), array_merge(
            $this->yarnIssuancePayload($contract, $item),
            ['remarks' => 'Yarn issuance test'],
        ));
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
            'purchase-without-contract',
            'sale-contract',
            'sale-contract-wise',
            'sale-without-contract',
            'issuance',
            'issuance-return',
            'issuance-transfer',
            'receipt-processed',
            'receipt-processed-auto',
            'godown-transfer',
            'loom-transfer',
            'gain-shortage',
            'opening',
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

        $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'issuance']), array_merge(
            $this->yarnIssuancePayload($from, $item, ['qty' => 3, 'rate' => 20, 'meta' => ['yarn_type' => 'warp', 'packing_size' => $from->packing_size ?: 40, 'no_of_cones' => 0]]),
        ))->assertRedirect();

        $issue = \App\Models\InventoryTransaction::query()->where('screen_slug', 'issuance')->latest()->firstOrFail();

        $greyContract = \App\Models\GreyConversionContract::query()->firstOrFail();

        $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'issuance-return']), [
            'trans_date' => now()->toDateString(),
            'account_id' => $from->account_id,
            'grey_conversion_contract_id' => $greyContract->id,
            'source_transaction_id' => $issue->id,
            'from_godown_id' => $from->godown_id,
            'meta' => ['yarn_contract_id' => $from->id],
            'submit_action' => 'post',
            'lines' => [
                ['item_id' => $item->id, 'description' => 'Return', 'qty' => 1, 'meta' => ['packing_size' => $from->packing_size ?: 40, 'no_of_cones' => 0], 'rate' => 20],
            ],
        ])->assertRedirect();

        $toGrey = \App\Models\GreyConversionContract::query()->create([
            'contract_no' => 'GREY-CONV-002',
            'contract_code' => 'GCC002',
            'contract_type' => 'CONV',
            'contract_date' => now()->toDateString(),
            'status' => 'running',
            'account_id' => $to->account_id,
            'grey_quality_id' => $greyContract->grey_quality_id,
            'qty_mtr' => 2000,
            'per_mtr_rate' => 12,
        ]);

        $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'issuance-transfer']), [
            'trans_date' => now()->toDateString(),
            'from_account_id' => $from->account_id,
            'from_grey_conversion_contract_id' => $greyContract->id,
            'to_account_id' => $to->account_id,
            'to_grey_conversion_contract_id' => $toGrey->id,
            'from_godown_id' => $from->godown_id,
            'meta' => [
                'yarn_contract_id' => $from->id,
                'from_yarn_contract_id' => $from->id,
                'to_yarn_contract_id' => $to->id,
                'from_grey_conversion_contract_id' => $greyContract->id,
            ],
            'submit_action' => 'post',
            'lines' => [
                ['item_id' => $item->id, 'description' => 'Transfer', 'qty' => 1, 'meta' => ['packing_size' => $from->packing_size ?: 40, 'no_of_cones' => 0], 'rate' => 20],
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
        $this->assertEqualsWithDelta(100, $snapshot['returned_weight_lbs'], 0.001);
        $this->assertEqualsWithDelta(100, $snapshot['transferred_out_weight_lbs'], 0.001);
        $this->assertEqualsWithDelta(100, $snapshot['gain_weight_lbs'], 0.001);
        $this->assertEqualsWithDelta(100, $snapshot['shortage_weight_lbs'], 0.001);
        $this->assertEqualsWithDelta(700, $snapshot['available_weight_lbs'], 0.001);
    }

    public function test_yarn_purchase_and_sale_without_contract_post_to_ledger_and_stock(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $supplier = \App\Models\Account::query()->where('code', '02001000100001')->firstOrFail();
        $customer = \App\Models\Account::query()->where('code', '01001000100001')->firstOrFail();
        $yarnStock = \App\Models\Account::query()->where('code', '01002000100001')->firstOrFail();
        $item = \App\Models\Item::query()->where('module', 'yarn')->firstOrFail();
        $godown = \App\Models\Godown::query()->where('module', 'yarn')->firstOrFail();

        $stockBefore = collect(app(\App\Services\YarnStockAvailabilityService::class)->yarnItemsPayload())
            ->firstWhere('id', $item->id)['available_bags'] ?? 0;

        $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'purchase-without-contract']), [
            'trans_date' => now()->toDateString(),
            'account_id' => $supplier->id,
            'from_godown_id' => $godown->id,
            'item_id' => $item->id,
            'packing_size' => 40,
            'quantity' => 10,
            'no_of_cones' => 0,
            'rate' => 100,
            'submit_action' => 'post',
            'meta' => ['voucher_type' => 'YPV'],
        ])->assertRedirect();

        $purchase = \App\Models\InventoryTransaction::query()
            ->where('screen_slug', 'purchase-without-contract')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('posted', $purchase->status);
        $this->assertNotEmpty($purchase->meta['voucher_id'] ?? null);

        $purchaseVoucher = \App\Models\Voucher::query()->findOrFail($purchase->meta['voucher_id']);
        $this->assertSame('posted', $purchaseVoucher->status);
        $this->assertDatabaseHas('voucher_lines', [
            'voucher_id' => $purchaseVoucher->id,
            'account_id' => $yarnStock->id,
            'debit' => 100000,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('voucher_lines', [
            'voucher_id' => $purchaseVoucher->id,
            'account_id' => $supplier->id,
            'debit' => 0,
            'credit' => 100000,
        ]);

        $stockAfterPurchase = collect(app(\App\Services\YarnStockAvailabilityService::class)->yarnItemsPayload())
            ->firstWhere('id', $item->id)['available_bags'] ?? 0;
        $this->assertEqualsWithDelta($stockBefore + 10, $stockAfterPurchase, 0.001);

        $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'sale-without-contract']), [
            'trans_date' => now()->toDateString(),
            'account_id' => $customer->id,
            'from_godown_id' => $godown->id,
            'item_id' => $item->id,
            'packing_size' => 40,
            'quantity' => 4,
            'no_of_cones' => 0,
            'rate' => 120,
            'submit_action' => 'post',
            'meta' => ['voucher_type' => 'YSV'],
        ])->assertRedirect();

        $sale = \App\Models\InventoryTransaction::query()
            ->where('screen_slug', 'sale-without-contract')
            ->latest('id')
            ->firstOrFail();

        $this->assertNotEmpty($sale->meta['voucher_id'] ?? null);
        $this->assertDatabaseHas('voucher_lines', [
            'voucher_id' => $sale->meta['voucher_id'],
            'account_id' => $customer->id,
            'debit' => 48000,
            'credit' => 0,
        ]);

        $stockAfterSale = collect(app(\App\Services\YarnStockAvailabilityService::class)->yarnItemsPayload())
            ->firstWhere('id', $item->id)['available_bags'] ?? 0;
        $this->assertEqualsWithDelta($stockBefore + 6, $stockAfterSale, 0.001);
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

    public function test_grey_dedicated_screens_and_master_data_work(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $supplier = \App\Models\Account::query()->postable()->orderBy('code')->firstOrFail();

        foreach (['purchase', 'sale', 'conversion-contract', 'conversion-inward', 'opening'] as $screen) {
            $this->actingAs($admin)
                ->get(route('erp.grey.screen', ['screen' => $screen]))
                ->assertOk();
        }

        $this->actingAs($admin)
            ->get(route('erp.grey.master-data'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('erp.grey.master-data.store'), [
                'tab' => 'master',
                'quality_no' => '100901',
                'tag' => 'CONVERSION',
                'season' => 'WINTER',
                'is_active' => 1,
                'reed' => 64,
                'pick' => 60,
                'width' => 48,
                'total_ends' => 3000,
                'color' => 'GREY',
                'details' => [
                    ['nature' => 'WARP', 'ends' => 3000, 'picks' => 60],
                ],
            ])
            ->assertRedirect();

        $quality = \App\Models\GreyQuality::query()->where('quality_no', '100901')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('erp.grey.screen.store', ['screen' => 'purchase']), [
                'trans_date' => now()->toDateString(),
                'account_id' => $supplier->id,
                'meta' => [
                    'voucher_type' => 'GPV',
                    'than_qty' => 10,
                    'long_qty' => 0,
                    'short_qty' => 2,
                    'grey_rate_mtr' => 100,
                    'commission_percent' => 0,
                    'brokery_rate' => 0,
                    'checker_rate_mtr' => 0,
                    'munshiana' => 0,
                ],
                'lines' => [
                    [
                        'description' => 'Grey purchase',
                        'qty' => 12,
                        'rate' => 100,
                        'amount' => 1200,
                        'meta' => ['grey_quality_id' => $quality->id],
                    ],
                ],
                'submit_action' => 'save',
            ])
            ->assertRedirect();

        $transaction = \App\Models\InventoryTransaction::query()
            ->where('module', 'grey')
            ->where('screen_slug', 'purchase')
            ->latest('id')
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('erp.grey.screen', ['screen' => 'purchase', 'edit' => $transaction->id]))
            ->assertOk()
            ->assertSee($transaction->trans_no, false);

        $this->actingAs($admin)
            ->patchJson(route('erp.grey.screen.update', ['screen' => 'purchase', 'transaction' => $transaction]), [
                'trans_date' => $transaction->trans_date->toDateString(),
                'account_id' => $supplier->id,
                'meta' => array_merge($transaction->meta ?? [], ['than_qty' => 11]),
                'lines' => [
                    [
                        'description' => 'Grey purchase',
                        'qty' => 13,
                        'rate' => 100,
                        'amount' => 1300,
                        'meta' => ['grey_quality_id' => $quality->id],
                    ],
                ],
                'submit_action' => 'save',
            ], ['Accept' => 'application/json'])
            ->assertOk();
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

    public function test_account_statement_report_shows_running_balance(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();
        $today = now()->format('d-m-Y');

        \App\Models\AccountOpening::query()->create([
            'voucher_date' => now()->toDateString(),
            'financial_year_id' => \App\Models\FinancialYear::query()->firstOrFail()->id,
            'account_id' => $account->id,
            'narration' => 'Test opening',
            'debit' => 1000,
            'credit' => 0,
        ]);

        $voucher = \App\Models\Voucher::query()->create([
            'module' => 'accounts',
            'voucher_type' => 'CP',
            'voucher_number' => 'CP-RPT-001',
            'voucher_date' => now()->toDateString(),
            'status' => 'posted',
            'total_debit' => 200,
            'total_credit' => 200,
            'total_amount' => 200,
            'created_by' => $admin->id,
        ]);

        \App\Models\VoucherLine::query()->create([
            'voucher_id' => $voucher->id,
            'account_id' => $account->id,
            'description' => 'Payment',
            'debit' => 0,
            'credit' => 200,
        ]);

        $bankVoucher = \App\Models\Voucher::query()->create([
            'module' => 'accounts',
            'voucher_type' => 'BPV',
            'voucher_number' => 'BPV-RPT-001',
            'voucher_date' => now()->toDateString(),
            'status' => 'posted',
            'total_debit' => 500,
            'total_credit' => 500,
            'total_amount' => 500,
            'created_by' => $admin->id,
        ]);

        \App\Models\VoucherLine::query()->create([
            'voucher_id' => $bankVoucher->id,
            'account_id' => $account->id,
            'description' => 'Bank payment',
            'debit' => 500,
            'credit' => 0,
            'meta' => ['instrument_no' => 'SLIP-7788', 'instrument_date' => '06-07-2026'],
        ]);

        $this->actingAs($admin)
            ->get(route('erp.reports.view', [
                'screen' => 'accounts',
                'report' => 'account-statement',
                'account_id' => $account->id,
                'from_date' => $today,
                'to_date' => $today,
            ]))
            ->assertOk()
            ->assertSee('Account Statement', false)
            ->assertSee('OPENING BALANCE', false)
            ->assertSee('CP-RPT-001', false)
            ->assertSee('SLIP-7788', false)
            ->assertSee('06-07-2026', false);

        $this->actingAs($admin)
            ->get(route('erp.reports.view', [
                'screen' => 'accounts',
                'report' => 'trial-balance',
                'from_date' => $today,
                'to_date' => $today,
            ]))
            ->assertOk()
            ->assertSee('Trial Balance', false);
    }

    public function test_bank_receipt_voucher_rejects_instrument_number_used_on_another_voucher(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $fy = \App\Models\FinancialYear::query()->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();

        $this->actingAs($admin)->post(route('erp.accounts.vouchers.store', ['voucherType' => 'brv']), [
            'voucher_date' => now()->toDateString(),
            'financial_year_id' => $fy->id,
            'remarks' => 'First BRV',
            'lines' => [
                [
                    'account_id' => $account->id,
                    'description' => 'Line 1',
                    'debit' => 2200,
                    'credit' => 0,
                    'meta' => ['instrument_no' => 'BRV-9001'],
                ],
            ],
        ])->assertRedirect();

        $response = $this->from(route('erp.accounts.vouchers.bpv'))
            ->actingAs($admin)
            ->post(route('erp.accounts.vouchers.store', ['voucherType' => 'bpv']), [
                'voucher_date' => now()->addDays(3)->toDateString(),
                'financial_year_id' => $fy->id,
                'remarks' => 'BPV reusing BRV instrument',
                'lines' => [
                    [
                        'account_id' => $account->id,
                        'description' => 'Line 1',
                        'debit' => 0,
                        'credit' => 3300,
                        'meta' => ['instrument_no' => 'BRV-9001'],
                    ],
                ],
            ]);

        $response->assertRedirect(route('erp.accounts.vouchers.bpv'));
        $response->assertSessionHasErrors('lines');
    }

    public function test_instrument_number_uniqueness_is_case_insensitive_across_vouchers(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $fy = \App\Models\FinancialYear::query()->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();

        $this->actingAs($admin)->post(route('erp.accounts.vouchers.store', ['voucherType' => 'bpv']), [
            'voucher_date' => now()->toDateString(),
            'financial_year_id' => $fy->id,
            'remarks' => 'Lowercase instrument',
            'lines' => [
                [
                    'account_id' => $account->id,
                    'description' => 'Line 1',
                    'debit' => 0,
                    'credit' => 1100,
                    'meta' => ['instrument_no' => 'chk-4001'],
                ],
            ],
        ])->assertRedirect();

        $response = $this->from(route('erp.accounts.vouchers.brv'))
            ->actingAs($admin)
            ->post(route('erp.accounts.vouchers.store', ['voucherType' => 'brv']), [
                'voucher_date' => now()->addWeek()->toDateString(),
                'financial_year_id' => $fy->id,
                'remarks' => 'Uppercase duplicate instrument',
                'lines' => [
                    [
                        'account_id' => $account->id,
                        'description' => 'Line 1',
                        'debit' => 1400,
                        'credit' => 0,
                        'meta' => ['instrument_no' => 'CHK-4001'],
                    ],
                ],
            ]);

        $response->assertRedirect(route('erp.accounts.vouchers.brv'));
        $response->assertSessionHasErrors('lines');
    }

    public function test_account_statement_csv_export_includes_instrument_columns(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $account = \App\Models\Account::query()->postable()->firstOrFail();
        $today = now()->format('d-m-Y');

        $bankVoucher = \App\Models\Voucher::query()->create([
            'module' => 'accounts',
            'voucher_type' => 'BPV',
            'voucher_number' => 'BPV-CSV-001',
            'voucher_date' => now()->toDateString(),
            'status' => 'posted',
            'total_debit' => 750,
            'total_credit' => 750,
            'total_amount' => 750,
            'created_by' => $admin->id,
        ]);

        \App\Models\VoucherLine::query()->create([
            'voucher_id' => $bankVoucher->id,
            'account_id' => $account->id,
            'description' => 'CSV bank payment',
            'debit' => 750,
            'credit' => 0,
            'meta' => ['instrument_no' => 'CSV-SLIP-99', 'instrument_date' => '01-08-2026'],
        ]);

        $response = $this->actingAs($admin)->get(route('erp.reports.export', [
            'screen' => 'accounts',
            'report' => 'account-statement',
            'account_id' => $account->id,
            'from_date' => $today,
            'to_date' => $today,
        ]));

        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Instrument #', $csv);
        $this->assertStringContainsString('Inst. Date', $csv);
        $this->assertStringContainsString('CSV-SLIP-99', $csv);
        $this->assertStringContainsString('01-08-2026', $csv);
        $this->assertTrue(strpos($csv, 'Instrument #') < strpos($csv, 'Inst. Date'));
    }

    public function test_yarn_purchase_contract_wise_merges_item_id_from_contract_when_missing(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $contract = \App\Models\YarnContract::query()->where('direction', 'purchase')->firstOrFail();
        $item = $contract->item ?? \App\Models\Item::query()->where('module', 'yarn')->firstOrFail();
        $yarnStock = \App\Models\Account::query()->where('code', '01002000100001')->firstOrFail();
        $supplier = \App\Models\Account::query()->where('code', '02001000100001')->firstOrFail();

        $response = $this->actingAs($admin)->post(
            route('erp.yarn.screen.store', ['screen' => 'purchase-contract-wise']),
            $this->yarnContractWisePayload($contract, 'purchase-contract-wise', ['quantity' => 4, 'rate' => 100]),
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors(['item_id']);

        $transaction = \App\Models\InventoryTransaction::query()
            ->where('screen_slug', 'purchase-contract-wise')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('posted', $transaction->status);
        $this->assertSame($item->id, (int) ($transaction->meta['item_id'] ?? 0));
        $this->assertDatabaseHas('inventory_transaction_lines', [
            'inventory_transaction_id' => $transaction->id,
            'item_id' => $item->id,
            'qty' => 4,
        ]);

        $this->assertNotEmpty($transaction->meta['voucher_id'] ?? null);
        $purchaseVoucher = \App\Models\Voucher::query()->findOrFail($transaction->meta['voucher_id']);
        $expectedAmount = (float) $transaction->total_amount;
        $this->assertSame('posted', $purchaseVoucher->status);
        $this->assertDatabaseHas('voucher_lines', [
            'voucher_id' => $purchaseVoucher->id,
            'account_id' => $yarnStock->id,
            'debit' => $expectedAmount,
        ]);
        $this->assertDatabaseHas('voucher_lines', [
            'voucher_id' => $purchaseVoucher->id,
            'account_id' => $supplier->id,
            'credit' => $expectedAmount,
        ]);
    }

    public function test_yarn_sale_contract_wise_merges_item_id_from_contract_when_missing(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $contract = \App\Models\YarnContract::query()->where('direction', 'sale')->firstOrFail();
        $item = $contract->item ?? \App\Models\Item::query()->where('module', 'yarn')->firstOrFail();
        $customer = \App\Models\Account::query()->where('code', '01001000100001')->firstOrFail();
        $yarnSales = \App\Models\Account::query()->where('code', '01003000100001')->firstOrFail();

        $response = $this->actingAs($admin)->post(
            route('erp.yarn.screen.store', ['screen' => 'sale-contract-wise']),
            $this->yarnContractWisePayload($contract, 'sale-contract-wise', ['quantity' => 5, 'rate' => 150]),
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors(['item_id']);

        $transaction = \App\Models\InventoryTransaction::query()
            ->where('screen_slug', 'sale-contract-wise')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('posted', $transaction->status);
        $this->assertSame($item->id, (int) ($transaction->meta['item_id'] ?? 0));
        $this->assertDatabaseHas('inventory_transaction_lines', [
            'inventory_transaction_id' => $transaction->id,
            'item_id' => $item->id,
            'qty' => 5,
        ]);

        $this->assertNotEmpty($transaction->meta['voucher_id'] ?? null);
        $saleVoucher = \App\Models\Voucher::query()->findOrFail($transaction->meta['voucher_id']);
        $expectedAmount = (float) $transaction->total_amount;
        $this->assertSame('posted', $saleVoucher->status);
        $this->assertDatabaseHas('voucher_lines', [
            'voucher_id' => $saleVoucher->id,
            'account_id' => $customer->id,
            'debit' => $expectedAmount,
        ]);
        $this->assertDatabaseHas('voucher_lines', [
            'voucher_id' => $saleVoucher->id,
            'account_id' => $yarnSales->id,
            'credit' => $expectedAmount,
        ]);
    }

    public function test_yarn_contract_wise_update_merges_item_id_from_contract_when_missing(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $contract = \App\Models\YarnContract::query()->where('direction', 'purchase')->firstOrFail();
        $item = $contract->item ?? \App\Models\Item::query()->where('module', 'yarn')->firstOrFail();

        $this->actingAs($admin)->post(
            route('erp.yarn.screen.store', ['screen' => 'purchase-contract-wise']),
            $this->yarnContractWisePayload($contract, 'purchase-contract-wise', ['quantity' => 2, 'rate' => 80]),
        )->assertRedirect();

        $transaction = \App\Models\InventoryTransaction::query()
            ->where('screen_slug', 'purchase-contract-wise')
            ->latest('id')
            ->firstOrFail();

        $response = $this->actingAs($admin)->patch(
            route('erp.yarn.screen.update', ['screen' => 'purchase-contract-wise', 'transaction' => $transaction]),
            $this->yarnContractWisePayload($contract, 'purchase-contract-wise', [
                'quantity' => 6,
                'rate' => 90,
                'remarks' => 'Updated without item_id',
            ]),
        );

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors(['item_id']);

        $transaction->refresh();
        $this->assertSame('Updated without item_id', $transaction->remarks);
        $this->assertDatabaseHas('inventory_transaction_lines', [
            'inventory_transaction_id' => $transaction->id,
            'item_id' => $item->id,
            'qty' => 6,
        ]);
    }

    public function test_yarn_contract_wise_rejects_save_when_contract_missing_and_item_id_empty(): void
    {
        $admin = \App\Models\User::query()->where('email', 'admin@erp.local')->firstOrFail();
        $contract = \App\Models\YarnContract::query()->where('direction', 'sale')->firstOrFail();

        $response = $this->from(route('erp.yarn.screen', ['screen' => 'sale-contract-wise']))
            ->actingAs($admin)
            ->post(route('erp.yarn.screen.store', ['screen' => 'sale-contract-wise']), [
                'trans_date' => now()->toDateString(),
                'account_id' => $contract->account_id,
                'from_godown_id' => $contract->godown_id,
                'item_id' => '',
                'packing_size' => 40,
                'quantity' => 2,
                'no_of_cones' => 0,
                'rate' => 150,
                'submit_action' => 'post',
                'meta' => ['voucher_type' => 'YSV'],
            ]);

        $response->assertRedirect(route('erp.yarn.screen', ['screen' => 'sale-contract-wise']));
        $response->assertSessionHasErrors(['yarn_contract_id', 'item_id']);
    }
}

