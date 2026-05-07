<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = [
        'module',
        'voucher_type',
        'voucher_number',
        'voucher_date',
        'financial_year_id',
        'party_id',
        'godown_id',
        'status',
        'remarks',
        'total_debit',
        'total_credit',
        'total_amount',
        'created_by',
        'posted_by',
        'posted_at',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(VoucherLine::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }
}
