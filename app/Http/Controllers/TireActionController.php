<?php
namespace App\Http\Controllers;

use App\Models\{Vehicle, Tire};
use App\Services\TireMaintenanceService;
use App\Http\Requests\{TireInternalRotationRequest, TireExternalOutRequest, TireExternalInRequest, TireReplacementRequest};

class TireActionController extends Controller
{
    protected TireMaintenanceService $service;

    public function __construct()
    {
        $this->service = app(TireMaintenanceService::class);
    }

    public function layout(Vehicle $vehicle)
    {
        $this->authorize('viewAny', Tire::class);
        $layout = $this->service->vehicleLayout($vehicle); // ['positions'=>[], 'tires'=>[]]
        return view('tires.vehicle-layout', [
            'vehicle'=>$vehicle,
            'positions'=>$layout['positions'],
            'tiresMap'=>$layout['tires'],
        ]);
    }

    public function internalRotation(TireInternalRotationRequest $request, Vehicle $vehicle)
    {
        $this->authorize('viewAny', Tire::class);
        $data = $request->validated();
        $result = $this->service->internalRotation($vehicle, $data['pos_a'], $data['pos_b']);
        return back()->with($result['success'] ? 'success':'error', $result['message']);
    }

    public function externalOut(TireExternalOutRequest $request, Vehicle $vehicle)
    {
        $this->authorize('viewAny', Tire::class);
        $result = $this->service->externalOut($vehicle, $request->validated()['position']);
        return back()->with($result['success'] ? 'success':'error', $result['message']);
    }

    public function externalIn(TireExternalInRequest $request, Vehicle $vehicle)
    {
        $this->authorize('viewAny', Tire::class);
        $data = $request->validated();
        $tire = Tire::findOrFail($data['tire_id']);
        $result = $this->service->externalIn($vehicle, $tire, $data['position']);
        return back()->with($result['success'] ? 'success':'error', $result['message']);
    }

    public function replacement(TireReplacementRequest $request, Vehicle $vehicle)
    {
        $this->authorize('viewAny', Tire::class);
        $map = $request->validated()['replacements'];
        $this->service->replacement($vehicle, $map);
        return back()->with('success','Substituição processada.');
    }
}
