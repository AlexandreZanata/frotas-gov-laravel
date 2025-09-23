<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\Auditable;

class Tire extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'serial_number','brand','model','dimension','purchase_date','initial_tread_depth_mm','current_tread_depth_mm',
        'expected_tread_life_km','accumulated_km','status','current_vehicle_id','position','installed_at','removed_at',
        'life_cycles','notes'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'installed_at' => 'datetime',
        'removed_at' => 'datetime'
    ];

    /*
     * Relationships
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class,'current_vehicle_id');
    }

    public function events()
    {
        return $this->hasMany(TireEvent::class);
    }

    public function mounts()
    {
        return $this->hasMany(VehicleTire::class);
    }

    /*
     * Scopes
     */
    public function scopeCritical($q)
    {
        return $q->where('status','critical');
    }

    public function scopeAttention($q)
    {
        return $q->where('status','attention');
    }

    /*
     * Helpers
     */
    public function getLifeUsagePercentAttribute(): ?float
    {
        if ($this->expected_tread_life_km && $this->expected_tread_life_km > 0) {
            $base = min(100, ($this->accumulated_km / $this->expected_tread_life_km) * 100);
            return (float) number_format($base, 1, '.', '');
        }
        return null;
    }

    public function refreshStatusFromMetrics(): void
    {
        if ($this->status === 'recap_out') return; // atualizado de retread_out para recap_out
        $percent = $this->life_usage_percent;
        if ($percent === null) return;
        $new = 'stock';
        if ($this->current_vehicle_id) $new = 'in_use';
        if ($percent >= 90) $new = 'critical';
        elseif ($percent >= 75) $new = 'attention';
        if ($new !== $this->status) {
            $this->status = $new;
            $this->saveQuietly();
        }
    }
}
