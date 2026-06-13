<?php

namespace App\Policies;

use App\Models\CalonPenerima;
use App\Models\User;

class CalonPenerimaPolicy
{
    /**
     * Determine if the user can view the model.
     */
    public function view(User $user, CalonPenerima $calonPenerima): bool
    {
        // Super admin can view all
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin paroki can view candidates in their paroki
        if ($user->hasRole('paroki') && $user->paroki_id === $calonPenerima->paroki_id) {
            return true;
        }

        // Stasi coordinator can view candidates in their stasi
        if ($user->hasRole('stasi') && $user->stasi_id === $calonPenerima->stasi_id) {
            return true;
        }

        // Lingkungan leader can view candidates in their lingkungan
        if ($user->hasRole('ketua_lingkungan_stasi') && $user->lingkungan_id === $calonPenerima->lingkungan_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('ketua_lingkungan_stasi') || $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(User $user, CalonPenerima $calonPenerima): bool
    {
        // Only creator or super admin can edit draft/revision status
        if (in_array($calonPenerima->status, ['draft', 'revision_requested'], true)) {
            return $user->id === $calonPenerima->created_by || $user->hasRole('super_admin');
        }

        return $user->hasRole('super_admin');
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(User $user, CalonPenerima $calonPenerima): bool
    {
        return $user->id === $calonPenerima->created_by || $user->hasRole('super_admin');
    }

    /**
     * Determine if user can approve/reject candidate
     */
    public function approve(User $user, CalonPenerima $calonPenerima): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($calonPenerima->status === 'submitted_to_stasi') {
            return $user->hasRole('stasi') && $user->stasi_id === $calonPenerima->stasi_id;
        }

        if ($calonPenerima->status === 'sent_to_paroki') {
            return $user->hasRole('paroki') && $user->paroki_id === $calonPenerima->paroki_id;
        }

        return false;
    }
}
