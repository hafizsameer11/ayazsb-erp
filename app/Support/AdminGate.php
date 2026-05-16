<?php

namespace App\Support;

use App\Models\User;

class AdminGate
{
    public static function canDeleteRecords(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->hasRole('Super Admin') || $user->hasPermission('admin.records.delete');
    }
}
