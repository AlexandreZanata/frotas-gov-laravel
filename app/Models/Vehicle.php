<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

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
        'prefix',
        'current_km',
        'fuel_type_id',
        'document_path',
        'category_id',
        'vehicle_category_id',
        'current_secretariat_id',
        'current_department_id',
        'tank_capacity',
        'avg_km_per_liter',
        'status',
        'tire_service_base_km','tire_service_base_date',
        'vehicle_status_id'
    ];

    protected $casts = [
        'tire_service_base_date'=>'date'
    ];

    /**
     * Get the category associated with the vehicle.
     */
    public function category()
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    /**
     * Get the secretariat associated with the vehicle.
     */
    public function secretariat()
    {
        return $this->belongsTo(Secretariat::class, 'current_secretariat_id');
    }

    /**
     * Get the department associated with the vehicle.
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
        return $this->belongsTo(FuelType::class, 'fuel_type_id');
    }

    public function oilChangeLogs()
    {
        return $this->hasMany(OilChangeLog::class);
    }

    public function latestOilChangeLog()
    {
        return $this->hasOne(OilChangeLog::class)->latestOfMany();
    }

    public function tires()
    {
        return $this->hasMany(Tire::class,'current_vehicle_id');
    }

    public function activeTireMounts()
    {
        return $this->hasMany(VehicleTire::class)->where('active',true);
    }

    public function tireEvents()
    {
        return $this->hasMany(TireEvent::class);
    }

    public function fines()
    {
        return $this->hasMany(Fine::class);
    }

    public function getOilMaintenanceStatusAttribute(): array
    {
        if (!Schema::hasTable('oil_change_logs') || !Schema::hasColumn('oil_change_logs','vehicle_id')) {
            // Fallback usando categoria
            $cat = $this->category;
            $nextKm = $cat ? $this->current_km + (int)$cat->oil_change_km : null;
            $nextDate = $cat ? Carbon::now()->addDays((int)$cat->oil_change_days)->format('Y-m-d') : null;
            return [
                'label' => 'Sem Registro',
                'color' => 'bg-gray-400',
                'km_progress' => 0,
                'days_progress' => 0,
                'next_km' => $nextKm,
                'next_date' => $nextDate
            ];
        }
        $log = $this->latestOilChangeLog; // eager loaded
        if (!$log) {
            $cat = $this->category;
            $nextKm = $cat ? $this->current_km + (int)$cat->oil_change_km : null;
            $nextDate = $cat ? Carbon::now()->addDays((int)$cat->oil_change_days)->format('Y-m-d') : null;
            return [
                'label' => 'Sem Registro',
                'color' => 'bg-gray-400',
                'km_progress' => 0,
                'days_progress' => 0,
                'next_km' => $nextKm,
                'next_date' => $nextDate
            ];
        }

        $product = $log->product;
        $intervalKm = $log->interval_km_used ?? $product?->recommended_interval_km;
        $intervalDays = $log->interval_days_used ?? $product?->recommended_interval_days;

        $nextKm = $log->next_change_km ?? ($intervalKm ? $log->odometer_km + $intervalKm : null);
        $nextDate = $log->next_change_date ? Carbon::parse($log->next_change_date) : ($intervalDays ? Carbon::parse($log->change_date)->copy()->addDays($intervalDays) : null);

        $now = Carbon::now();
        $kmProgress = null; $daysProgress = null;
        if ($intervalKm && $nextKm) {
            $kmUsed = $this->current_km - $log->odometer_km;
            $kmProgress = \max(0, \min(100, \round(($kmUsed / $intervalKm) * 100, 1)));
        }
        if ($intervalDays && $nextDate) {
            $daysUsed = Carbon::parse($log->change_date)->diffInDays($now);
            $daysProgress = \max(0, \min(100, \round(($daysUsed / $intervalDays) * 100, 1)));
        }

        $expired = ($nextKm && $this->current_km >= $nextKm) || ($nextDate && $now->isAfter($nextDate));
        if ($expired) {
            $label = 'Vencido'; $color = 'bg-red-600';
        } else {
            $maxProgress = max($kmProgress ?? 0, $daysProgress ?? 0);
            if ($maxProgress >= 90) { $label = 'Crítico'; $color = 'bg-red-500'; }
            elseif ($maxProgress >= 75) { $label = 'Atenção'; $color = 'bg-yellow-500'; }
            else { $label = 'Em Dia'; $color = 'bg-green-600'; }
        }

        return [
            'label' => $label,
            'color' => $color,
            'km_progress' => $kmProgress,
            'days_progress' => $daysProgress,
            'next_km' => $nextKm,
            'next_date' => $nextDate?->format('Y-m-d')
        ];
    }

    public function getTireMaintenanceStatusAttribute(): array
    {
        $cat = $this->category;
        if (!$cat) {
            return [
                'label'=>'Sem Categoria','color'=>'bg-gray-400','km_progress'=>null,'days_progress'=>null,
                'next_km'=>null,'next_date'=>null
            ];
        }
        $intervalKm = $cat->tire_change_km ?: null;
        $intervalDays = $cat->tire_change_days ?: null;
        $baseKm = $this->tire_service_base_km ?? $this->current_km; // se não definido, começa agora (progress 0)
        $baseDate = $this->tire_service_base_date ?: ($this->created_at ?? now());
        $nextKm = $intervalKm ? ($baseKm + $intervalKm) : null;
        $nextDate = $intervalDays ? (\Carbon\Carbon::parse($baseDate)->copy()->addDays($intervalDays)) : null;

        $kmProgress = null; $daysProgress = null;
        if ($intervalKm && $nextKm) {
            $kmUsed = max(0, $this->current_km - $baseKm);
            $kmProgress = \min(100, \round(($kmUsed / $intervalKm) * 100,1));
        }
        if ($intervalDays && $nextDate) {
            $daysUsed = \Carbon\Carbon::parse($baseDate)->diffInDays(now());
            $daysProgress = \min(100, \round(($daysUsed / $intervalDays) * 100,1));
        }
        $maxProgress = max($kmProgress ?? 0, $daysProgress ?? 0);
        if ($maxProgress >= 100) { $label='Vencido'; $color='bg-red-600'; }
        elseif ($maxProgress >= 90) { $label='Crítico'; $color='bg-red-500'; }
        elseif ($maxProgress >= 75) { $label='Atenção'; $color='bg-yellow-500'; }
        else { $label='Em Dia'; $color='bg-green-600'; }
        return [
            'label'=>$label,'color'=>$color,
            'km_progress'=>$kmProgress,'days_progress'=>$daysProgress,
            'next_km'=>$nextKm,'next_date'=>$nextDate?->format('Y-m-d')
        ];
    }
}
