<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Run extends Model
{
    use HasFactory;

    // Desativa timestamps automáticos (created_at, updated_at) se não existirem na sua tabela
    public $timestamps = false;

    protected $fillable = [
        'checklist_id',
        'vehicle_id',
        'driver_id',
        'secretariat_id',
        'start_km',
        'end_km',
        'start_time',
        'end_time',
        'destination',
        'stop_point',
        'status',
        'blocked_at',
        'blocked_by',
        'blocked_previous_status'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'blocked_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        // A corrida pertence a um "driver", que é um User
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function secretariat()
    {
        return $this->belongsTo(Secretariat::class);
    }

    public function checklist()
    {
        // Uma corrida tem um checklist
        return $this->hasOne(Checklist::class);
    }

    public function fuelings()
    {
        // Uma corrida pode ter vários abastecimentos
        return $this->hasMany(Fueling::class);
    }

    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }
}
