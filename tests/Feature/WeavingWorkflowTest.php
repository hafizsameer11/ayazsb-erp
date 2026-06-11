<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeavingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->createWeavingFixtures();
    }

    private function createWeavingFixtures(): void
    {
        $superAdminRole = \App\Models\Role::query()->where('slug', 'super-admin')->firstOrFail();
        $admin = \App\Models\User::factory()->create([
            'name' => 'Weaving Admin',
            'username' => 'weaving-admin',
            'email' => 'weaving@erp.local',
            'password' => 'admin123',
        ]);
        $admin->roles()->sync([$superAdminRole->id]);

        \App\Models\FinancialYear::query()->firstOrCreate(
            ['year_code' => '2026'],
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_closed' => false]
        );

        $assetHead = \App\Models\Account::query()->create([
            'level' => 'head', 'code' => '01', 'name' => 'Assets', 'is_active' => true,
        ]);
        $control = \App\Models\Account::query()->create([
            'level' => 'control', 'code' => '01001', 'name' => 'Control', 'parent_id' => $assetHead->id, 'is_active' => true,
        ]);
        $ledger = \App\Models\Account::query()->create([
            'level' => 'ledger', 'code' => '010010001', 'name' => 'Ledger', 'parent_id' => $control->id, 'is_active' => true,
        ]);
        \App\Models\Account::query()->create([
            'level' => 'sub_ledger', 'code' => '01001000100001', 'name' => 'Weaving Party', 'parent_id' => $ledger->id, 'is_active' => true,
        ]);
        \App\Models\Account::query()->create([
            'level' => 'sub_ledger', 'code' => '01001000100002', 'name' => 'Store Stock', 'parent_id' => $ledger->id, 'is_active' => true,
        ]);
        \App\Models\Account::query()->create([
            'level' => 'sub_ledger', 'code' => '01001000100003', 'name' => 'Store Expense', 'parent_id' => $ledger->id, 'is_active' => true,
        ]);

        \App\Models\WeavingAccountSetting::current()->update([
            'store_stock_account_id' => \App\Models\Account::query()->where('code', '01001000100002')->value('id'),
            'default_expense_account_id' => \App\Models\Account::query()->where('code', '01001000100003')->value('id'),
        ]);

        \App\Models\Item::query()->create([
            'code' => 'ST001', 'name' => 'Store bolt', 'module' => 'store', 'unit' => 'PCS', 'is_active' => true,
        ]);
        \App\Models\Item::query()->create([
            'code' => 'WY001', 'name' => 'Weaving yarn', 'module' => 'yarn', 'unit' => 'BAGS', 'is_active' => true,
        ]);

        \App\Models\WeavingDepartment::query()->create([
            'code' => 'D01', 'name' => 'Weaving Dept', 'is_active' => true,
        ]);
        \App\Models\WeavingLoom::query()->create([
            'loom_no' => 'L01', 'name' => 'Loom 1', 'is_active' => true,
        ]);

        \App\Models\GreyQuality::query()->create([
            'quality_no' => '100900', 'quality_name' => 'Test Grey', 'is_active' => true,
        ]);
    }

    private function admin(): \App\Models\User
    {
        return \App\Models\User::query()->where('email', 'weaving@erp.local')->firstOrFail();
    }

    public function test_weaving_dashboard_and_screens_render(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('erp.weaving.dashboard'))->assertOk();

        foreach (\App\Support\WeavingModule::screenSlugs() as $slug) {
            if ($slug === 'master-data') {
                $this->actingAs($admin)->get(route('erp.weaving.master-data'))->assertOk();
                continue;
            }
            $this->actingAs($admin)->get(route('erp.weaving.screen', ['screen' => $slug]))->assertOk();
        }
    }

    public function test_store_issue_reduces_store_stock(): void
    {
        $admin = $this->admin();
        $item = \App\Models\Item::query()->where('code', 'ST001')->firstOrFail();
        $dept = \App\Models\WeavingDepartment::query()->firstOrFail();

        app(\App\Services\WeavingStockService::class)->applyStoreReceipt([
            ['item_id' => $item->id, 'qty' => 100],
        ]);

        $response = $this->actingAs($admin)->post(route('erp.weaving.screen.store', ['screen' => 'store-issue']), [
            'trans_date' => now()->toDateString(),
            'department_id' => $dept->id,
            'lines' => [
                ['item_id' => $item->id, 'qty' => 10, 'rate' => 50, 'amount' => 500, 'meta' => ['issue_as' => 'Consumption']],
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals(90.0, app(\App\Services\WeavingStockService::class)->available('store', $item->id));
    }

    public function test_purchase_order_increases_store_stock(): void
    {
        $admin = $this->admin();
        $item = \App\Models\Item::query()->where('code', 'ST001')->firstOrFail();
        $party = \App\Models\Account::query()->postable()->firstOrFail();

        $this->actingAs($admin)->post(route('erp.weaving.screen.store', ['screen' => 'purchase-order']), [
            'trans_date' => now()->toDateString(),
            'account_id' => $party->id,
            'lines' => [
                ['item_id' => $item->id, 'qty' => 25, 'rate' => 100, 'amount' => 2500],
            ],
        ])->assertRedirect();

        $this->assertEquals(25.0, app(\App\Services\WeavingStockService::class)->available('store', $item->id));
    }

    public function test_yarn_receipt_and_issuance_to_production(): void
    {
        $admin = $this->admin();
        $yarn = \App\Models\Item::query()->where('code', 'WY001')->firstOrFail();
        $stock = app(\App\Services\WeavingStockService::class);

        $this->actingAs($admin)->post(route('erp.weaving.screen.store', ['screen' => 'yarn-receipt']), [
            'trans_date' => now()->toDateString(),
            'account_id' => \App\Models\Account::query()->postable()->firstOrFail()->id,
            'lines' => [['item_id' => $yarn->id, 'qty' => 50, 'rate' => 10, 'amount' => 500]],
        ])->assertRedirect();

        $this->assertEquals(50.0, $stock->available('stock', $yarn->id));

        $this->actingAs($admin)->post(route('erp.weaving.screen.store', ['screen' => 'yarn-issuance-stock-to-production']), [
            'trans_date' => now()->toDateString(),
            'lines' => [['item_id' => $yarn->id, 'qty' => 20, 'rate' => 10, 'amount' => 200]],
        ])->assertRedirect();

        $this->assertEquals(30.0, $stock->available('stock', $yarn->id));
        $this->assertEquals(20.0, $stock->available('production', $yarn->id));
    }

    public function test_set_receipt_creates_beams(): void
    {
        $admin = $this->admin();
        $quality = \App\Models\GreyQuality::query()->firstOrFail();

        $this->actingAs($admin)->post(route('erp.weaving.screen.store', ['screen' => 'set-receipt-details']), [
            'entry_date' => now()->toDateString(),
            'receipt_date' => now()->toDateString(),
            'grey_quality_id' => $quality->id,
            'width' => 60,
            'meters' => 1000,
            'beams' => [
                ['beam_no' => 'B001', 'beam_length' => 500],
                ['beam_no' => 'B002', 'beam_length' => 500],
            ],
        ])->assertRedirect();

        $this->assertDatabaseCount('weaving_sets', 1);
        $this->assertDatabaseCount('weaving_beams', 2);
    }

    public function test_master_data_saves_department_and_store_item(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('erp.weaving.master-data.store'), [
            'tab' => 'departments',
            'departments' => [
                ['code' => 'D02', 'name' => 'Sizing', 'is_active' => 1],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('weaving_departments', ['code' => 'D02', 'name' => 'Sizing']);

        $this->actingAs($admin)->post(route('erp.weaving.master-data.store'), [
            'tab' => 'store-items',
            'items' => [
                ['code' => 'ST002', 'name' => 'Needle pack', 'unit' => 'BOX', 'is_active' => 1],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('items', ['code' => 'ST002', 'module' => 'store']);
    }

    public function test_purchase_return_and_fabric_issue_save(): void
    {
        $admin = $this->admin();
        $item = \App\Models\Item::query()->where('code', 'ST001')->firstOrFail();
        $party = \App\Models\Account::query()->postable()->firstOrFail();
        $quality = \App\Models\GreyQuality::query()->firstOrFail();
        $stock = app(\App\Services\WeavingStockService::class);

        $stock->applyStoreReceipt([['item_id' => $item->id, 'qty' => 40]]);

        $this->actingAs($admin)->post(route('erp.weaving.screen.store', ['screen' => 'purchase-return']), [
            'trans_date' => now()->toDateString(),
            'account_id' => $party->id,
            'lines' => [['item_id' => $item->id, 'qty' => 5, 'rate' => 100, 'amount' => 500]],
        ])->assertRedirect();

        $this->assertEquals(35.0, $stock->available('store', $item->id));

        $this->actingAs($admin)->post(route('erp.weaving.screen.store', ['screen' => 'fabric-issue-conversion-kachi']), [
            'trans_date' => now()->toDateString(),
            'account_id' => $party->id,
            'lines' => [['qty' => 100, 'rate' => 10, 'amount' => 1000, 'meta' => ['grey_quality_id' => $quality->id, 'than' => 2]]],
        ])->assertRedirect();

        $this->assertDatabaseHas('weaving_transactions', ['screen_slug' => 'fabric-issue-conversion-kachi']);
    }

    public function test_sized_beams_issuance_marks_beam_issued(): void
    {
        $admin = $this->admin();
        $quality = \App\Models\GreyQuality::query()->firstOrFail();
        $loom = \App\Models\WeavingLoom::query()->firstOrFail();

        $this->actingAs($admin)->post(route('erp.weaving.screen.store', ['screen' => 'set-receipt-details']), [
            'entry_date' => now()->toDateString(),
            'receipt_date' => now()->toDateString(),
            'grey_quality_id' => $quality->id,
            'beams' => [['beam_no' => 'B100', 'beam_length' => 400]],
        ]);

        $beam = \App\Models\WeavingBeam::query()->where('beam_no', 'B100')->firstOrFail();

        $this->actingAs($admin)->post(route('erp.weaving.screen.store', ['screen' => 'sized-beams-issuance']), [
            'trans_date' => now()->toDateString(),
            'lines' => [[
                'qty' => 400,
                'meta' => ['beam_id' => $beam->id, 'loom_id' => $loom->id, 'grey_quality_id' => $quality->id],
            ]],
        ])->assertRedirect();

        $beam->refresh();
        $this->assertSame('issued', $beam->status);
        $this->assertSame($loom->id, $beam->loom_id);
    }

    public function test_production_data_entry_saves_lines(): void
    {
        $admin = $this->admin();
        $quality = \App\Models\GreyQuality::query()->firstOrFail();
        $loom = \App\Models\WeavingLoom::query()->firstOrFail();

        $this->actingAs($admin)->post(route('erp.weaving.screen.store', ['screen' => 'production-data-entry']), [
            'doc_date' => now()->toDateString(),
            'contract_grey_quality_id' => $quality->id,
            'production_grey_quality_id' => $quality->id,
            'lines' => [[
                'sr' => 1,
                'loom_id' => $loom->id,
                'grey_quality_id' => $quality->id,
                'width' => 60,
                'beam_balance' => 400,
                'sides' => ['picking_fresh' => 10, 'receiving_fresh' => 9],
                'beam_status' => 'Running',
            ]],
        ])->assertRedirect();

        $this->assertDatabaseCount('weaving_production_entries', 1);
        $this->assertDatabaseCount('weaving_production_lines', 1);
    }

    public function test_store_issue_voucher_uses_sub_ledger_accounts(): void
    {
        $admin = $this->admin();
        $item = \App\Models\Item::query()->where('code', 'ST001')->firstOrFail();
        $dept = \App\Models\WeavingDepartment::query()->firstOrFail();
        $expense = \App\Models\Account::query()->where('code', '01001000100003')->firstOrFail();
        $stock = app(\App\Services\WeavingStockService::class);
        $stock->applyStoreReceipt([['item_id' => $item->id, 'qty' => 100]]);

        $this->actingAs($admin)->post(route('erp.weaving.screen.store', ['screen' => 'store-issue']), [
            'trans_date' => now()->toDateString(),
            'department_id' => $dept->id,
            'lines' => [[
                'item_id' => $item->id,
                'qty' => 10,
                'rate' => 50,
                'amount' => 500,
                'meta' => ['cc_account_id' => $expense->id, 'issue_as' => 'Consumption'],
            ]],
        ]);

        $transaction = \App\Models\WeavingTransaction::query()->where('screen_slug', 'store-issue')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('erp.weaving.screen.voucher', ['screen' => 'store-issue', 'transaction' => $transaction]))
            ->assertRedirect();

        $transaction->refresh();
        $voucher = $transaction->voucher()->with('lines.account')->first();
        $this->assertNotNull($voucher);
        $this->assertCount(2, $voucher->lines);
        $this->assertEquals(500.0, (float) $voucher->total_debit);
        $this->assertEquals(500.0, (float) $voucher->total_credit);
        $this->assertTrue($voucher->lines->every(fn ($line) => $line->account?->level === 'sub_ledger'));
    }
}
