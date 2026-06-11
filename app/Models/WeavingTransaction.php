<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeavingTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'screen_slug', 'trans_no', 'trans_date', 'account_id', 'department_id',
        'source_transaction_id', 'weaving_set_id', 'grey_conversion_contract_id',
        'grey_quality_id', 'status', 'remarks', 'meta', 'total_qty', 'total_amount',
        'voucher_id', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'trans_date' => 'date',
            'meta' => 'array',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(WeavingTransactionLine::class)->orderBy('line_no');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(WeavingDepartment::class);
    }

    public function sourceTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_transaction_id');
    }

    public function weavingSet(): BelongsTo
    {
        return $this->belongsTo(WeavingSet::class);
    }

    public function greyConversionContract(): BelongsTo
    {
        return $this->belongsTo(GreyConversionContract::class);
    }

    public function greyQuality(): BelongsTo
    {
        return $this->belongsTo(GreyQuality::class);
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
