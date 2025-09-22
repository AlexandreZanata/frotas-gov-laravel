<?php

namespace App\Models;

use App\Traits\Auditable; // 1. Importe o Trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleCategory extends Model
{
    use HasFactory, Auditable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'layout_key',
        'oil_change_km',
        'oil_change_days',
        'tire_change_km',
        'tire_change_days',
        'tire_layout_id',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'vehicle_category_id');
    }
    public function tireLayout()
    {
        return $this->belongsTo(TireLayout::class,'tire_layout_id');
    }
}
