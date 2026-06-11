<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeavingStockBalance extends Model
{
    protected $fillable = ['stock_pool', 'item_id', 'qty'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
