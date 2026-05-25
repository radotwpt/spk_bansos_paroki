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
}
