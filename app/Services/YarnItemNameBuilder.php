<?php

namespace App\Services;

use App\Models\Item;
use App\Models\YarnBlend;
use App\Models\YarnBrand;
use App\Models\YarnCount;
use App\Models\YarnRatio;
use App\Models\YarnThread;

class YarnItemNameBuilder
{
    public function build(
        ?int $yarnCountId,
        ?int $yarnThreadId,
        ?int $yarnBlendId,
        ?int $yarnBrandId,
        ?int $yarnRatioId,
        ?string $itemType,
        ?int $packSizeCones,
        ?float $packingWeight,
        ?string $unit = 'LBS',
    ): string {
        $blend = $yarnBlendId ? YarnBlend::query()->find($yarnBlendId)?->blend : null;
        $count = $yarnCountId ? YarnCount::query()->find($yarnCountId)?->count : null;
        $thread = $yarnThreadId ? YarnThread::query()->find($yarnThreadId)?->thread : null;
        $brand = $yarnBrandId ? YarnBrand::query()->find($yarnBrandId)?->brand : null;
        $ratio = $yarnRatioId ? YarnRatio::query()->find($yarnRatioId)?->ratio : null;

        $parts = array_filter([
            $blend,
            $count && $thread ? "{$count} / {$thread}" : ($count ?: $thread),
            $brand,
            $ratio,
        ]);

        $base = trim(implode(' ', $parts));

        if ($itemType) {
            $base = trim($base . ' ' . $itemType);
        }

        $packBits = [];
        if ($packSizeCones) {
            $packBits[] = "{$packSizeCones}C";
        }
        if ($packingWeight) {
            $weightUnit = $unit ?: 'LBS';
            $packBits[] = rtrim(rtrim(number_format((float) $packingWeight, 2, '.', ''), '0'), '.') . $weightUnit;
        }

        if ($packBits !== []) {
            $base = trim($base . ' (' . implode(' ', $packBits) . ')');
        }

        return $base !== '' ? $base : 'Yarn item';
    }

    public function buildFromItem(Item $item): string
    {
        return $this->build(
            $item->yarn_count_id,
            $item->yarn_thread_id,
            $item->yarn_blend_id,
            $item->yarn_brand_id,
            $item->yarn_ratio_id,
            $item->item_type,
            $item->pack_size_cones,
            $item->packing_weight !== null ? (float) $item->packing_weight : null,
            $item->unit,
        );
    }
}
