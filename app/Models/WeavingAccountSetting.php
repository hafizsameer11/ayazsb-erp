<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeavingAccountSetting extends Model
{
    protected $fillable = [
        'store_stock_account_id',
        'yarn_stock_account_id',
        'grey_stock_account_id',
        'default_expense_account_id',
        'sizing_expense_account_id',
        'fabric_sales_account_id',
        'fabric_cogs_account_id',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }

    public function storeStockAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'store_stock_account_id');
    }

    public function yarnStockAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'yarn_stock_account_id');
    }

    public function greyStockAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'grey_stock_account_id');
    }

    public function defaultExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'default_expense_account_id');
    }

    public function sizingExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sizing_expense_account_id');
    }

    public function fabricSalesAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'fabric_sales_account_id');
    }

    public function fabricCogsAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'fabric_cogs_account_id');
    }

    public function accountIdForSource(string $source): ?int
    {
        return match ($source) {
            'store_stock' => $this->store_stock_account_id,
            'yarn_stock' => $this->yarn_stock_account_id,
            'grey_stock' => $this->grey_stock_account_id,
            'expense' => $this->default_expense_account_id,
            'sizing_expense' => $this->sizing_expense_account_id,
            'fabric_sales' => $this->fabric_sales_account_id,
            'fabric_cogs' => $this->fabric_cogs_account_id,
            default => null,
        };
    }
}
