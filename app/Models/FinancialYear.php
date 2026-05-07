<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialYear extends Model
{
    protected $fillable = [
        'year_code',
        'start_date',
        'end_date',
        'is_closed',
        'description',
    ];

    public function openings(): HasMany
    {
        return $this->hasMany(AccountOpening::class);
    }
}
