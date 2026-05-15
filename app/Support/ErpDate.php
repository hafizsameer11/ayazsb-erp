<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeInterface;

class ErpDate
{
  public const DISPLAY_FORMAT = 'd-m-Y';

  public const STORAGE_FORMAT = 'Y-m-d';

  public static function display(DateTimeInterface|string|null $value): string
  {
    if ($value === null || $value === '') {
      return '';
    }

    try {
      return self::parse($value)?->format(self::DISPLAY_FORMAT) ?? '';
    } catch (\Throwable) {
      return '';
    }
  }

  public static function parse(DateTimeInterface|string|null $value): ?Carbon
  {
    if ($value === null || $value === '') {
      return null;
    }

    if ($value instanceof DateTimeInterface) {
      return Carbon::instance($value)->startOfDay();
    }

    $value = trim((string) $value);

    foreach ([self::DISPLAY_FORMAT, self::STORAGE_FORMAT, 'd/m/Y', 'm/d/Y'] as $format) {
      try {
        return Carbon::createFromFormat($format, $value)->startOfDay();
      } catch (InvalidFormatException) {
        continue;
      }
    }

    try {
      return Carbon::parse($value)->startOfDay();
    } catch (\Throwable) {
      return null;
    }
  }

  public static function toStorage(DateTimeInterface|string|null $value): ?string
  {
    return self::parse($value)?->format(self::STORAGE_FORMAT);
  }

  public static function todayDisplay(): string
  {
    return now()->format(self::DISPLAY_FORMAT);
  }
}
