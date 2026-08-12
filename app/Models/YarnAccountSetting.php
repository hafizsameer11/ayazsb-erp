<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YarnAccountSetting extends Model
{
    protected $fillable = [
        'yarn_stock_account_id',
        'yarn_sales_account_id',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }

    public function yarnStockAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'yarn_stock_account_id');
    }

    public function yarnSalesAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'yarn_sales_account_id');
    }

    public function accountIdForSource(string $source): ?int
    {
        return match ($source) {
            'yarn_stock' => $this->yarn_stock_account_id,
            'yarn_sales' => $this->yarn_sales_account_id,
            default => null,
        };
    }
}
