<?php

namespace App\Rules;

use App\Support\ErpDate as ErpDateSupport;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ErpDate implements ValidationRule
{
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    if ($value === null || $value === '') {
      return;
    }

    if (ErpDateSupport::parse($value) === null) {
      $fail('The :attribute must be a valid date (DD-MM-YYYY).');
    }
  }
}
