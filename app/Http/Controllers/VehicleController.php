<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\Secretariat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     * Exibe uma lista paginada de todos os veículos.
     */
    public function index()
    {
        // Busca os veículos com seus relacionamentos para evitar N+1 queries
        $vehicles = Vehicle::with(['category', 'secretariat'])->latest()->paginate(15);

        // Retorna a view de listagem (você precisará criar o arquivo /resources/views/vehicles/index.blade.php)
        // return view('vehicles.index', compact('vehicles'));

        // Por enquanto, vamos redirecionar para o dashboard para não dar erro
        return redirect()->route('dashboard')->with('info', 'Página de listagem de veículos em construção.');
    }

    /**
     * Show the form for creating a new resource.
     * Exibe o formulário de criação de veículo.
     */
    public function create()
    {
        $categories = VehicleCategory::orderBy('name')->get();
        $secretariats = Secretariat::orderBy('name')->get();

        return view('vehicles.create', compact('categories', 'secretariats'));
    }

    /**
     * Store a newly created resource in storage.
     * Salva o novo veículo no banco de dados.
     */
    public function store(Request $request)
    {
        // Validação ATUALIZADA
        $validatedData = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|digits:4',
            'plate' => 'required|string|max:10|unique:vehicles,plate',
            'renavam' => 'required|string|max:11|unique:vehicles,renavam',
            'current_km' => 'required|integer|min:0',
            'fuel_type' => 'required|string|max:50',
            'tank_capacity' => 'required|numeric|min:0',
            'category_id' => 'required|exists:vehicle_categories,id',
            'current_secretariat_id' => 'required|exists:secretariats,id',
            'current_department_id' => 'nullable|exists:departments,id',
            'status' => 'required|string|in:Disponível,Em uso,Manutenção,Inativo',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

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
        $categories = VehicleCategory::orderBy('name')->get();
        $secretariats = Secretariat::orderBy('name')->get();

        // Retorna a view de edição (você precisará criar o arquivo /resources/views/vehicles/edit.blade.php)
        // return view('vehicles.edit', compact('vehicle', 'categories', 'secretariats'));
        return redirect()->route('dashboard')->with('info', 'Página de edição de veículo em construção.');
    }

    /**
     * Update the specified resource in storage.
     * Atualiza os dados de um veículo no banco de dados.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        // Validação ATUALIZADA
        $validatedData = $request->validate([
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|digits:4',
            'plate' => ['required', 'string', 'max:10', Rule::unique('vehicles')->ignore($vehicle->id)],
            'renavam' => ['required', 'string', 'max:11', Rule::unique('vehicles')->ignore($vehicle->id)],
            'current_km' => 'required|integer|min:0',
            'fuel_type' => 'required|string|max:50',
            'tank_capacity' => 'required|numeric|min:0',
            'category_id' => 'required|exists:vehicle_categories,id',
            'current_secretariat_id' => 'required|exists:secretariats,id',
            'current_department_id' => 'nullable|exists:departments,id',
            'status' => 'required|string|in:Disponível,Em uso,Manutenção,Inativo',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ]);

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
