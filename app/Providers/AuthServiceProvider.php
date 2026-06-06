<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\ActivityLog;
use App\Models\CalonPenerima;
use App\Models\User;
use App\Policies\ActivityLogPolicy;
use App\Policies\CalonPenerimaPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        CalonPenerima::class => CalonPenerimaPolicy::class,
        ActivityLog::class => ActivityLogPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::define('manage-master-data', function (User $user) {
            return $user->role === 'super_admin';
        });
    }
}
