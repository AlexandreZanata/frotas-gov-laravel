<?php

namespace App\Http\Controllers;

use App\Models\Run;
use App\Models\Vehicle;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DiarioBordoController extends Controller
{
    /**
     * Ponto de entrada do Diário de Bordo. Redireciona para a etapa correta.
     */
    public function index()
    {
        $activeRun = Run::where('driver_id', Auth::id())
            ->whereIn('status', ['in_progress', 'pending_start'])
            ->first();

        if ($activeRun) {
            if ($activeRun->status === 'pending_start') {
                return redirect()->route('diario.showStartRunForm', $activeRun);
            }
            return redirect()->route('diario.finishRun', $activeRun);
        }

        return redirect()->route('diario.selectVehicle');
    }

    /**
     * ETAPA 1: Exibe a página para selecionar o veículo.
     */
    public function showSelectVehicle()
    {
        $activeRun = Run::where('driver_id', Auth::id())->whereIn('status', ['in_progress', 'pending_start'])->first();
        if ($activeRun) {
            return redirect()->route('diario.index')->with('status', 'Você já tem uma corrida em andamento. Finalize-a para iniciar uma nova.');
        }
        return view('diario-de-bordo.select-vehicle');
    }

    /**
     * API: Busca veículos dinamicamente.
     */
    public function searchVehicles(Request $request)
    {
        $query = $request->input('query');
        if (!$query) {
            return response()->json([]);
        }
        $user = auth()->user();

        // 1. Busca ampla por texto, já carregando os relacionamentos de status e secretaria
        $vehicles = Vehicle::with(['status', 'secretariat'])
            ->where(function($q) use ($query) {
                $q->where('prefix', 'LIKE', "%{$query}%")
                    ->orWhere('plate', 'LIKE', "%{$query}%")
                    ->orWhere('model', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get();

        // 2. Itera sobre os resultados para adicionar a lógica de disponibilidade
        $results = $vehicles->map(function ($vehicle) use ($user) {
            $selectable = true;
            $reason = '';

            // --- INÍCIO DAS REGRAS DE VERIFICAÇÃO ---

            // Regra 1: O veículo precisa ter um status definido
            if (!$vehicle->status) {
                $selectable = false;
                $reason = 'Status não definido';
            }
            // Regra 2: O veículo precisa estar na mesma secretaria do motorista
            elseif ($vehicle->current_secretariat_id !== $user->secretariat_id) {
                $selectable = false;
                $reason = 'Veículo alocado em outra secretaria.';
            }
            // Regra 3: O status do veículo precisa ser 'Disponível'
            // Esta é a verificação que você pediu. Usamos o relacionamento para checar o 'slug'.
            // 'disponivel' corresponde ao ID 1 na sua tabela 'vehicle_statuses'.
            elseif ($vehicle->status->slug !== 'disponivel') {
                $selectable = false;
                $reason = 'Status atual: ' . $vehicle->status->name;
            }

            // --- FIM DAS REGRAS DE VERIFICAÇÃO ---

            // Adiciona os novos campos para o frontend usar
            $vehicle->selectable = $selectable;
            $vehicle->reason = $reason;

            return $vehicle;
        });

        return response()->json($results);
    }

    /**
     * ETAPA 2: Exibe a página de Checklist, mostrando o último checklist do veículo.
     */
    /**
     * ETAPA 2: Exibe a página de Checklist, mostrando o último checklist do veículo.
     */
    public function showChecklist(Vehicle $vehicle)
    {
        $activeRunInUse = Run::where('vehicle_id', $vehicle->id)
            ->where('status', 'in_progress')
            ->where('driver_id', '!=', Auth::id())
            ->with('driver')
            ->first();

        if ($activeRunInUse) {
            return view('diario-de-bordo.vehicle-in-use', compact('vehicle', 'activeRunInUse'));
        }

        $checklistItems = Cache::rememberForever('checklist_items', function () {
            return ChecklistItem::orderBy('id')->get();
        });

        $lastCompletedRun = Run::where('vehicle_id', $vehicle->id)
            ->where('status', 'completed')
            ->with(['checklist.answers' => function($query) {
                $query->with('item');
            }])
            ->latest('end_time')
            ->first();

        $lastAnswers = [];
        if ($lastCompletedRun && $lastCompletedRun->checklist) {
            foreach ($lastCompletedRun->checklist->answers as $answer) {
                $lastAnswers[$answer->item_id] = [
                    'status' => $answer->status,
                    'notes' => $answer->notes,
                ];
            }
        }

        return view('diario-de-bordo.checklist', compact('vehicle', 'checklistItems', 'lastAnswers'));
    }

    /**
     * ETAPA 2 -> 3: Salva o Checklist e Cria a Corrida em estado "pendente".
     */
    public function storeChecklistAndCreateRun(Request $request, Vehicle $vehicle)
    {
        $activeRun = Run::where('driver_id', Auth::id())->whereIn('status', ['in_progress', 'pending_start'])->first();
        if ($activeRun) {
            return redirect()->route('diario.index')->withErrors('Você já tem uma corrida em andamento ou pendente.');
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.status' => 'required|in:ok,attention,problem',
            'answers.*.notes' => 'nullable|string|max:255|required_if:answers.*.status,problem',
        ]);

        $run = DB::transaction(function () use ($validated, $vehicle) {
            $run = Run::create([
                'vehicle_id' => $vehicle->id,
                'driver_id' => Auth::id(),
                'status' => 'pending_start',
                'start_time' => Carbon::now(),
                'secretariat_id' => Auth::user()->secretariat_id ?? null,
            ]);

            $checklist = Checklist::create([
                'run_id' => $run->id,
                'user_id' => Auth::id(),
                'vehicle_id' => $vehicle->id,
            ]);

            $run->checklist_id = $checklist->id;
            $run->save();

            foreach ($validated['answers'] as $itemId => $answerData) {
                $checklist->answers()->create([
                    'item_id' => $itemId,
                    'status' => $answerData['status'],
                    'notes' => $answerData['notes'] ?? null,
                ]);
            }

            return $run;
        });

        return redirect()->route('diario.showStartRunForm', $run);
    }

    /**
     * ETAPA 3: Mostra o formulário para iniciar a corrida (KM, Destino).
     */
    public function showStartRunForm(Run $run)
    {
        if ($run->driver_id !== Auth::id() || $run->status !== 'pending_start') {
            abort(403, 'Acesso não autorizado ou corrida já iniciada.');
        }

        $vehicle = $run->vehicle;
        $lastRunKm = Run::where('vehicle_id', $vehicle->id)
            ->where('status', 'completed')
            ->latest('end_time')
            ->value('end_km');

        return view('diario-de-bordo.start-run', [
            'run' => $run,
            'vehicle' => $vehicle,
            'start_km' => $lastRunKm ?? $vehicle->current_km,
        ]);
    }

    /**
     * ETAPA 3 -> FIM: Atualiza a corrida com KM, Destino e a inicia de fato.
     */
    public function startRun(Request $request, Run $run)
    {
        if ($run->driver_id !== Auth::id() || $run->status !== 'pending_start') {
            abort(403);
        }

        $request->validate([
            'start_km' => 'required|integer|min:0|gte:' . ($run->vehicle->runs()->where('status', 'completed')->latest('end_time')->value('end_km') ?? 0),
            'destination' => 'required|string|max:255',
        ]);

        $run->update([
            'start_km' => $request->start_km,
            'destination' => $request->destination,
            'status' => 'in_progress',
        ]);

        return redirect()->route('diario.finishRun', $run)->with('status', 'Corrida iniciada! Boa viagem!');
    }

    /**
     * ETAPA 4: Mostra o formulário para finalizar uma corrida.
     */
    public function showFinishRun(Run $run)
    {
        if ($run->driver_id !== Auth::id() || $run->status !== 'in_progress') {
            abort(403, 'Acesso não autorizado ou a corrida não está em andamento.');
        }

        $gasStations = \App\Models\GasStation::orderBy('name')->get();
        $fuelTypes = \App\Models\FuelType::all();

        return view('diario-de-bordo.finish-run', compact('run', 'gasStations', 'fuelTypes'));
    }

    /**
     * Processa e salva a finalização da corrida e o abastecimento (se houver).
     */
    public function updateRun(Request $request, Run $run)
    {
        if ($run->driver_id !== Auth::id() || $run->status !== 'in_progress') {
            abort(403);
        }

        $validated = $request->validate([
            'end_km' => 'required|integer|gte:' . $run->start_km,
            'stop_point' => 'required|string|max:255',
            'fueling_added' => 'nullable|boolean',
        ]);

        if ($request->input('fueling_added')) {
            $request->validate([
                'fueling_km' => 'required|integer|gte:' . $run->start_km . '|lte:' . $request->end_km,
                'liters' => 'required|numeric|min:0.01',
                'fuel_type_id' => 'required|exists:fuel_types,id',
                'is_manual' => 'required|boolean',
                'gas_station_id' => 'required_if:is_manual,0|exists:gas_stations,id',
                'gas_station_name' => 'required_if:is_manual,1|string|max:150',
                'total_value' => 'required_if:is_manual,1|numeric|min:0.01',
                'invoice_path' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            ]);
        }

        DB::transaction(function () use ($request, $run) {
            $run->update([
                'end_km' => $request->end_km,
                'stop_point' => $request->stop_point,
                'end_time' => Carbon::now(),
                'status' => 'completed',
            ]);

            if ($request->input('fueling_added')) {
                $invoicePath = null;
                if ($request->hasFile('invoice_path')) {
                    $invoicePath = $request->file('invoice_path')->store('invoices', 'public');
                }

                $run->fuelings()->create([
                    'user_id' => Auth::id(),
                    'vehicle_id' => $run->vehicle_id,
                    'secretariat_id' => $run->secretariat_id,
                    'gas_station_id' => $request->is_manual == '0' ? $request->gas_station_id : null,
                    'fuel_type_id' => $request->fuel_type_id,
                    'km' => $request->fueling_km,
                    'liters' => $request->liters,
                    'is_manual' => $request->is_manual,
                    'gas_station_name' => $request->is_manual == '1' ? $request->gas_station_name : null,
                    'total_value' => $request->is_manual == '1' ? $request->total_value : null,
                    'invoice_path' => $invoicePath,
                ]);
            }
        });

        return redirect()->route('diario.index')->with('status', 'Corrida finalizada com sucesso!');
    }
}

