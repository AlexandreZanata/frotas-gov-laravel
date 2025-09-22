<?php
namespace App\Http\Controllers;

use App\Http\Requests\{TireStoreRequest, TireUpdateRequest};
use App\Models\{Tire, Vehicle};
use App\Services\TireMaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class TireController extends Controller
{
    public function dashboard(Request $request)
    {
        $this->authorize('viewAny', Tire::class);
        $service = app(TireMaintenanceService::class);
        $stats = $service->dashboardStats();
        $search = $request->get('q');
        $vehiclesQuery = Vehicle::query()->with(['category','tires']);
        if ($search) {
            $vehiclesQuery->where(function($q) use ($search){
                $q->where('plate','like',"%$search%")
                  ->orWhere('prefix','like',"%$search%")
                  ->orWhere('model','like',"%$search%")
                  ->orWhere('brand','like',"%$search%");
            });
        }
        $vehicles = $vehiclesQuery->orderBy('plate')->paginate(12)->withQueryString();
        return view('tires.dashboard', compact('stats','vehicles','search'));
    }

    public function attention(Request $request)
    {
        $this->authorize('viewAny', Tire::class);
        $tires = Tire::query()->whereIn('status',['attention','critical'])->orderByDesc('updated_at')->paginate(20);
        return view('tires.attention', compact('tires'));
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Tire::class);
        $query = Tire::query();
        $search = $request->get('q');
        if ($search) {
            $query->where(function($q) use ($search) {
                foreach (['serial_number','brand','model','dimension','status'] as $col) {
                    if (Schema::hasColumn('tires',$col)) {
                        $q->orWhere($col,'like',"%$search%");
                    }
                }
            });
        }
        $orderColumn = Schema::hasColumn('tires','serial_number') ? 'serial_number' : 'id';
        $tires = $query->orderBy($orderColumn)->paginate(20)->withQueryString();
        return view('tires.index', compact('tires','search'));
    }

    public function create()
    {
        $this->authorize('create', Tire::class);
        return view('tires.create');
    }

    public function store(TireStoreRequest $request)
    {
        $this->authorize('create', Tire::class);
        $data = $this->filterExistingColumns($request->validated());
        if (empty($data['current_tread_depth_mm']) && !empty($data['initial_tread_depth_mm'])) {
            $data['current_tread_depth_mm'] = $data['initial_tread_depth_mm'];
        }
        $tire = Tire::create($data);
        Cache::forget('tires.dashboard.stats');
        return redirect()->route('tires.index')->with('success','Pneu cadastrado com sucesso.');
    }

    public function edit(Tire $tire)
    {
        $this->authorize('update', $tire);
        return view('tires.edit', compact('tire'));
    }

    public function update(TireUpdateRequest $request, Tire $tire)
    {
        $this->authorize('update', $tire);
        $data = $this->filterExistingColumns($request->validated());
        if (isset($data['initial_tread_depth_mm']) && isset($data['current_tread_depth_mm']) && $data['current_tread_depth_mm'] > $data['initial_tread_depth_mm']) {
            $data['current_tread_depth_mm'] = $data['initial_tread_depth_mm'];
        }
        $tire->update($data);
        Cache::forget('tires.dashboard.stats');
        return redirect()->route('tires.index')->with('success','Pneu atualizado com sucesso.');
    }

    public function destroy(Tire $tire)
    {
        $this->authorize('delete', $tire);
        $tire->delete();
        Cache::forget('tires.dashboard.stats');
        return redirect()->route('tires.index')->with('success','Pneu excluído.');
    }

    // --- Recapagem ---
    public function sendForRetread(Request $request, Tire $tire)
    {
        $this->authorize('action', $tire);
        $service = app(TireMaintenanceService::class);
        $notes = $request->input('notes');
        $result = $service->sendForRetread($tire, $notes);
        return back()->with($result['success'] ? 'success':'error', $result['message']);
    }

    public function receiveFromRetread(Request $request, Tire $tire)
    {
        $this->authorize('action', $tire);
        $validated = $request->validate([
            'new_tread_depth_mm' => 'nullable|numeric|min:0',
            'expected_tread_life_km' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500'
        ]);
        $service = app(TireMaintenanceService::class);
        $result = $service->receiveFromRetread(
            $tire,
            $validated['new_tread_depth_mm'] ?? null,
            $validated['expected_tread_life_km'] ?? null,
            $validated['notes'] ?? null
        );
        return back()->with($result['success'] ? 'success':'error', $result['message']);
    }

    public function searchStock(\Illuminate\Http\Request $request)
    {
        $this->authorize('viewAny', \App\Models\Tire::class);
        $q = trim($request->get('q',''));
        $limit = min(30, (int)$request->get('limit', 15));
        $query = \App\Models\Tire::query()->whereNull('current_vehicle_id')->where('status','stock');
        if ($q !== '') {
            $query->where(function($sub) use ($q) {
                // Tokeniza por espaços para permitir múltiplos termos
                $terms = preg_split('/\s+/', $q);
                foreach ($terms as $term) {
                    $t = "%".$term."%";
                    $sub->where(function($w) use ($t, $term) {
                        $w->where('serial_number','like',$t)
                          ->orWhere('brand','like',$t)
                          ->orWhere('model','like',$t)
                          ->orWhere('dimension','like',$t)
                          ->orWhere('notes','like',$t);
                        if (is_numeric($term)) {
                            $w->orWhere('id',(int)$term);
                            $w->orWhere('expected_tread_life_km',(int)$term);
                        }
                    });
                }
            });
        }
        $tires = $query->orderByDesc('updated_at')->limit($limit)->get([
            'id','serial_number','brand','model','dimension','current_tread_depth_mm','expected_tread_life_km','status'
        ]);
        return response()->json([
            'data' => $tires,
        ]);
    }

    private function filterExistingColumns(array $data): array
    {
        $table = (new Tire)->getTable();
        return collect($data)->filter(fn($v,$k)=>Schema::hasColumn($table,$k))->all();
    }
}
