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
    /**
     * Exibe o dashboard com os dados consolidados.
     */
    public function index(): View
    {
        $user = Auth::user();
        $secretariatId = $user->secretariat_id;

        // Base para as queries, aplicando filtro de secretaria se não for Gestor Geral (role_id = 1)
        $runsQuery = ($user->role_id == 1) ? Run::query() : Run::where('secretariat_id', $secretariatId);
        $vehiclesQuery = ($user->role_id == 1) ? Vehicle::query() : Vehicle::where('current_secretariat_id', $secretariatId);
        $fuelingsQuery = ($user->role_id == 1) ? Fueling::query() : Fueling::where('secretariat_id', $secretariatId);

        // --- DADOS PARA OS KPIs ---

        // Clonamos a query base para não afetar outros cálculos
        $totalRuns = $runsQuery->clone()->count();

        // Veículos em uso
        $totalVehiclesInUse = $vehiclesQuery->clone()
            ->whereHas('status', function ($query) {
                $query->where('slug', 'em-uso');
            })
            ->count(); // <-- PONTO E VÍRGULA ADICIONADO AQUI

        // Custo total com combustível
        $totalFuelCost = $fuelingsQuery->clone()->sum('total_value');

        // KM total rodado em corridas completas
        $totalKm = $runsQuery->clone()
            ->where('status', 'completed')
            ->where(DB::raw('end_km - start_km'), '>=', 0)
            ->sum(DB::raw('end_km - start_km'));

        // --- DADOS PARA OS GRÁFICOS ---

        // Corridas por Veículo
        $runsByVehicleData = $runsQuery->clone()
            ->join('vehicles', 'runs.vehicle_id', '=', 'vehicles.id')
            ->select(DB::raw("CONCAT(vehicles.brand, ' ', vehicles.model, ' (', vehicles.plate, ')') as vehicle_name"), DB::raw('count(runs.id) as run_count'))
            ->groupBy('vehicle_name')
            ->orderBy('run_count', 'desc')
            ->take(10)
            ->get();

        // Gastos com combustível por mês
        $monthlyFuelData = $fuelingsQuery->clone()
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('SUM(total_value) as total_value'))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return view('dashboard', [
            'totalRuns' => $totalRuns,
            'totalVehiclesInUse' => $totalVehiclesInUse, // <-- NOME DA VARIÁVEL CORRIGIDO
            'totalFuelCost' => number_format($totalFuelCost, 2, ',', '.'),
            'totalKm' => number_format($totalKm, 0, ',', '.'),
            'runsByVehicleData' => json_encode($runsByVehicleData),
            'monthlyFuelData' => json_encode($monthlyFuelData),
        ]);
    }
}
