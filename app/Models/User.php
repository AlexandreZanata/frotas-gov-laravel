<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log; // Importa a classe Log

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'cpf',
        'email',
        'password',
        'role_id',
        'secretariat_id',
        'department_id',
        'cnh_number',
        'cnh_expiry_date',
        'profile_photo_path',
        'cnh_photo_path',
        'phone',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'cnh_expiry_date' => 'date',
        ];
    }

    /**
     * O método booted é usado para registrar eventos do model.
     * Ele é executado apenas uma vez quando o model é inicializado.
     */
    protected static function booted(): void
    {
        // Evento 'deleting' é acionado um pouco antes de um usuário ser excluído.
        static::deleting(function (User $user) {
            // Log para depuração
            Log::info("Evento 'deleting' acionado para o usuário ID: {$user->id}");

            // Apaga todos os registros relacionados em outras tabelas para evitar erros de chave estrangeira.
            $user->runs()->delete();
            $user->checklists()->delete();
            $user->fuelings()->delete();
            $user->vehicleTransfers()->delete();

            // Exclui os logs de auditoria onde este usuário foi o autor da ação.
            $user->auditLogs()->delete();

            Log::info("Todos os registros relacionados para o usuário ID: {$user->id} foram marcados para exclusão.");
        });
    }

    // --- RELACIONAMENTOS ---

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function secretariat(): BelongsTo
    {
        return $this->belongsTo(Secretariat::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(Run::class, 'driver_id');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class);
    }

    public function fuelings(): HasMany
    {
        return $this->hasMany(Fueling::class);
    }

    public function vehicleTransfers(): HasMany
    {
        return $this->hasMany(VehicleTransfer::class, 'requester_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function finesAsDriver(): HasMany
    {
        return $this->hasMany(Fine::class, 'driver_id');
    }
}
