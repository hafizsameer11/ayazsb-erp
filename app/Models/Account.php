<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Account extends Model
{
    protected $fillable = [
        'level',
        'code',
        'name',
        'parent_id',
        'is_active',
    ];

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['name'] = $value === null ? null : Str::upper(trim($value));
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Leaf accounts used on vouchers, openings, and other GL postings.
     */
    public function scopePostable(Builder $query): Builder
    {
        return $query->where('level', 'sub_ledger')->where('is_active', true);
    }
}
