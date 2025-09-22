<?php

namespace App\Http\Controllers;

use App\Models\VehicleCategory;
use App\Models\TireLayout; // adicionado
use Illuminate\Http\Request;

class VehicleCategoryController extends Controller
{
    public function index()
    {
        $categories = VehicleCategory::latest()->paginate(10);
        return view('vehicle-categories.index', compact('categories'));
    }

    public function create()
    {
        $tireLayouts = TireLayout::orderBy('name')->get();
        return view('vehicle-categories.create', compact('tireLayouts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:vehicle_categories,name',
            'layout_key' => 'nullable|string|max:50',
            'oil_change_km' => 'required|integer|min:0',
            'oil_change_days' => 'required|integer|min:0',
            'tire_change_km' => 'nullable|integer|min:0',
            'tire_change_days' => 'nullable|integer|min:0',
            'tire_layout_id' => 'nullable|exists:tire_layouts,id',
        ]);

        VehicleCategory::create($validated);

        return redirect()->route('vehicle-categories.index')->with('success', 'Categoria cadastrada com sucesso!');
    }

    public function edit(VehicleCategory $vehicleCategory)
    {
        $tireLayouts = TireLayout::orderBy('name')->get();
        return view('vehicle-categories.edit', ['category' => $vehicleCategory,'tireLayouts'=>$tireLayouts]);
    }

    public function update(Request $request, VehicleCategory $vehicleCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:vehicle_categories,name,' . $vehicleCategory->id,
            'layout_key' => 'nullable|string|max:50',
            'oil_change_km' => 'required|integer|min:0',
            'oil_change_days' => 'required|integer|min:0',
            'tire_change_km' => 'nullable|integer|min:0',
            'tire_change_days' => 'nullable|integer|min:0',
            'tire_layout_id' => 'nullable|exists:tire_layouts,id',
        ]);

        $vehicleCategory->update($validated);

        return redirect()->route('vehicle-categories.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(VehicleCategory $vehicleCategory)
    {
        $vehicleCategory->delete();
        return redirect()->route('vehicle-categories.index')->with('success', 'Categoria excluída com sucesso!');
    }
}
