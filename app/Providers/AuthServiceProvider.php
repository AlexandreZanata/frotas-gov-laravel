<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\{OilProduct, OilChangeLog};
use App\Policies\{OilProductPolicy, OilChangeLogPolicy};

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        OilProduct::class => OilProductPolicy::class,
        OilChangeLog::class => OilChangeLogPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}

