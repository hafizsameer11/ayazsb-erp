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
        $item = \App\Models\Item::query()->firstOrFail();

        $save = $this->actingAs($admin)->post(route('erp.yarn.screen.store', ['screen' => 'issuance']), [
            'trans_date' => now()->toDateString(),
            'remarks' => 'Yarn issuance test',
            'lines' => [
                ['item_id' => $item->id, 'description' => 'Issue line', 'qty' => 2, 'rate' => 50, 'amount' => 100],
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

