<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OilProduct;
use App\Models\Vehicle;

class StoreOilChangeLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => ['required','exists:vehicles,id'],
            'oil_product_id' => ['nullable','exists:oil_products,id'],
            'change_date' => ['required','date'],
            'odometer_km' => ['required','integer','min:0'],
            'quantity_used' => ['required','numeric','min:0.1'],
            'interval_km_used' => ['nullable','integer','min:100'],
            'interval_days_used' => ['nullable','integer','min:1'],
            'notes' => ['nullable','string','max:2000']
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function($v){
            $productId = $this->input('oil_product_id');
            if ($productId) {
                $product = OilProduct::find($productId);
                if ($product && ceil($this->quantity_used) > $product->stock_quantity) {
                    $v->errors()->add('quantity_used', 'Quantidade solicitada maior que o estoque disponível.');
                }
            }
            $vehicle = Vehicle::find($this->input('vehicle_id'));
            if ($vehicle) {
                $odo = (int)$this->input('odometer_km');
                if ($odo < $vehicle->current_km) {
                    $v->errors()->add('odometer_km','Odômetro inferior ao registrado no veículo ('.number_format($vehicle->current_km,0,'','.').').');
                }
                if ($odo > $vehicle->current_km + 200000) { // limite arbitrário de segurança
                    $v->errors()->add('odometer_km','Valor de odômetro muito acima do atual para este veículo.');
                }
            }
            $ikm = (int)$this->input('interval_km_used');
            if ($ikm && $ikm > 50000) {
                $v->errors()->add('interval_km_used','Intervalo em KM excede o limite (50.000).');
            }
            $idays = (int)$this->input('interval_days_used');
            if ($idays && $idays > 1095) { // 3 anos
                $v->errors()->add('interval_days_used','Intervalo em dias excede o limite (1095).');
            }
        });
    }
}
