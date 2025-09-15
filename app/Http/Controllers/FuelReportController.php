<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class FuelReportController extends Controller
{
    public function index(): View
    {
        // Futuramente, aqui buscaremos os dados do banco para os gráficos.
        // Por enquanto, apenas carregamos a view.
        return view('reports.fuel-analysis');
    }
}
