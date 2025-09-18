<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\Secretariat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\VehicleStatus;


class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Busca os veículos com seus relacionamentos
        $vehicles = Vehicle::with(['category', 'secretariat'])->latest()->paginate(15);

        // Retorna a view de listagem
        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new resource.
     * Exibe o formulário de criação de veículo.
     */
    public function create()
    {
        $categories = VehicleCategory::orderBy('name')->get();
        $secretariats = Secretariat::orderBy('name')->get();
        // Adicione departments se necessário no formulário
        // $departments = Department::orderBy('name')->get();

        return view('vehicles.create', compact('categories', 'secretariats'));
    }


    /**
     * Store a newly created resource in storage.
     * Salva o novo veículo no banco de dados.
     */
    public function store(VehicleRequest $request) // **Use o VehicleRequest aqui**
    {
        $validatedData = $request->validated();

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('vehicle_documents', 'public');
            $validatedData['document_path'] = $path;
        }

        Vehicle::create($validatedData);

        return redirect()->route('dashboard')->with('success', 'Veículo cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     * Exibe os detalhes de um veículo específico.
     */
    public function show(Vehicle $vehicle)
    {
        // Carrega os relacionamentos para exibição
        $vehicle->load(['category', 'secretariat', 'department']);

        // Retorna a view de detalhes (você precisará criar o arquivo /resources/views/vehicles/show.blade.php)
        // return view('vehicles.show', compact('vehicle'));
        return redirect()->route('dashboard')->with('info', 'Página de detalhes do veículo em construção.');
    }

    /**
     * Show the form for editing the specified resource.
     * Exibe o formulário de edição de um veículo.
     */
    public function edit(Vehicle $vehicle)
    {
        // 2. BUSCA OS DADOS DE TODAS AS TABELAS RELACIONADAS
        $statuses = VehicleStatus::orderBy('name')->get(); // <-- ESTA É A LINHA QUE FALTAVA
        $categories = VehicleCategory::orderBy('name')->get();
        $secretariats = Secretariat::orderBy('name')->get();

        // 3. AGORA, TODAS AS VARIÁVEIS EXISTEM E PODEM SER PASSADAS PARA A VIEW
        return view('vehicles.edit', compact(
            'vehicle',
            'statuses',
            'categories',
            'secretariats'
        ));
    }

    /**
     * Update the specified resource in storage.
     * Atualiza os dados de um veículo no banco de dados.
     */
    public function update(VehicleRequest $request, Vehicle $vehicle) // **Use o VehicleRequest aqui também**
    {
        $validatedData = $request->validated();

        if ($request->hasFile('document')) {
            // Se já existe um documento, apaga o antigo antes de salvar o novo
            if ($vehicle->document_path) {
                Storage::disk('public')->delete($vehicle->document_path);
            }
            $path = $request->file('document')->store('vehicle_documents', 'public');
            $validatedData['document_path'] = $path;
        }

        $vehicle->update($validatedData);

        return redirect()->route('dashboard')->with('success', 'Veículo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     * Exclui um veículophp do banco de dados.
     */
    public function destroy(Vehicle $vehicle)
    {
        // Apaga o documento associado do armazenamento
        if ($vehicle->document_path) {
            Storage::disk('public')->delete($vehicle->document_path);
        }

        $vehicle->delete();

        return redirect()->route('dashboard')->with('success', 'Veículo excluído com sucesso!');
    }
}
