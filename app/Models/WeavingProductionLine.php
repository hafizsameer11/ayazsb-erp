<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeavingProductionLine extends Model
{
    protected $fillable = [
        'weaving_production_entry_id', 'sr', 'loom_id', 'beam_id', 'weaving_set_id',
        'grey_conversion_contract_id', 'grey_quality_id', 'width', 'beam_balance',
        'sides', 'beam_status', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'sides' => 'array',
            'meta' => 'array',
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(WeavingProductionEntry::class, 'weaving_production_entry_id');
    }

    public function pieceLengths(): HasMany
    {
        return $this->hasMany(WeavingPieceLength::class);
    }

    public function loom(): BelongsTo
    {
        return $this->belongsTo(WeavingLoom::class);
    }

    public function beam(): BelongsTo
    {
        return $this->belongsTo(WeavingBeam::class);
    }
}
