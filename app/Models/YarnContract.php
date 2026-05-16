<?php

namespace App\Models;

use App\Services\YarnContractBalanceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YarnContract extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'contract_no',
        'contract_code',
        'direction',
        'contract_type',
        'contract_date',
        'payment_term',
        'party_id',
        'account_id',
        'broker_account_id',
        'commission_percent',
        'brokery_percent',
        'yarn_type',
        'item_id',
        'godown_id',
        'yarn_tag',
        'condition',
        'unit',
        'quantity',
        'no_of_cones',
        'weight_lbs',
        'total_kgs',
        'packing_size',
        'packing_weight',
        'rate',
        'total_amount',
        'total_commission',
        'total_brokery',
        'total_net_amount',
        'sale_rate',
        'status',
        'remarks',
        'meta',
        'created_by',
    ];

    protected $casts = [
        'contract_date' => 'date',
        'meta' => 'array',
        'commission_percent' => 'decimal:4',
        'brokery_percent' => 'decimal:4',
        'quantity' => 'decimal:4',
        'no_of_cones' => 'decimal:4',
        'weight_lbs' => 'decimal:4',
        'total_kgs' => 'decimal:4',
        'packing_size' => 'decimal:4',
        'packing_weight' => 'decimal:4',
        'rate' => 'decimal:4',
        'total_amount' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'total_brokery' => 'decimal:2',
        'total_net_amount' => 'decimal:2',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'broker_account_id');
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
