<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\{OilProduct, OilChangeLog, Tire, Fine};
use App\Policies\{OilProductPolicy, OilChangeLogPolicy, TirePolicy, FinePolicy};
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\VehicleTransfer;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        OilProduct::class => OilProductPolicy::class,
        OilChangeLog::class => OilChangeLogPolicy::class,
        Tire::class => TirePolicy::class,
        Fine::class => FinePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('manage-transfer', function (User $user, VehicleTransfer $transfer) {
            // Regra 1: Gestor Geral (role_id 1) pode gerenciar qualquer transferência.
            if ($user->role_id === 1) {
                return true;
            }

            // Regra 2: Gestor Setorial (role_id 2) só pode gerenciar transferências de sua secretaria de origem.
            if ($user->role_id === 2 && $user->secretariat_id === $transfer->origin_secretariat_id) {
                return true;
            }

            return false;
        });
    }
}
