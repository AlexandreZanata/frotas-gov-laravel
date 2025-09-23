<?php
namespace App\Http\Controllers;

use App\Models\{Run, AuditLog};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RunBlockController extends Controller
{
    private function ensureCanManage(Request $request): void
    {
        $u = $request->user();
        if (!$u || (int)$u->role_id !== 1) {
            abort(403,'Acesso não autorizado.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureCanManage($request);
        return view('runs.blocking');
    }

    public function search(Request $request)
    {
        $this->ensureCanManage($request);
        $q = trim($request->get('q',''));
        $query = Run::query()->with(['vehicle','driver','blocker'])
            ->whereIn('status',['pending_start','in_progress','blocked']);
        if ($q !== '') {
            $query->where(function($sub) use ($q){
                $like = "%$q%";
                $sub->whereHas('vehicle', fn($v)=>$v->where('plate','like',$like)->orWhere('model','like',$like)->orWhere('brand','like',$like))
                    ->orWhereHas('driver', fn($d)=>$d->where('name','like',$like));
            });
        }
        $runs = $query->orderByDesc('id')->limit(50)->get()->map(function($r){
            return [
                'id'=>$r->id,
                'vehicle'=> $r->vehicle?->plate.' '.$r->vehicle?->model,
                'driver'=> $r->driver?->name,
                'status'=> $r->status,
                'blocked_at'=> $r->blocked_at?->format('Y-m-d H:i'),
                'blocked_by'=> $r->blocker?->name,
                'destination'=> $r->destination,
                'start_km'=> $r->start_km,
                'start_time'=> optional($r->start_time)->format('Y-m-d H:i'),
            ];
        });
        return response()->json(['data'=>$runs]);
    }

    public function block(Request $request, Run $run)
    {
        $this->ensureCanManage($request);
        $request->validate(['keyword'=>['required','in:BLOQUEAR']]);
        if ($run->status === 'completed') {
            return response()->json(['message'=>'Corrida já finalizada, não pode ser bloqueada'],422);
        }
        if ($run->status === 'blocked') {
            return response()->json(['message'=>'Corrida já bloqueada'],200);
        }
        $oldStatus = $run->status;
        $run->update([
            'blocked_previous_status'=>$oldStatus,
            'status'=>'blocked',
            'blocked_at'=>now(),
            'blocked_by'=>$request->user()->id
        ]);
        $this->audit($request,'block_run',$run->id,['status'=>$oldStatus],['status'=>'blocked']);
        return response()->json(['message'=>'Corrida bloqueada']);
    }

    public function unblock(Request $request, Run $run)
    {
        $this->ensureCanManage($request);
        $request->validate(['keyword'=>['required','in:DESBLOQUEAR']]);
        if ($run->status !== 'blocked') {
            return response()->json(['message'=>'Corrida não está bloqueada'],422);
        }
        $restore = $run->blocked_previous_status ?: 'pending_start';
        $run->update([
            'status'=>$restore,
            'blocked_previous_status'=>null,
            'blocked_at'=>null,
            'blocked_by'=>null
        ]);
        $this->audit($request,'unblock_run',$run->id,['status'=>'blocked'],['status'=>$restore]);
        return response()->json(['message'=>'Corrida desbloqueada']);
    }

    private function audit(Request $request,string $action,int $recordId,array $old,array $new): void
    {
        AuditLog::create([
            'user_id'=>$request->user()->id,
            'action'=>$action,
            'table_name'=>'runs',
            'record_id'=>$recordId,
            'old_value'=>$old,
            'new_value'=>$new,
            'ip_address'=>$request->ip()
        ]);
    }
}

