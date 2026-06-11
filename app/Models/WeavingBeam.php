<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeavingBeam extends Model
{
    protected $fillable = [
        'weaving_set_id', 'beam_no', 'beam_length', 'status', 'loom_id', 'meta',
    ];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function set(): BelongsTo
    {
        return $this->belongsTo(WeavingSet::class, 'weaving_set_id');
    }

    public function loom(): BelongsTo
    {
        return $this->belongsTo(WeavingLoom::class, 'loom_id');
    }
}
