<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class TireEvent extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'tire_id','user_id','vehicle_id','type','from_vehicle_id','to_vehicle_id','from_position','to_position','odometer_km','notes'
    ];

    public function tire()
    {
        return $this->belongsTo(Tire::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault(['name'=>'Sistema']);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function fromVehicle()
    {
        return $this->belongsTo(Vehicle::class,'from_vehicle_id');
    }

    public function toVehicle()
    {
        return $this->belongsTo(Vehicle::class,'to_vehicle_id');
    }
}
