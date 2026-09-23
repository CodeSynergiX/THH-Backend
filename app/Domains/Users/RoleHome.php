<?php

namespace App\Domains\Users;

use App\Models\User;

class RoleHome
{
    public static function url(?User $user): string
    {
        if (! $user) {
            return '/';
        }

        if ($user->hasRole(['super_admin', 'admin', 'staff', 'collector'])) {
            return '/admin/dashboard';
        }

        if ($user->hasRole(['mentor', 'volunteer'])) {
            return '/admin/cases';
        }

        if ($user->hasRole('citizen')) {
            return '/account';
        }

        return '/';
    }
}
