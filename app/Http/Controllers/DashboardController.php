<?php

namespace App\Http\Controllers;

use App\Models\Fueling;
use App\Models\Run;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $secretariatId = $user->secretariat_id;

        // Base para as queries, aplicando filtro de secretaria se não for Gestor Geral
        $runsQuery = ($user->role_id == 1) ? Run::query() : Run::where('secretariat_id', $secretariatId);
        $vehiclesQuery = ($user->role_id == 1) ? Vehicle::query() : Vehicle::where('current_secretariat_id', $secretariatId);
        $fuelingsQuery = ($user->role_id == 1) ? Fueling::query() : Fueling::where('secretariat_id', $secretariatId);

        // --- DADOS PARA OS KPIs ---
        $totalRuns = $runsQuery->count();
        $totalVehiclesInUse = $vehiclesQuery->where('status', 'in_use')->count();
        $totalFuelCost = $fuelingsQuery->sum('total_value');
        $totalKm = $runsQuery->where('status', 'completed')->where(DB::raw('end_km - start_km'), '>=', 0)->sum(DB::raw('end_km - start_km'));

        // --- DADOS PARA OS GRÁFICOS ---
        $runsByVehicleQuery = ($user->role_id == 1) ? Run::query() : Run::where('secretariat_id', $secretariatId);
        $runsByVehicleData = $runsByVehicleQuery
            ->join('vehicles', 'runs.vehicle_id', '=', 'vehicles.id')
            ->select('vehicles.name', DB::raw('count(runs.id) as run_count'))
            ->groupBy('vehicles.name')
            ->orderBy('run_count', 'desc')
            ->take(10)
            ->get();

        $monthlyFuelQuery = ($user->role_id == 1) ? Fueling::query() : Fueling::where('secretariat_id', $secretariatId);
        $monthlyFuelData = $monthlyFuelQuery
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('SUM(total_value) as total_value'))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return view('dashboard', [
            'totalRuns' => $totalRuns,
            'totalVehiclesInUse' => $totalVehiclesInUse,
            'totalFuelCost' => number_format($totalFuelCost, 2, ',', '.'),
            'totalKm' => number_format($totalKm, 0, ',', '.'),
            'runsByVehicleData' => json_encode($runsByVehicleData),
            'monthlyFuelData' => json_encode($monthlyFuelData),
        ]);
    }
}
