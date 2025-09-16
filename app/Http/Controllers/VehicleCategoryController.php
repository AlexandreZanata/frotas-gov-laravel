<?php

namespace App\Http\Controllers;

use App\Models\VehicleCategory;
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
        return view('vehicle-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:vehicle_categories,name',
            'layout_key' => 'nullable|string|max:50',
            'oil_change_km' => 'required|integer|min:0',
            'oil_change_days' => 'required|integer|min:0',
        ]);

        VehicleCategory::create($validated);

        return redirect()->route('vehicle-categories.index')->with('success', 'Categoria cadastrada com sucesso!');
    }

    public function edit(VehicleCategory $vehicleCategory)
    {
        return view('vehicle-categories.edit', ['category' => $vehicleCategory]);
    }

    public function update(Request $request, VehicleCategory $vehicleCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:vehicle_categories,name,' . $vehicleCategory->id,
            'layout_key' => 'nullable|string|max:50',
            'oil_change_km' => 'required|integer|min:0',
            'oil_change_days' => 'required|integer|min:0',
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
