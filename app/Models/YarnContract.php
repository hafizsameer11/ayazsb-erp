<?php

namespace App\Models;

use App\Services\YarnContractBalanceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YarnContract extends Model
{
    protected $fillable = [
        'contract_no',
        'direction',
        'contract_type',
        'contract_date',
        'party_id',
        'item_id',
        'godown_id',
        'yarn_tag',
        'condition',
        'unit',
        'quantity',
        'weight_lbs',
        'packing_size',
        'packing_weight',
        'rate',
        'sale_rate',
        'status',
        'remarks',
        'meta',
        'created_by',
    ];

    protected $casts = [
        'contract_date' => 'date',
        'meta' => 'array',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function godown(): BelongsTo
    {
        return $this->belongsTo(Godown::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function fromTransfers(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'from_yarn_contract_id');
    }

    public function toTransfers(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'to_yarn_contract_id');
    }

    /**
     * Contract stock/issue summary used by Yarn screens and validation.
     *
     * @return array<string, float>
     */
    public function balance(): array
    {
        return app(YarnContractBalanceService::class)->snapshot($this);
    }
}
