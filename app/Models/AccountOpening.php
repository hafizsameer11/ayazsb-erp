<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountOpening extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'voucher_date',
        'financial_year_id',
        'account_id',
        'narration',
        'debit',
        'credit',
        'created_by',
    ];

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
