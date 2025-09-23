<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class VehicleTire extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'tire_id','vehicle_id','position','mounted_at','start_odometer_km','dismounted_at','end_odometer_km','km_used','active'
    ];

    protected $casts = [
        'mounted_at' => 'datetime',
        'dismounted_at' => 'datetime',
        'active' => 'boolean'
    ];

    public function tire()
    {
        return $this->belongsTo(Tire::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
