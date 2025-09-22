<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fueling extends Model
{
    use HasFactory;

    public $timestamps = false; // migration só define created_at custom

    protected $fillable = [
        'run_id','user_id','vehicle_id','secretariat_id','gas_station_id','fuel_type_id',
        'gas_station_name','km','liters','total_value','invoice_path','is_manual','created_at'
    ];

    protected $casts = [
        'is_manual'=>'boolean','created_at'=>'datetime'
    ];

    public function run(){ return $this->belongsTo(Run::class); }
    public function user(){ return $this->belongsTo(User::class); }
    public function vehicle(){ return $this->belongsTo(Vehicle::class); }
    public function secretariat(){ return $this->belongsTo(Secretariat::class); }
    public function gasStation(){ return $this->belongsTo(GasStation::class); }
    public function fuelType(){ return $this->belongsTo(FuelType::class); }
}
