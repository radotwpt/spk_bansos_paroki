<?php

namespace App\Policies;

use App\Models\User;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            'super_admin',
            'paroki',
            'stasi',
            'ketua_lingkungan_paroki',
            'ketua_lingkungan_stasi',
        ], true);
    }
}
