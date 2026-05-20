<?php

namespace App\Services;

use App\Models\GreyQuality;
use App\Models\YarnBlend;
use App\Models\YarnCount;

class GreyQualityNameBuilder
{
    public function build(
        ?float $reed,
        ?float $pick,
        ?float $width,
        ?float $totalEnds,
        ?int $yarnBlendId,
        ?string $blendLabel,
        ?string $color,
    ): string {
        $parts = [];
        if ($reed && $pick) {
            $parts[] = rtrim(rtrim(number_format($reed, 2, '.', ''), '0'), '.') . ' X ' . rtrim(rtrim(number_format($pick, 2, '.', ''), '0'), '.');
        }

        $blend = $blendLabel ?: ($yarnBlendId ? YarnBlend::query()->find($yarnBlendId)?->blend : null);
        if ($blend) {
            $parts[] = $blend;
        }

        if ($width) {
            $parts[] = rtrim(rtrim(number_format($width, 2, '.', ''), '0'), '.') . '"';
        }

        if ($totalEnds) {
            $parts[] = rtrim(rtrim(number_format($totalEnds, 0, '.', ''), '0'), '.') . ' ENDS';
        }

        if ($color) {
            $parts[] = '(' . $color . ')';
        }

        $name = trim(implode(' ', array_filter($parts)));

        return $name !== '' ? $name : 'Grey quality';
    }

    public function buildFromQuality(GreyQuality $quality): string
    {
        return $this->build(
            $quality->reed !== null ? (float) $quality->reed : null,
            $quality->pick !== null ? (float) $quality->pick : null,
            $quality->width !== null ? (float) $quality->width : null,
            $quality->total_ends !== null ? (float) $quality->total_ends : null,
            $quality->yarn_blend_id,
            $quality->blend_label,
            $quality->color,
        );
    }
}
