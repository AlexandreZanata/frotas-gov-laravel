<?php
namespace App\Policies;

use App\Models\{Fine, User};

class FinePolicy
{
    public function before(User $user, string $ability)
    {
        return null; // espaço para super admin
    }

    public function viewAny(User $user): bool
    {
        // gestores e mecânico podem listar; motorista vê somente suas próprias (tratado no controller)
        return $user->role_id <= 3 || $user->role_id == 4; // assumindo 4 = driver
    }

    public function view(User $user, Fine $fine): bool
    {
        if ($user->role_id <= 3) return true; // gestor/mecânico
        return $fine->driver_id === $user->id; // motorista só sua multa
    }

    public function create(User $user): bool
    {
        return $user->role_id <= 2; // apenas gestores
    }

    public function update(User $user, Fine $fine): bool
    {
        return $user->role_id <= 2 && $fine->status !== 'pago';
    }

    public function delete(User $user, Fine $fine): bool
    {
        return $user->role_id <= 2 && $fine->status === 'draft';
    }

    public function changeStatus(User $user, Fine $fine): bool
    {
        return $user->role_id <= 2; // gestores
    }

    public function acknowledge(User $user, Fine $fine): bool
    {
        return $fine->driver_id === $user->id && $fine->acknowledged_at === null;
    }
}

