<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Godown extends Model
{
    protected $fillable = [
        'code',
        'name',
        'module',
        'location',
        'is_active',
    ];

    public function fromTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'from_godown_id');
    }

    public function toTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'to_godown_id');
    }

    public function yarnContracts(): HasMany
    {
        return $this->hasMany(YarnContract::class);
    }
}
