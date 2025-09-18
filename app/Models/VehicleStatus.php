<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- ADICIONE ESTA LINHA
use Illuminate\Database\Eloquent\Model;

class VehicleStatus extends Model
{
    use HasFactory; // Agora esta linha vai funcionar corretamente

    protected $guarded = ['id'];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
}
