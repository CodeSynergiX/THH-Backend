<?php

namespace App\Domains\Users\Scopes;

use App\Domains\Cases\Models\Application;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ScopeHelper
{
    /**
     * Apply scope rules to an Applications query builder based on the authenticated user.
     *
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    public static function applyApplicationScope(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['super_admin', 'admin'])) {
            return $query;
        }

        if ($user->hasRole('staff')) {
            return $query->where(function (Builder $q) use ($user) {
                if ($user->village_id) {
                    $q->where('village_id', $user->village_id);
                } elseif ($user->district_id) {
                    $q->whereHas('village.taluka', function (Builder $tq) use ($user) {
                        $tq->where('district_id', $user->district_id);
                    });
                }
            });
        }

        if ($user->hasRole(['mentor', 'volunteer'])) {
            return $query->where('current_assignee_id', $user->id);
        }

        if ($user->hasRole('partner')) {
            return $query->whereHas('assignments', function (Builder $aq) use ($user) {
                $aq->where('assignee_id', $user->id);
            });
        }

        // Citizen: own applications only
        return $query->where('user_id', $user->id);
    }

    /**
     * Apply scope rules to a Users / Citizens query builder.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public static function applyUserScope(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['super_admin', 'admin'])) {
            return $query;
        }

        if ($user->hasRole('staff')) {
            if ($user->village_id) {
                return $query->where('village_id', $user->village_id);
            }
            if ($user->district_id) {
                return $query->where('district_id', $user->district_id);
            }
        }

        return $query->where('id', $user->id);
    }
}
