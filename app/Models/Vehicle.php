<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'brand',
        'model',
        'year',
        'plate',
        'renavam',
        'chassi',
        'prefix', // <-- Adicionado
        'current_km',
        'fuel_type',
        'document_path',
        'category_id',
        'current_secretariat_id',
        'current_department_id',
        'tank_capacity',
        'avg_km_per_liter', // <-- Adicionado
        'status',
    ];

    /**
     * Get the category associated with the vehicle.
     */
    public function category()
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    /**
     * Get the current secretariat associated with the vehicle.
     */
    public function secretariat()
    {
        return $this->belongsTo(Secretariat::class, 'current_secretariat_id');
    }

    /**
     * Get the current department associated with the vehicle.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'current_department_id');
    }

    public function status()
    {
        return $this->belongsTo(VehicleStatus::class, 'vehicle_status_id');
    }
    public function runs()
    {
        return $this->hasMany(Run::class);
    }

    public function fuelType()
    {
        return $this->belongsTo(FuelType::class);
    }


}
