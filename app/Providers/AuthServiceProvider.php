<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\CalonPenerima;
use App\Policies\CalonPenerimaPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        CalonPenerima::class => CalonPenerimaPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
