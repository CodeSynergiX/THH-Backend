<?php

namespace App\Domains\Users\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ScopeHelper
{
    /**
     * Apply scope rules to an Applications query builder based on the authenticated user.
     *
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function applyApplicationScope(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['super_admin', 'admin', 'staff', 'collector'])) {
            return $query;
        }

        if ($user->hasRole(['mentor', 'volunteer'])) {
            return $query->where('current_assignee_id', $user->id);
        }

        if ($user->hasRole('partner')) {
            return $query->whereHas('assignments', function (Builder $aq) use ($user) {
                $aq->where('assignee_id', $user->id);
            });
        }

        // Citizen: own applications only (by user_id OR phone / email match)
        return $query->where(function (Builder $q) use ($user) {
            $q->where('user_id', $user->id);

            if (! empty($user->phone)) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone);
                if (strlen($cleanPhone) >= 10) {
                    $last10 = substr($cleanPhone, -10);
                    $q->orWhereHas('user', fn (Builder $uq) => $uq->where('phone', 'like', "%{$last10}%"));
                }
            }

            if (! empty($user->email)) {
                $q->orWhereHas('user', fn (Builder $uq) => $uq->where('email', $user->email));
            }
        });
    }

    /**
     * Apply scope rules to a Users / Citizens query builder.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public static function applyUserScope(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['super_admin', 'admin', 'staff', 'collector'])) {
            return $query;
        }

        return $query->where('id', $user->id);
    }
}
