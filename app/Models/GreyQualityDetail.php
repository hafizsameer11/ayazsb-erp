<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GreyQualityDetail extends Model
{
    protected $fillable = [
        'grey_quality_id',
        'nature',
        'yarn_count_id',
        'yarn_thread_id',
        'yarn_blend_id',
        'line_name',
        'ends',
        'picks',
        'calc_count',
        'weight',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'ends' => 'decimal:4',
            'picks' => 'decimal:4',
            'calc_count' => 'decimal:4',
            'weight' => 'decimal:6',
        ];
    }

    public function quality(): BelongsTo
    {
        return $this->belongsTo(GreyQuality::class, 'grey_quality_id');
    }

    public function yarnCount(): BelongsTo
    {
        return $this->belongsTo(YarnCount::class);
    }

    public function yarnThread(): BelongsTo
    {
        return $this->belongsTo(YarnThread::class);
    }

    public function yarnBlend(): BelongsTo
    {
        return $this->belongsTo(YarnBlend::class);
    }
}
