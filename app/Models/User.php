<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    // --- RELACIONAMENTOS ADICIONADOS ---

    /**
     * Define o relacionamento: um Usuário (User) pertence a um Perfil (Role).
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Define o relacionamento: um Usuário (User) pertence a uma Secretaria (Secretariat).
     */
    public function secretariat(): BelongsTo
    {
        return $this->belongsTo(Secretariat::class);
    }

    /**
     * Define o relacionamento: um Usuário (User) pertence a um Departamento (Department).
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
