<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleStatusController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query();

        // Funcionalidade de busca (CORRIGIDA)
        if ($request->filled('search')) { // Usar filled() é um pouco mais seguro
            $searchTerm = $request->input('search');

            // Agrupa as condições de busca para evitar conflitos com outros WHERES
            $query->where(function ($q) use ($searchTerm) {
                $q->where('brand', 'like', "%{$searchTerm}%")
                    ->orWhere('model', 'like', "%{$searchTerm}%")
                    ->orWhere('plate', 'like', "%{$searchTerm}%")
                    ->orWhere('renavam', 'like', "%{$searchTerm}%")
                    ->orWhere('prefix', 'like', "%{$searchTerm}%")
                    ->orWhere('chassi', 'like', "%{$searchTerm}%"); // <-- CHASSI ADICIONADO AQUI
            });
        }

        $vehicles = $query->paginate(10);

        return view('vehicles.status', compact('vehicles'));
    }
}
