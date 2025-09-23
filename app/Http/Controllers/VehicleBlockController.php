<?php

namespace App\Http\Controllers;

use App\Models\{Vehicle, VehicleStatus, AuditLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleBlockController extends Controller
{
    private function ensureCanManage(Request $request): void
    {
        $user = $request->user();
        if (!$user || (int)$user->role_id !== 1) { // alinhado com views existentes
            abort(403,'Acesso não autorizado.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureCanManage($request);
        return view('vehicles.blocking');
    }

    public function search(Request $request)
    {
        $this->ensureCanManage($request);
        $q = trim($request->get('q',''));
        $query = Vehicle::query()->with('status');
        if ($q !== '') {
            $query->where(function($sub) use ($q) {
                $sub->where('model','like',"%$q%")
                    ->orWhere('brand','like',"%$q%")
                    ->orWhere('plate','like',"%$q%");
            });
        }
        $vehicles = $query->orderBy('model')->limit(50)->get()->map(function($v){
            return [
                'id' => $v->id,
                'brand' => $v->brand,
                'model' => $v->model,
                'plate' => $v->plate,
                'status' => $v->status?->name,
                'status_slug' => $v->status?->slug,
                'status_color' => $v->status?->color,
            ];
        });
        return response()->json(['data'=>$vehicles]);
    }

    private function getStatusId(string $slug): ?int
    {
        return VehicleStatus::where('slug',$slug)->value('id');
    }

    public function block(Request $request, Vehicle $vehicle)
    {
        $this->ensureCanManage($request);
        $request->validate(['keyword'=>['required','in:BLOQUEAR']]);
        $blockedId = $this->getStatusId('bloqueado');
        if (!$blockedId) { return response()->json(['message'=>'Status bloqueado não configurado'],422); }
        if ($vehicle->vehicle_status_id == $blockedId) {
            return response()->json(['message'=>'Veículo já bloqueado'],200);
        }
        $old = $vehicle->vehicle_status_id;
        $vehicle->update(['vehicle_status_id'=>$blockedId]);
        $this->log($request,'block_vehicle',$vehicle->id,['vehicle_status_id'=>$old],['vehicle_status_id'=>$blockedId]);
        return response()->json(['message'=>'Veículo bloqueado com sucesso']);
    }

    public function unblock(Request $request, Vehicle $vehicle)
    {
        $this->ensureCanManage($request);
        $request->validate(['keyword'=>['required','in:DESBLOQUEAR']]);
        $availableId = $this->getStatusId('disponivel');
        if (!$availableId) { return response()->json(['message'=>'Status disponível não configurado'],422); }
        if ($vehicle->vehicle_status_id == $availableId) {
            return response()->json(['message'=>'Veículo já disponível'],200);
        }
        $old = $vehicle->vehicle_status_id;
        $vehicle->update(['vehicle_status_id'=>$availableId]);
        $this->log($request,'unblock_vehicle',$vehicle->id,['vehicle_status_id'=>$old],['vehicle_status_id'=>$availableId]);
        return response()->json(['message'=>'Veículo desbloqueado com sucesso']);
    }

    private function log(Request $request, string $action, int $recordId, array $old, array $new): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'table_name' => 'vehicles',
            'record_id' => $recordId,
            'old_value' => $old,
            'new_value' => $new,
            'ip_address' => $request->ip()
        ]);
    }
}
