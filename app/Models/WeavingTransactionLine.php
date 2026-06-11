<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeavingTransactionLine extends Model
{
    protected $fillable = [
        'weaving_transaction_id', 'item_id', 'line_no', 'description',
        'qty', 'rate', 'amount', 'meta',
    ];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(WeavingTransaction::class, 'weaving_transaction_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
