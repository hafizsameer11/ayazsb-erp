<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryTransaction extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'module',
        'screen_slug',
        'trans_no',
        'trans_date',
        'party_id',
        'account_id',
        'from_account_id',
        'to_account_id',
        'yarn_contract_id',
        'from_yarn_contract_id',
        'to_yarn_contract_id',
        'source_transaction_id',
        'from_godown_id',
        'to_godown_id',
        'status',
        'remarks',
        'meta',
        'total_qty',
        'total_amount',
        'created_by',
    ];

    protected $casts = [
        'trans_date' => 'date',
        'meta' => 'array',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(InventoryTransactionLine::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function yarnContract(): BelongsTo
    {
        return $this->belongsTo(YarnContract::class);
    }

    public function fromYarnContract(): BelongsTo
    {
        return $this->belongsTo(YarnContract::class, 'from_yarn_contract_id');
    }

    public function toYarnContract(): BelongsTo
    {
        return $this->belongsTo(YarnContract::class, 'to_yarn_contract_id');
    }

    public function sourceTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_transaction_id');
    }

    public function fromGodown(): BelongsTo
    {
        return $this->belongsTo(Godown::class, 'from_godown_id');
    }

    public function toGodown(): BelongsTo
    {
        return $this->belongsTo(Godown::class, 'to_godown_id');
    }
}
