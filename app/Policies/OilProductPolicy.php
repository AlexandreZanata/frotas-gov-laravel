<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OilProduct;

class OilProductPolicy
{
    // Gestor Geral (1) e Gestor Setorial (2) podem gerenciar totalmente.
    // Mecânico (3) pode apenas visualizar (viewAny/view) e não editar estoque crítico (aqui simplificado para negar update/delete/create).
    public function before(User $user, string $ability)
    {
        // Poderia colocar super-admin aqui se existisse.
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->role_id <= 3; // inclui mecânico para ver lista
    }

    public function view(User $user, OilProduct $product): bool
    {
        return $user->role_id <= 3;
    }

    public function create(User $user): bool
    {
        return $user->role_id <= 2; // apenas gestores
    }

    public function update(User $user, OilProduct $product): bool
    {
        return $user->role_id <= 2;
    }

    public function delete(User $user, OilProduct $product): bool
    {
        return $user->role_id <= 2;
    }
}

