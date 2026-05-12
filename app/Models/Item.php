<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'code',
        'name',
        'module',
        'category',
        'unit',
        'default_rate',
        'is_active',
    ];

    public function voucherLines(): HasMany
    {
        return $this->hasMany(VoucherLine::class);
    }

    public function inventoryLines(): HasMany
    {
        return $this->hasMany(InventoryTransactionLine::class);
    }

    public function yarnContracts(): HasMany
    {
        return $this->hasMany(YarnContract::class);
    }
}
