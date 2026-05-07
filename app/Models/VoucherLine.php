<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherLine extends Model
{
    protected $fillable = [
        'voucher_id',
        'account_id',
        'item_id',
        'description',
        'qty',
        'unit',
        'rate',
        'amount',
        'debit',
        'credit',
        'tag',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
