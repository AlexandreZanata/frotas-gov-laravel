<?php
namespace App\Policies;

use App\Models\Tire;
use App\Models\User;

class TirePolicy
{
    public function before(User $user, string $ability)
    {
        return null; // espaço para super admin
    }

    public function viewAny(User $user): bool
    {
        return $user->role_id <= 3; // inclui mecânico
    }

    public function view(User $user, Tire $tire): bool
    {
        return $user->role_id <= 3;
    }

    public function create(User $user): bool
    {
        return $user->role_id <= 2; // gestores
    }

    public function update(User $user, Tire $tire): bool
    {
        return $user->role_id <= 2;
    }

    public function delete(User $user, Tire $tire): bool
    {
        return $user->role_id <= 2;
    }

    public function action(User $user, Tire $tire): bool
    {
        return $user->role_id <= 3; // mecânico pode executar ações de manutenção
    }
}

