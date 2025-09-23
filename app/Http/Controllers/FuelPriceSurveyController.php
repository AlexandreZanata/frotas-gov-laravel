<?php

namespace App\Http\Controllers;

use App\Models\{FuelPriceSurvey, FuelPriceSurveyStationPrice, GasStation, AuditLog};
use Illuminate\Http\Request;
use App\Http\Requests\StoreFuelPriceSurveyRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class FuelPriceSurveyController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('fuel_price_surveys')) {
            return view('fuel_survey.missing');
        }
        $surveys = FuelPriceSurvey::orderByDesc('survey_date')->paginate(15);
        return view('fuel_survey.index', compact('surveys'));
    }

    public function create()
    {
        $stations = GasStation::where('status','active')->orderBy('name')->get();
        $fuelLabels = FuelPriceSurvey::fuelLabels();
        return view('fuel_survey.create', compact('stations','fuelLabels'));
    }

    public function store(StoreFuelPriceSurveyRequest $request)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $survey = FuelPriceSurvey::create(collect($data)->except('station_prices')->toArray());
            foreach ($data['station_prices'] as $idx => $row) {
                $spData = collect($row)->only([
                    'gas_station_id','include_in_average','include_in_comparison',
                    'diesel_s500_price','diesel_s10_price','gasoline_price','ethanol_price'
                ])->toArray();
                $spData['include_in_average'] = isset($row['include_in_average']) ? (bool)$row['include_in_average'] : false;
                $spData['include_in_comparison'] = isset($row['include_in_comparison']) ? (bool)$row['include_in_comparison'] : false;
                $stationPrice = $survey->stationPrices()->create($spData);
                // Upload de anexos
                foreach (['diesel_s500','diesel_s10','gasoline','ethanol'] as $fuel) {
                    $field = $fuel . '_attachment';
                    if ($request->hasFile("station_prices.$idx.$field")) {
                        $path = $request->file("station_prices.$idx.$field")->store('fuel-survey','public');
                        $stationPrice->update([$fuel . '_attachment_path' => $path]);
                    }
                }
            }
            DB::commit();
            return redirect()->route('fuel-surveys.show', $survey)->with('success','Cotação criada com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->withErrors('Erro ao salvar: '.$e->getMessage())->withInput();
        }
    }

    public function show(FuelPriceSurvey $fuelSurvey)
    {
        $fuelSurvey->load('stationPrices.station');
        $comparison = $fuelSurvey->produceComparison();
        $fuelLabels = FuelPriceSurvey::fuelLabels();
        return view('fuel_survey.show', [
            'survey'=>$fuelSurvey,
            'comparison'=>$comparison,
            'fuelLabels'=>$fuelLabels
        ]);
    }
}
