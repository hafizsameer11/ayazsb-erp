<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeavingPieceLength extends Model
{
    protected $fillable = ['weaving_production_line_id', 'length', 'meta'];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function productionLine(): BelongsTo
    {
        return $this->belongsTo(WeavingProductionLine::class, 'weaving_production_line_id');
    }
}
