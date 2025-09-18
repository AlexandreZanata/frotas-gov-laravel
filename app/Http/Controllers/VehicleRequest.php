<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Altere para false se precisar de lógica de autorização
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $vehicleId = $this->route('vehicle') ? $this->route('vehicle')->id : null;

        return [
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),
            'plate' => [
                'required',
                'string',
                'max:10',
                Rule::unique('vehicles')->ignore($vehicleId),
            ],
            'renavam' => [
                'required',
                'digits:11',
                Rule::unique('vehicles')->ignore($vehicleId),
            ],
            'prefix' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('vehicles')->ignore($vehicleId),
            ],
            'current_km' => 'required|integer',
            'fuel_type' => 'required|string|max:50',
            'tank_capacity' => 'required|numeric',
            'avg_km_per_liter' => 'nullable|numeric',
            'category_id' => 'nullable|exists:vehicle_categories,id',
            'current_secretariat_id' => 'required|exists:secretariats,id',
            'current_department_id' => 'nullable|exists:departments,id',
            'status' => 'required|in:Disponível,Em uso,Manutenção,Inativo',
        ];
    }
}
