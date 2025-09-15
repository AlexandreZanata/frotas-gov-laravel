<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View; // Importe a classe View

class VehicleStatusController extends Controller
{
    // Adicione este método
    public function index(): View
    {
        // Por enquanto, vamos apenas retornar uma view simples.
        // A lógica de buscar os veículos do banco de dados virá depois.
        return view('vehicles.status');
    }
}
