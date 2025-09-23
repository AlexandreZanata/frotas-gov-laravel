<?php
namespace App\Http\Controllers;

use App\Models\{Fine, FineInfraction, FineInfractionAttachment, Vehicle, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\{FineStoreRequest, FineUpdateRequest, FineInfractionStoreRequest, FineInfractionUpdateRequest};
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;

class FineController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Fine::class);
        $search = trim($request->get('q',''));
        $statusFilter = $request->get('status');
        $query = Fine::query()->with(['vehicle','driver'])->orderByDesc('id');
        $user = $request->user();
        if ($user->role_id == 4) { // motorista vê apenas as suas
            $query->where('driver_id',$user->id);
        }
        if ($search !== '') {
            $query->where(function($q) use ($search){
                $like = "%$search%";
                $q->where('auto_number','like',$like)
                  ->orWhereHas('vehicle', fn($v)=>$v->where('plate','like',$like)->orWhere('prefix','like',$like))
                  ->orWhereHas('driver', fn($d)=>$d->where('name','like',$like));
            });
        }
        if ($statusFilter && in_array($statusFilter,['draft','aguardando_pagamento','pago','cancelado','arquivado'])) {
            $query->where('status',$statusFilter);
        }
        $fines = $query->paginate(15)->withQueryString();
        return view('fines.index', [
            'fines'=>$fines,
            'search'=>$search,
            'statusFilter'=>$statusFilter,
        ]);
    }

    public function pending(Request $request)
    {
        $user = $request->user();
        $fines = Fine::pendingAcknowledgement($user->id)->with(['vehicle','infractions'])->get();
        return view('fines.pending', compact('fines'));
    }

    public function create()
    {
        $this->authorize('create', Fine::class);
        $vehicles = Vehicle::orderBy('plate')->limit(200)->get();
        $drivers = User::where('role_id',4)->orderBy('name')->limit(200)->get();
        return view('fines.create', compact('vehicles','drivers'));
    }

    public function store(FineStoreRequest $request)
    {
        $this->authorize('create', Fine::class);
        $data = $request->validated();
        $fine = Fine::create($data);
        return redirect()->route('fines.edit',$fine)->with('success','Multa criada. Adicione infrações.');
    }

    public function edit(Fine $fine)
    {
        $this->authorize('update', $fine);
        $fine->load(['vehicle','driver','infractions.attachments','statusHistories.user']);
        $vehicles = Vehicle::orderBy('plate')->limit(200)->get();
        $drivers = User::where('role_id',4)->orderBy('name')->limit(200)->get();
        return view('fines.edit', compact('fine','vehicles','drivers'));
    }

    public function update(FineUpdateRequest $request, Fine $fine)
    {
        $this->authorize('update', $fine);
        $fine->update($request->validated());
        return redirect()->route('fines.edit',$fine)->with('success','Multa atualizada.');
    }

    public function destroy(Fine $fine)
    {
        $this->authorize('delete', $fine);
        $fine->delete();
        return redirect()->route('fines.index')->with('success','Multa removida.');
    }

    // --- Infrações ---
    public function storeInfraction(FineInfractionStoreRequest $request, Fine $fine)
    {
        $this->authorize('update', $fine);
        $data = $request->validated();
        $data['fine_id'] = $fine->id;
        $inf = FineInfraction::create($data);
        return back()->with('success','Infração adicionada.');
    }

    public function updateInfraction(FineInfractionUpdateRequest $request, Fine $fine, FineInfraction $infraction)
    {
        $this->authorize('update', $fine);
        if ($infraction->fine_id !== $fine->id) abort(404);
        $infraction->update($request->validated());
        return back()->with('success','Infração atualizada.');
    }

    public function deleteInfraction(Fine $fine, FineInfraction $infraction)
    {
        $this->authorize('update', $fine);
        if ($infraction->fine_id !== $fine->id) abort(404);
        $infraction->delete();
        return back()->with('success','Infração removida.');
    }

    // --- Anexos ---
    public function uploadAttachment(Request $request, Fine $fine, FineInfraction $infraction)
    {
        $this->authorize('update', $fine);
        if ($infraction->fine_id !== $fine->id) abort(404);
        $request->validate([
            'file' => ['required','file','max:5120']
        ]);
        $file = $request->file('file');
        $dir = 'fines/'.$fine->id.'/'.$infraction->id;
        $stored = $file->store($dir);
        $infraction->attachments()->create([
            'type' => $request->get('type','evidencia'),
            'original_name' => $file->getClientOriginalName(),
            'path' => $stored,
            'size' => $file->getSize()
        ]);
        return back()->with('success','Arquivo anexado.');
    }

    public function deleteAttachment(FineInfractionAttachment $attachment)
    {
        $fine = $attachment->infraction->fine;
        $this->authorize('update', $fine);
        Storage::delete($attachment->path);
        $attachment->delete();
        return back()->with('success','Anexo removido.');
    }

    // --- Status ---
    public function changeStatus(Request $request, Fine $fine)
    {
        $this->authorize('changeStatus',$fine);
        $request->validate([
            'status' => ['required','in:draft,aguardando_pagamento,pago,cancelado,arquivado']
        ]);
        $from = $fine->status; $to = $request->status;
        if ($from === $to) return back();
        DB::transaction(function() use ($fine,$from,$to,$request){
            $fine->status = $to;
            if ($to === 'pago') { $fine->paid_at = now(); }
            $fine->save();
            $fine->registerStatusChange($from,$to,$request->user()->id);
        });
        return back()->with('success','Status atualizado.');
    }

    public function acknowledge(Request $request, Fine $fine)
    {
        $this->authorize('acknowledge',$fine);
        if ($fine->acknowledged_at) return back();
        $fine->acknowledged_at = now();
        $fine->save();
        return redirect()->route('dashboard')->with('success','Ciência registrada.');
    }

    // --- Visualização ---
    public function show(Fine $fine)
    {
        $this->authorize('view',$fine);
        $fine->load(['vehicle','driver','infractions.attachments','statusHistories.user']);
        // registra primeira visualização (admin/gestor) se aplicável
        if (!$fine->first_view_at && auth()->user()->role_id <= 2) {
            $fine->first_view_at = now();
            $fine->save();
        }
        $fine->viewLogs()->create([
            'user_id'=>auth()->id(),
            'viewed_at'=>now(),
            'ip_address'=>request()->ip()
        ]);
        return view('fines.show', compact('fine'));
    }

    // --- Verificação Pública ---
    public function verifyForm()
    {
        return view('fines.verify');
    }

    public function verifySubmit(Request $request)
    {
        $data = $request->validate([
            'auth_code' => ['required','string'],
            'auto_number' => ['required','string'],
            'plate' => ['required','string']
        ]);
        $fine = Fine::where('auth_code',$data['auth_code'])
            ->where('auto_number',$data['auto_number'])
            ->whereHas('vehicle', fn($q)=>$q->where('plate',$data['plate']))
            ->with(['vehicle','infractions'])
            ->first();
        return view('fines.verify', [
            'result' => $fine,
            'data' => $data
        ]);
    }

    // --- PDF ---
    public function pdf(Fine $fine): StreamedResponse
    {
        $this->authorize('view',$fine);
        $fine->load(['vehicle','driver','infractions']);
        // Uso simples do TCPDF
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $html = '<h1>Notificação de Multa</h1>';
        $html .= '<p><strong>Auto:</strong> '.e($fine->auto_number).' | <strong>Código Autenticidade:</strong> '.e($fine->auth_code).'</p>';
        $html .= '<p><strong>Veículo:</strong> '.e($fine->vehicle->plate).' - '.e($fine->vehicle->model).'</p>';
        if ($fine->driver) { $html .= '<p><strong>Condutor:</strong> '.e($fine->driver->name).'</p>'; }
        $html .= '<table border="1" cellpadding="4"><thead><tr><th>Código</th><th>Descrição</th><th>Valor</th></tr></thead><tbody>';
        foreach ($fine->infractions as $inf) {
            $html .= '<tr><td>'.e($inf->code).'</td><td>'.e($inf->description).'</td><td>'.number_format($inf->final_amount,2,',','.').'</td></tr>';
        }
        $html .= '</tbody></table>';
        $html .= '<p><strong>Total:</strong> R$ '.number_format($fine->total_amount,2,',','.').'</p>';
        $pdf->writeHTML($html);
        return response()->streamDownload(function() use ($pdf){ $pdf->Output('multa.pdf','I'); }, 'multa-'.$fine->id.'.pdf');
    }

    // --- BUSCAS EM TEMPO REAL ---
    public function searchVehicles(Request $request)
    {
        $this->authorize('viewAny', Fine::class);
        $q = trim($request->get('q',''));
        $query = Vehicle::query();
        if ($q !== '') {
            $query->where(function($w) use ($q){
                $like = "%$q%";
                $w->where('plate','like',$like)
                  ->orWhere('prefix','like',$like)
                  ->orWhere('model','like',$like)
                  ->orWhere('brand','like',$like);
            });
        }
        return response()->json([
            'data' => $query->orderBy('plate')->limit(20)->get(['id','plate','prefix','brand','model'])
        ]);
    }

    public function searchDrivers(Request $request)
    {
        $this->authorize('viewAny', Fine::class);
        $q = trim($request->get('q',''));
        $query = User::query()->where('role_id',4);
        if ($q !== '') {
            $query->where('name','like',"%$q%");
        }
        return response()->json(['data'=>$query->orderBy('name')->limit(20)->get(['id','name'])]);
    }

    public function searchInfractionCodes(Request $request)
    {
        $this->authorize('viewAny', Fine::class);
        $q = trim($request->get('q',''));
        $codes = FineInfraction::query()
            ->when($q !== '', function($qq) use ($q){
                $qq->where('code','like',"%$q%")
                   ->orWhere('description','like',"%$q%");
            })
            ->select('code','description')
            ->groupBy('code','description')
            ->orderBy('code')
            ->limit(20)
            ->get();
        return response()->json(['data'=>$codes]);
    }

    public function searchAutoNumbers(Request $request)
    {
        $this->authorize('viewAny', Fine::class);
        $q = trim($request->get('q',''));
        $query = Fine::query();
        if ($q !== '') { $query->where('auto_number','like',"%$q%"); }
        return response()->json(['data'=>$query->orderByDesc('id')->limit(20)->pluck('auto_number')->unique()->values()]);
    }
}
