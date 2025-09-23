<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuelPriceSurveyStationPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'fuel_price_survey_id','gas_station_id','include_in_average','include_in_comparison',
        'diesel_s500_price','diesel_s10_price','gasoline_price','ethanol_price',
        'diesel_s500_attachment_path','diesel_s10_attachment_path','gasoline_attachment_path','ethanol_attachment_path'
    ];

    protected $casts = [
        'include_in_average'=>'boolean',
        'include_in_comparison'=>'boolean'
    ];

    public function survey()
    {
        return $this->belongsTo(FuelPriceSurvey::class,'fuel_price_survey_id');
    }

    public function station()
    {
        return $this->belongsTo(GasStation::class,'gas_station_id');
    }
}

