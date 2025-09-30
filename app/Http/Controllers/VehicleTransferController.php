<?php

namespace App\Http\Controllers;

use App\Models\Secretariat;
use App\Models\Vehicle;
use App\Models\VehicleTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class VehicleTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $user = Auth::user();
        $query = VehicleTransfer::with(['vehicle', 'requester', 'originSecretariat', 'destinationSecretariat', 'approver']);

        // Gestor Geral (role_id 1) pode ver tudo
        if ($user->role_id == 2) { // Gestor Setorial
            $query->where('origin_secretariat_id', $user->secretariat_id);
        } elseif ($user->role_id > 2) { // Outros usuários (motoristas, etc)
            $query->where('requester_id', $user->id);
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('vehicles.transfers.index', compact('transfers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $secretariats = Secretariat::orderBy('name')->get();
        return view('vehicles.transfers.create', compact('secretariats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'destination_secretariat_id' => 'required|exists:secretariats,id',
            'transfer_type' => 'required|in:permanent,temporary',
            'start_date' => 'nullable|required_if:transfer_type,temporary|date|after_or_equal:today',
            'end_date' => 'nullable|required_if:transfer_type,temporary|date|after_or_equal:start_date',
            'request_notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        if ($vehicle->current_secretariat_id == $validated['destination_secretariat_id']) {
            return back()->withErrors(['destination_secretariat_id' => 'A secretaria de destino não pode ser a mesma de origem.'])->withInput();
        }

        // <-- LÓGICA DE APROVAÇÃO AUTOMÁTICA -->
        $isGeneralManager = $user->role_id == 1;

        $status = $isGeneralManager ? 'approved' : 'pending';
        $approverId = $isGeneralManager ? $user->id : null;
        $approvalNotes = $isGeneralManager ? 'Aprovado automaticamente pelo Gestor Geral solicitante.' : null;

        VehicleTransfer::create([
            'vehicle_id' => $vehicle->id,
            'requester_id' => $user->id,
            'origin_secretariat_id' => $vehicle->current_secretariat_id,
            'destination_secretariat_id' => $validated['destination_secretariat_id'],
            'transfer_type' => $validated['transfer_type'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'request_notes' => $validated['request_notes'],
            'status' => $status,
            'approver_id' => $approverId,
            'approval_notes' => $approvalNotes,
        ]);

        // Se for Gestor Geral e a transferência permanente, atualiza o veículo imediatamente
        if ($isGeneralManager && $validated['transfer_type'] === 'permanent') {
            $vehicle->current_secretariat_id = $validated['destination_secretariat_id'];
            $vehicle->save();
        }

        return redirect()->route('vehicles.transfers.index')->with('success', 'Solicitação de transferência enviada com sucesso.');
    }

    /**
     * Aprova uma solicitação de transferência.
     */
    /**
     * Aprova uma solicitação de transferência.
     */
    public function approve(VehicleTransfer $transfer): RedirectResponse
    {
        // <-- LÓGICA DE AUTORIZAÇÃO ATUALIZADA -->
        // Usando o Gate para autorização. Mais limpo e centralizado.
        Gate::authorize('manage-transfer', $transfer);

        $transfer->status = 'approved';
        $transfer->approver_id = Auth::id();

        if ($transfer->transfer_type === 'permanent') {
            $vehicle = $transfer->vehicle;
            $vehicle->current_secretariat_id = $transfer->destination_secretariat_id;
            $vehicle->save();
        }

        $transfer->save();

        return redirect()->route('vehicles.transfers.index')->with('success', 'Transferência aprovada com sucesso.');
    }

    /**
     * Rejeita uma solicitação de transferência.
     */
    public function reject(Request $request, VehicleTransfer $transfer): RedirectResponse
    {
        // <-- LÓGICA DE AUTORIZAÇÃO ATUALIZADA -->
        // Usando o mesmo Gate para autorizar a rejeição.
        Gate::authorize('manage-transfer', $transfer);

        $request->validate(['approval_notes' => 'required|string|max:500']);

        $transfer->status = 'rejected';
        $transfer->approver_id = Auth::id();
        $transfer->approval_notes = $request->input('approval_notes');
        $transfer->save();

        return redirect()->route('vehicles.transfers.index')->with('success', 'Transferência rejeitada.');
    }


    /**
     * Search for a vehicle by plate or prefix for autocomplete.
     */
    public function searchVehicle(Request $request): JsonResponse
    {
        $identifier = $request->input('identifier');

        if (empty($identifier) || strlen($identifier) < 2) {
            return response()->json([]);
        }

        $vehicles = Vehicle::with('currentSecretariat')
            ->where(function ($query) use ($identifier) {
                $query->where('plate', 'LIKE', "%{$identifier}%")
                    ->orWhere('prefix', 'LIKE', "%{$identifier}%");
            })
            ->limit(10)
            ->get();

        $results = $vehicles->map(function ($vehicle) {
            return [
                'id' => $vehicle->id,
                'plate' => $vehicle->plate,
                'prefix' => $vehicle->prefix,
                'name' => $vehicle->brand . ' ' . $vehicle->model,
                'secretariat' => $vehicle->currentSecretariat?->name ?? 'Não definida',
            ];
        });

        return response()->json($results);
    }

    /**
     * Exibe o histórico completo de transferências.
     */
    public function history(Request $request): View
    {
        $query = VehicleTransfer::with([
            'vehicle',
            'requester',
            'originSecretariat',
            'destinationSecretariat',
            'approver'
        ]);

        // Filtros
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('type') && $request->type != '') {
            $query->where('transfer_type', $request->type);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Ordenação: mais recentes primeiro
        $transfers = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('vehicles.transfers.history', compact('transfers'));
    }
}
