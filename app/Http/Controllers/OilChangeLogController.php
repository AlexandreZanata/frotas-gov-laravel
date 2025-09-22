<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOilChangeLogRequest;
use App\Models\{OilChangeLog, OilProduct, Vehicle};
use Illuminate\Support\Facades\DB;

class OilChangeLogController extends Controller
{
    public function store(StoreOilChangeLogRequest $request)
    {
        $this->authorize('create', \App\Models\OilChangeLog::class);

        $data = $request->validated();
        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        $product = isset($data['oil_product_id']) ? OilProduct::find($data['oil_product_id']) : null;

        return DB::transaction(function() use ($data,$vehicle,$product) {
            // Fallback de intervalos: informado no formulário -> produto -> categoria -> defaults
            $cat = $vehicle->category; // via relacionamento
            $intervalKm = $data['interval_km_used']
                ?? $product?->recommended_interval_km
                ?? $cat?->oil_change_km
                ?? 10000;
            $intervalDays = $data['interval_days_used']
                ?? $product?->recommended_interval_days
                ?? $cat?->oil_change_days
                ?? 180;

            $nextKm = $intervalKm ? ($data['odometer_km'] + $intervalKm) : null;
            $nextDate = $intervalDays ? (\Carbon\Carbon::parse($data['change_date'])->addDays($intervalDays)) : null;

            $unitCost = $product?->unit_cost;
            $totalCost = $unitCost ? bcmul((string)$unitCost,(string)$data['quantity_used'],2) : null;

            $log = OilChangeLog::create([
                'vehicle_id' => $vehicle->id,
                'oil_product_id' => $product?->id,
                'user_id' => auth()->id(),
                'change_date' => $data['change_date'],
                'odometer_km' => $data['odometer_km'],
                'quantity_used' => $data['quantity_used'],
                'unit_cost_at_time' => $unitCost,
                'total_cost' => $totalCost,
                'next_change_km' => $nextKm,
                'next_change_date' => $nextDate,
                'interval_km_used' => $intervalKm,
                'interval_days_used' => $intervalDays,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($product) {
                $product->decrement('stock_quantity', (int) ceil($data['quantity_used']));
            }

            if ($data['odometer_km'] > $vehicle->current_km) {
                $vehicle->current_km = $data['odometer_km'];
                $vehicle->save();
            }

            return redirect()->route('oil.maintenance')
                ->with('success', 'Troca de óleo registrada com sucesso.');
        });
    }
}
