<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FuelPriceSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_date','method',
        'discount_diesel_s500','discount_diesel_s10','discount_gasoline','discount_ethanol',
        'custom_avg_diesel_s500','custom_avg_diesel_s10','custom_avg_gasoline','custom_avg_ethanol'
    ];

    protected $casts = [
        'survey_date' => 'date'
    ];

    public function stationPrices()
    {
        return $this->hasMany(FuelPriceSurveyStationPrice::class);
    }

    public static function fuelKeys(): array
    {
        return ['diesel_s500','diesel_s10','gasoline','ethanol'];
    }

    public static function fuelLabels(): array
    {
        return [
            'diesel_s500' => 'Diesel S500',
            'diesel_s10' => 'Diesel S10',
            'gasoline' => 'Gasolina',
            'ethanol' => 'Etanol'
        ];
    }

    public function computeSimpleAverage(string $fuel): ?float
    {
        $column = $this->fuelColumn($fuel);
        if (!$column) { return null; }
        $values = $this->stationPrices->where('include_in_average', true)
            ->pluck($column)
            ->filter(fn($v)=>!is_null($v));
        if ($values->count() === 0) { return null; }
        return round($values->avg(), 3);
    }

    public function getAverage(string $fuel): ?float
    {
        if ($this->method === 'custom') {
            $custom = $this->getAttribute('custom_avg_' . $fuel);
            if (!is_null($custom)) { return (float)$custom; }
        }
        return $this->computeSimpleAverage($fuel);
    }

    public function getDiscountedAverage(string $fuel): ?float
    {
        $avg = $this->getAverage($fuel);
        if (is_null($avg)) { return null; }
        $discount = $this->getAttribute('discount_' . $fuel);
        if (is_null($discount)) { return $avg; }
        return round($avg * (1 - ($discount/100)), 3);
    }

    public function produceComparison(): array
    {
        $data = [];
        foreach (self::fuelKeys() as $fuel) {
            $discounted = $this->getDiscountedAverage($fuel);
            $rows = [];
            foreach ($this->stationPrices->where('include_in_comparison', true) as $sp) {
                $col = $this->fuelColumn($fuel);
                $price = $sp->{$col};
                if (is_null($price) || is_null($discounted)) {
                    $status = null; $favorable = null;
                } else {
                    // Favorável se preço com desconto <= preço de bomba
                    $favorable = $discounted <= $price;
                    $status = $favorable ? 'favoravel' : 'desfavoravel';
                }
                $rows[] = [
                    'station' => $sp->station?->name,
                    'price' => $price,
                    'status' => $status,
                    'favorable' => $favorable
                ];
            }
            $data[$fuel] = [
                'discounted_average' => $discounted,
                'rows' => $rows
            ];
        }
        return $data;
    }

    private function fuelColumn(string $fuel): ?string
    {
        return match($fuel) {
            'diesel_s500' => 'diesel_s500_price',
            'diesel_s10' => 'diesel_s10_price',
            'gasoline' => 'gasoline_price',
            'ethanol' => 'ethanol_price',
            default => null
        };
    }
}
