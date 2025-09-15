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
        'plate',
        'renavam',
        'year',
        'current_km',
        'tank_capacity',
        'fuel_type',
        'category_id',
        'current_secretariat_id',
        'current_department_id',
        'status',
        'document_path',
    ];

    /**
     * Get the category associated with the vehicle.
     */
    public function category()
    {
        return $this->belongsTo(VehicleCategory::class);
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
}
