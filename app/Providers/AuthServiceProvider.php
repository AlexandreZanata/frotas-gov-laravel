<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\{OilProduct, OilChangeLog, Tire, Fine};
use App\Policies\{OilProductPolicy, OilChangeLogPolicy, TirePolicy, FinePolicy};

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        OilProduct::class => OilProductPolicy::class,
        OilChangeLog::class => OilChangeLogPolicy::class,
        Tire::class => TirePolicy::class,
        Fine::class => FinePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
