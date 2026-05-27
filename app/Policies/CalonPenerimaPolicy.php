<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CalonPenerima;

class CalonPenerimaPolicy
{
    public function view(User $user, CalonPenerima $calon)
    {
        if (in_array($user->role ?? '', ['super_admin'])) return true;
        if ($user->role === 'ketua_lingkungan_stasi' && $user->lingkungan_stasi_id === $calon->lingkungan_stasi_id) return true;
        if ($user->role === 'stasi' && $user->stasi_id === $calon->stasi_id) return true;
        return false;
    }

    public function update(User $user, CalonPenerima $calon)
    {
        if (in_array($user->role ?? '', ['super_admin'])) return true;

        // Only allow updates when still in draft
        if ($calon->status_alur !== 'draft') return false;

        return $this->view($user, $calon);
    }

    public function delete(User $user, CalonPenerima $calon)
    {
        if (in_array($user->role ?? '', ['super_admin'])) return true;

        // Only allow deletes when still in draft
        if ($calon->status_alur !== 'draft') return false;

        return $this->view($user, $calon);
    }

    public function approve(User $user, CalonPenerima $calon)
    {
        if (in_array($user->role ?? '', ['super_admin'])) return true;

        // Only stasi can approve
        if ($user->role !== 'stasi') return false;

        // Check if from same stasi
        return $user->stasi_id === $calon->stasi_id;
    }

    public function reject(User $user, CalonPenerima $calon)
    {
        if (in_array($user->role ?? '', ['super_admin'])) return true;

        // Only stasi can reject
        if ($user->role !== 'stasi') return false;

        // Check if from same stasi
        return $user->stasi_id === $calon->stasi_id;
    }
}
