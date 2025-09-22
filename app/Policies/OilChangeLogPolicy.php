<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OilChangeLog;

class OilChangeLogPolicy
{
    public function viewAny(User $user): bool
    {
        // Gestor geral, setorial e mecânico podem ver histórico
        return $user->role_id <= 3;
    }

    public function view(User $user, OilChangeLog $log): bool
    {
        return $user->role_id <= 3;
    }

    public function create(User $user): bool
    {
        // Mecânico (3) e gestores (1,2) podem registrar troca
        return $user->role_id <= 3;
    }

    public function update(User $user, OilChangeLog $log): bool
    {
        // Apenas gestores podem editar registros
        return $user->role_id <= 2;
    }

    public function delete(User $user, OilChangeLog $log): bool
    {
        return $user->role_id <= 2;
    }
}

