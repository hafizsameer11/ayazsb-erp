<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GreyConversionContract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contract_no',
        'contract_code',
        'contract_type',
        'contract_date',
        'status',
        'account_id',
        'grey_quality_id',
        'nature',
        'loom_type',
        'loom_width',
        'loom_panna',
        'manual_quality_name',
        'qty_mtr',
        'required_bags',
        'conv_per_pick',
        'per_mtr_rate',
        'fabric_rate',
        'looms_plan',
        'completion_date',
        'invoice_quality_id',
        'broker_account_id',
        'brokery_type',
        'brokery_rate',
        'checker_account_id',
        'checker_rate',
        'munshiana',
        'commission_percent',
        'freight_term',
        'total_amount',
        'total_brokery',
        'total_checkery',
        'total_munshiana',
        'total_net_amount',
        'remarks',
        'warp_details',
        'weft_details',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'contract_date' => 'date',
            'completion_date' => 'date',
            'warp_details' => 'array',
            'weft_details' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function quality(): BelongsTo
    {
        return $this->belongsTo(GreyQuality::class, 'grey_quality_id');
    }

    public function invoiceQuality(): BelongsTo
    {
        return $this->belongsTo(GreyQuality::class, 'invoice_quality_id');
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'broker_account_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'checker_account_id');
    }
}
