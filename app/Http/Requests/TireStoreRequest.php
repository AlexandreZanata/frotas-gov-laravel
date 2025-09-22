<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;

class TireStoreRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        $rules = [
            'serial_number' => ['required','string','max:100','unique:tires,serial_number'],
            'brand' => ['nullable','string','max:100'],
            'model' => ['nullable','string','max:100'],
            'dimension' => ['nullable','string','max:50'],
            'purchase_date' => ['nullable','date'],
            'initial_tread_depth_mm' => ['nullable','numeric','min:0','max:999.99'],
            'current_tread_depth_mm' => ['nullable','numeric','min:0','max:999.99'],
            'expected_tread_life_km' => ['nullable','integer','min:100','max:1000000'],
            'notes' => ['nullable','string','max:2000'],
        ];
        if (!Schema::hasTable('tires')) return [];
        return $rules;
    }

    protected function prepareForValidation(): void
    {
        foreach (['initial_tread_depth_mm','current_tread_depth_mm'] as $f) {
            if ($this->has($f) && $this->$f !== null) {
                $v = (float) $this->$f; if ($v > 999.99) $v = 999.99; if ($v < 0) $v = 0; $this->merge([$f=>$v]);
            }
        }
        if ($this->has('expected_tread_life_km') && $this->expected_tread_life_km !== null) {
            $km = (int)$this->expected_tread_life_km; if ($km > 1000000) $km = 1000000; if ($km < 100) $km = 100; $this->merge(['expected_tread_life_km'=>$km]);
        }
    }
}
