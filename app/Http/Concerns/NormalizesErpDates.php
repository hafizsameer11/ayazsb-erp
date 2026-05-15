<?php

namespace App\Http\Concerns;

use App\Support\ErpDate;
use Illuminate\Http\Request;

trait NormalizesErpDates
{
    /**
     * @param  list<string>  $fields
     */
    protected function normalizeErpDates(Request $request, array $fields): void
    {
        $merged = [];

        foreach ($fields as $field) {
            if (! $request->has($field)) {
                continue;
            }

            $storage = ErpDate::toStorage($request->input($field));
            if ($storage !== null) {
                $merged[$field] = $storage;
            }
        }

        if ($merged !== []) {
            $request->merge($merged);
        }
    }
}
