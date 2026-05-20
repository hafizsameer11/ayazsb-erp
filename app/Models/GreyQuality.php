<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GreyQuality extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quality_no',
        'tag',
        'season',
        'is_active',
        'reed',
        'pick',
        'width',
        'total_ends',
        'yarn_blend_id',
        'blend_label',
        'color',
        'quality_name',
        'quality_name_manual',
        'remarks',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'reed' => 'decimal:4',
            'pick' => 'decimal:4',
            'width' => 'decimal:4',
            'total_ends' => 'decimal:4',
        ];
    }

    public function blend(): BelongsTo
    {
        return $this->belongsTo(YarnBlend::class, 'yarn_blend_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(GreyQualityDetail::class)->orderBy('sort_order');
    }
}
