<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeavingProductionEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'doc_no', 'doc_date', 'contract_grey_quality_id', 'production_grey_quality_id',
        'meta', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'doc_date' => 'date',
            'meta' => 'array',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(WeavingProductionLine::class)->orderBy('sr');
    }

    public function contractQuality(): BelongsTo
    {
        return $this->belongsTo(GreyQuality::class, 'contract_grey_quality_id');
    }

    public function productionQuality(): BelongsTo
    {
        return $this->belongsTo(GreyQuality::class, 'production_grey_quality_id');
    }
}
