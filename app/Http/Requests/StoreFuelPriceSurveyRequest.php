<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFuelPriceSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() != null; // ajustar se precisar de permissão específica
    }

    public function rules(): array
    {
        return [
            'survey_date' => ['required','date'],
            'method' => ['required','in:simple,custom'],
            'discount_diesel_s500' => ['nullable','numeric','between:0,100'],
            'discount_diesel_s10' => ['nullable','numeric','between:0,100'],
            'discount_gasoline' => ['nullable','numeric','between:0,100'],
            'discount_ethanol' => ['nullable','numeric','between:0,100'],
            'custom_avg_diesel_s500' => ['nullable','numeric','between:0,999.999'],
            'custom_avg_diesel_s10' => ['nullable','numeric','between:0,999.999'],
            'custom_avg_gasoline' => ['nullable','numeric','between:0,999.999'],
            'custom_avg_ethanol' => ['nullable','numeric','between:0,999.999'],
            'station_prices' => ['required','array','min:1'],
            'station_prices.*.gas_station_id' => ['required','integer','distinct'],
            'station_prices.*.include_in_average' => ['nullable','boolean'],
            'station_prices.*.include_in_comparison' => ['nullable','boolean'],
            'station_prices.*.diesel_s500_price' => ['nullable','numeric','between:0,999.999'],
            'station_prices.*.diesel_s10_price' => ['nullable','numeric','between:0,999.999'],
            'station_prices.*.gasoline_price' => ['nullable','numeric','between:0,999.999'],
            'station_prices.*.ethanol_price' => ['nullable','numeric','between:0,999.999'],
            'station_prices.*.diesel_s500_attachment' => ['nullable','file','image','max:4096'],
            'station_prices.*.diesel_s10_attachment' => ['nullable','file','image','max:4096'],
            'station_prices.*.gasoline_attachment' => ['nullable','file','image','max:4096'],
            'station_prices.*.ethanol_attachment' => ['nullable','file','image','max:4096'],
        ];
    }
}

