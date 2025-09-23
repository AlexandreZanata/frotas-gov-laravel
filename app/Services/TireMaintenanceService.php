<?php
namespace App\Services;

use App\Models\{Tire, TireEvent, Vehicle};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TireMaintenanceService
{
    public function dashboardStats(): array
    {
        return Cache::remember('tires.dashboard.stats', 30, function () {
            $totalTires = Tire::count();
            $critical = Tire::critical()->count();
            $attention = Tire::attention()->count();
            $vehiclesMonitored = Vehicle::has('tires')->count();
            $avgLife = $this->averageLifeUsage();
            return [
                'total_tires' => $totalTires,
                'critical' => $critical,
                'attention' => $attention,
                'vehicles_monitored' => $vehiclesMonitored,
                'avg_life_usage' => $avgLife,
            ];
        });
    }

    public function recentEvents(int $limit = 10)
    {
        return TireEvent::with(['tire','user','vehicle'])->latest()->limit($limit)->get();
    }

    public function averageLifeUsage(): ?float
    {
        $query = Tire::query()->whereNotNull('expected_tread_life_km')->where('expected_tread_life_km', '>', 0);
        $count = $query->count();
        if ($count === 0) return null;
        $sum = 0;
        $tires = $query->get(['accumulated_km', 'expected_tread_life_km']);
        foreach ($tires as $t) {
            $sum += min(100, ($t->accumulated_km / $t->expected_tread_life_km) * 100);
        }
        $avg = $sum / $count;
        return (float) number_format($avg, 1, '.', '');
    }

    public function vehicleLayout(Vehicle $vehicle): array
    {
        $vehicle->loadMissing('category.tireLayout');
        $positionsMeta = [];
        if ($vehicle->category && $vehicle->category->tireLayout) {
            $positionsMeta = collect($vehicle->category->tireLayout->positions)->map(fn($p)=>[
                'code'=>$p['code'],
                'label'=>$p['label'] ?? $p['code'],
                'x'=>$p['x'] ?? null,
                'y'=>$p['y'] ?? null,
            ])->values()->all();
        } else {
            $positionsMeta = [
                ['code'=>'FL','label'=>'Dianteiro Esquerdo'],
                ['code'=>'FR','label'=>'Dianteiro Direito'],
                ['code'=>'RL','label'=>'Traseiro Esquerdo'],
                ['code'=>'RR','label'=>'Traseiro Direito'],
            ];
        }
        $codes = array_column($positionsMeta,'code');
        $tires = Tire::where('current_vehicle_id',$vehicle->id)->get()->keyBy('position');
        $map = [];
        foreach ($codes as $c) { $map[$c] = $tires[$c] ?? null; }
        return [
            'positions'=>$positionsMeta,
            'tires'=>$map,
        ];
    }

    public function internalRotation(Vehicle $vehicle, string $posA, string $posB): array
    {
        if ($posA === $posB) {
            return ['success' => false, 'message' => 'Posições devem ser diferentes'];
        }
        return DB::transaction(function () use ($vehicle, $posA, $posB) {
            $tireA = Tire::where('current_vehicle_id', $vehicle->id)->where('position', $posA)->firstOrFail();
            $tireB = Tire::where('current_vehicle_id', $vehicle->id)->where('position', $posB)->firstOrFail();
            $tireA->position = $posB; $tireA->save();
            $tireB->position = $posA; $tireB->save();
            TireEvent::create([
                'tire_id' => $tireA->id,
                'user_id' => auth()->id(),
                'vehicle_id' => $vehicle->id,
                'type' => 'rotation_internal',
                'from_vehicle_id' => $vehicle->id,
                'to_vehicle_id' => $vehicle->id,
                'from_position' => $posA,
                'to_position' => $posB,
                'odometer_km' => $vehicle->current_km,
            ]);
            TireEvent::create([
                'tire_id' => $tireB->id,
                'user_id' => auth()->id(),
                'vehicle_id' => $vehicle->id,
                'type' => 'rotation_internal',
                'from_vehicle_id' => $vehicle->id,
                'to_vehicle_id' => $vehicle->id,
                'from_position' => $posB,
                'to_position' => $posA,
                'odometer_km' => $vehicle->current_km,
            ]);
            return ['success' => true, 'message' => 'Rodízio interno realizado.'];
        });
    }

    public function externalOut(Vehicle $vehicle, string $position): array
    {
        return DB::transaction(function () use ($vehicle, $position) {
            $tire = Tire::where('current_vehicle_id', $vehicle->id)->where('position', $position)->firstOrFail();
            $fromPos = $tire->position;
            $tire->current_vehicle_id = null;
            $tire->position = null;
            $tire->status = 'stock';
            $tire->removed_at = now();
            $tire->save();
            TireEvent::create([
                'tire_id' => $tire->id,
                'user_id' => auth()->id(),
                'vehicle_id' => $vehicle->id,
                'type' => 'rotation_external_out',
                'from_vehicle_id' => $vehicle->id,
                'from_position' => $fromPos,
                'odometer_km' => $vehicle->current_km,
            ]);
            return ['success' => true, 'message' => 'Pneu movido para estoque.'];
        });
    }

    public function externalIn(Vehicle $vehicle, Tire $tire, string $position): array
    {
        if ($tire->current_vehicle_id) {
            return ['success' => false, 'message' => 'Pneu já está instalado em um veículo.'];
        }
        // Validar se posição existe no layout do veículo (dinâmico ou padrão)
        $allowedPositions = [];
        $vehicle->loadMissing('category.tireLayout');
        if ($vehicle->category && $vehicle->category->tireLayout) {
            $allowedPositions = collect($vehicle->category->tireLayout->positions)->pluck('code')->map(fn($c)=>strtoupper($c))->all();
        } else {
            $allowedPositions = ['FL','FR','RL','RR'];
        }
        $position = strtoupper($position);
        if (!in_array($position, $allowedPositions)) {
            return ['success' => false, 'message' => 'Posição inválida para este veículo.'];
        }
        return DB::transaction(function () use ($vehicle, $tire, $position) {
            $occupied = Tire::where('current_vehicle_id', $vehicle->id)->where('position', $position)->exists();
            if ($occupied) return ['success' => false, 'message' => 'Posição já ocupada.'];
            $tire->current_vehicle_id = $vehicle->id;
            $tire->position = $position;
            $tire->status = 'in_use';
            $tire->installed_at = now();
            $tire->save();
            TireEvent::create([
                'tire_id' => $tire->id,
                'user_id' => auth()->id(),
                'vehicle_id' => $vehicle->id,
                'type' => 'rotation_external_in',
                'to_vehicle_id' => $vehicle->id,
                'to_position' => $position,
                'odometer_km' => $vehicle->current_km,
            ]);
            return ['success' => true, 'message' => 'Pneu instalado no veículo.'];
        });
    }

    public function replacement(Vehicle $vehicle, array $mapPositionToTireId): array
    {
        return DB::transaction(function () use ($vehicle, $mapPositionToTireId) {
            $results = [];
            foreach ($mapPositionToTireId as $position => $tireId) {
                $tire = Tire::findOrFail($tireId);
                if ($tire->current_vehicle_id) { $results[] = ['pos' => $position, 'ok' => false, 'msg' => 'Pneu já instalado']; continue; }
                $occupied = Tire::where('current_vehicle_id', $vehicle->id)->where('position', $position)->first();
                if ($occupied) {
                    $old = $occupied;
                    $old->current_vehicle_id = null; $old->position = null; $old->status = 'stock'; $old->removed_at = now(); $old->save();
                    TireEvent::create([
                        'tire_id' => $old->id,
                        'user_id' => auth()->id(),
                        'vehicle_id' => $vehicle->id,
                        'type' => 'replacement',
                        'from_vehicle_id' => $vehicle->id,
                        'from_position' => $position,
                        'odometer_km' => $vehicle->current_km,
                        'notes' => 'Removido para substituição'
                    ]);
                }
                $tire->current_vehicle_id = $vehicle->id;
                $tire->position = $position;
                $tire->status = 'in_use';
                $tire->installed_at = now();
                $tire->save();
                TireEvent::create([
                    'tire_id' => $tire->id,
                    'user_id' => auth()->id(),
                    'vehicle_id' => $vehicle->id,
                    'type' => 'replacement',
                    'to_vehicle_id' => $vehicle->id,
                    'to_position' => $position,
                    'odometer_km' => $vehicle->current_km
                ]);
                $results[] = ['pos' => $position, 'ok' => true];
            }
            return ['success' => true, 'results' => $results];
        });
    }

    public function sendForRetread(Tire $tire, ?string $notes = null): array
    {
        if ($tire->status === 'recap_out') {
            return ['success' => false, 'message' => 'Pneu já enviado para recapagem.'];
        }
        return DB::transaction(function () use ($tire, $notes) {
            $vehicleId = $tire->current_vehicle_id;
            $fromPosition = $tire->position;
            if ($vehicleId) {
                $tire->current_vehicle_id = null;
                $tire->position = null;
                $tire->removed_at = now();
            }
            $tire->status = 'recap_out';
            $tire->save();
            TireEvent::create([
                'tire_id' => $tire->id,
                'user_id' => auth()->id(),
                'vehicle_id' => $vehicleId,
                'type' => 'recap_sent',
                'from_vehicle_id' => $vehicleId,
                'from_position' => $fromPosition,
                'notes' => $notes
            ]);
            Cache::forget('tires.dashboard.stats');
            return ['success' => true, 'message' => 'Pneu enviado para recapagem.'];
        });
    }

    public function receiveFromRetread(Tire $tire, ?float $newTreadDepth = null, ?int $expectedLifeKm = null, ?string $notes = null): array
    {
        if ($tire->status !== 'recap_out') {
            return ['success' => false, 'message' => 'Pneu não está marcado como enviado para recapagem.'];
        }
        return DB::transaction(function () use ($tire, $newTreadDepth, $expectedLifeKm, $notes) {
            if ($newTreadDepth !== null) {
                $tire->current_tread_depth_mm = $newTreadDepth;
            }
            if ($expectedLifeKm !== null) {
                $tire->expected_tread_life_km = $expectedLifeKm;
            }
            $tire->life_cycles = (int) $tire->life_cycles + 1;
            $tire->status = 'stock';
            $tire->save();
            TireEvent::create([
                'tire_id' => $tire->id,
                'user_id' => auth()->id(),
                'type' => 'recap_returned',
                'notes' => $notes
            ]);
            Cache::forget('tires.dashboard.stats');
            return ['success' => true, 'message' => 'Pneu retornou da recapagem e está em estoque.'];
        });
    }
}
