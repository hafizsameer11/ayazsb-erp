<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Party extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function yarnContracts(): HasMany
    {
        return $this->hasMany(YarnContract::class);
    }
}
