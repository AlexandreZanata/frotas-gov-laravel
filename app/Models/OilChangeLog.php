<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class OilChangeLog extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'vehicle_id','oil_product_id','user_id','change_date','odometer_km','quantity_used',
        'unit_cost_at_time','total_cost','next_change_km','next_change_date','interval_km_used',
        'interval_days_used','notes'
    ];

    protected $casts = [
        'change_date' => 'date',
        'next_change_date' => 'date'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function product()
    {
        return $this->belongsTo(OilProduct::class, 'oil_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
