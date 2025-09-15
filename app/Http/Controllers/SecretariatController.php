<?php

namespace App\Http\Controllers;

use App\Models\Secretariat; // <-- Adicione esta linha
use Illuminate\Http\Request;
use Illuminate\View\View; // <-- Adicione esta linha

class SecretariatController extends Controller
{
    // Adicione este método inteiro
    public function index(): View
    {
        // Busca todas as secretarias do banco, ordenadas por nome
        $secretariats = Secretariat::orderBy('name')->get();

        // Retorna a view (a tela) e passa a variável 'secretariats' para ela
        return view('secretariats.index', [
            'secretariats' => $secretariats,
        ]);
    }
}
