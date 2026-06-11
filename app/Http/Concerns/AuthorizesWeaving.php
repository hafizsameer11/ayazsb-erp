<?php

namespace App\Http\Concerns;

use Illuminate\Support\Facades\Auth;

trait AuthorizesWeaving
{
    protected function weavingAllowed(string $screen, string $action): bool
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\User) {
            return false;
        }

        return $user->hasPermission("weaving.{$screen}.{$action}");
    }
}
