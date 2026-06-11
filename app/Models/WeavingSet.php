<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeavingSet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'set_no', 'company_set_no', 'entry_date', 'receipt_date',
        'sizing_party_account_id', 'grey_conversion_contract_id', 'grey_quality_id',
        'shrink_percent', 'width',         'ends_tareen', 'meters', 'meta', 'status', 'created_by', 'voucher_id',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'receipt_date' => 'date',
            'meta' => 'array',
        ];
    }

    public function beams(): HasMany
    {
        return $this->hasMany(WeavingBeam::class);
    }

    public function sizingParty(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sizing_party_account_id');
    }

    public function quality(): BelongsTo
    {
        return $this->belongsTo(GreyQuality::class, 'grey_quality_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(GreyConversionContract::class, 'grey_conversion_contract_id');
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
