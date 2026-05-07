<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'module',
        'screen_slug',
        'trans_no',
        'trans_date',
        'party_id',
        'from_godown_id',
        'to_godown_id',
        'status',
        'remarks',
        'total_qty',
        'total_amount',
        'created_by',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(InventoryTransactionLine::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }
}
