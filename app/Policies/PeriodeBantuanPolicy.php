<?php

namespace App\Policies;

use App\Models\PeriodeBantuan;
use App\Models\User;

class PeriodeBantuanPolicy
{
    /**
     * Determine if the user can view the period.
     */
    public function view(User $user, PeriodeBantuan $period): bool
    {
        // Super admin can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Paroki user can view periods in their paroki
        if ($user->hasRole('paroki') && $user->paroki_id === $period->paroki_id) {
            return true;
        }

        // Stasi users can view periods in their paroki
        if ($user->hasRole('stasi') && $user->paroki_id === $period->paroki_id) {
            return true;
        }

        // Lingkungan leaders can view periods in their paroki
        if ($user->hasRole('ketua_lingkungan_stasi') && $user->paroki_id === $period->paroki_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can create periods.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('paroki') || $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can update the period.
     */
    public function update(User $user, PeriodeBantuan $period): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Only paroki can update their own period, and only in draft status
        return $user->hasRole('paroki')
            && $user->paroki_id === $period->paroki_id
            && in_array($period->status, ['draft', 'open'], true);
    }

    /**
     * Determine if user can manage ranking for period
     */
    public function manageRanking(User $user, PeriodeBantuan $period): bool
    {
        return $user->hasRole('paroki') && $user->paroki_id === $period->paroki_id
            || $user->hasRole('super_admin');
    }
}
