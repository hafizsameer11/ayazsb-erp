<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = [
        'code',
        'name',
        'module',
        'category',
        'unit',
        'default_rate',
        'is_active',
        'yarn_count_id',
        'yarn_thread_id',
        'yarn_blend_id',
        'yarn_brand_id',
        'yarn_ratio_id',
        'item_type',
        'pack_size_cones',
        'packing_weight',
        'yarn_code',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'packing_weight' => 'decimal:4',
        ];
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

    public function yarnBrand(): BelongsTo
    {
        return $this->belongsTo(YarnBrand::class);
    }

    public function yarnRatio(): BelongsTo
    {
        return $this->belongsTo(YarnRatio::class);
    }

    public function voucherLines(): HasMany
    {
        return $this->hasMany(VoucherLine::class);
    }

    public function inventoryLines(): HasMany
    {
        return $this->hasMany(InventoryTransactionLine::class);
    }

    public function yarnContracts(): HasMany
    {
        return $this->hasMany(YarnContract::class);
    }
}
