<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;

class OilProductUpdateRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }
    public function rules(): array
    {
        $id = $this->route('oil_product')?->id ?? $this->route('oilProduct')?->id ?? null;
        $rules = [
            'brand' => ['nullable','string','max:100'],
            'viscosity' => ['nullable','string','max:50'],
            'stock_quantity' => ['required','integer','min:0'],
            'reorder_level' => ['required','integer','min:0','lte:stock_quantity'],
            'unit_cost' => ['required','numeric','min:0'],
            'recommended_interval_km' => ['required','integer','min:100'],
            'recommended_interval_days' => ['required','integer','min:1'],
            'description' => ['nullable','string','max:2000'],
        ];
        if (Schema::hasTable('oil_products')) {
            if (Schema::hasColumn('oil_products','name')) {
                $rules['name'] = ['required','string','max:255'];
            }
            if (Schema::hasColumn('oil_products','code')) {
                $rules['code'] = ['required','string','max:50','unique:oil_products,code,' . $id];
            }
        }
        return $rules;
    }
}
