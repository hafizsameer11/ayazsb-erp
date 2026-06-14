<?php

namespace App\Services;

use App\Models\Item;

class WeavingItemNumberService
{
    public function nextCode(): string
    {
        $max = Item::query()
            ->where('module', 'store')
            ->where('code', 'like', 'WIT%')
            ->pluck('code')
            ->map(fn (string $code) => (int) preg_replace('/\D/', '', $code))
            ->max();

        $next = ((int) $max) + 1;

        do {
            $candidate = 'WIT' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            $exists = Item::query()->where('code', $candidate)->exists();
            $next++;
        } while ($exists);

        return $candidate;
    }
}
