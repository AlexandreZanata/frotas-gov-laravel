<?php

namespace App\Http\Controllers;

use App\Models\OilProduct;
use App\Models\AuditLog;
use App\Http\Requests\OilProductStoreRequest;
use App\Http\Requests\OilProductUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OilProductController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', OilProduct::class);
        $query = OilProduct::query();
        $search = $request->get('q');
        if ($search) {
            $query->where(function($q) use ($search) {
                $candidates = ['name','code','brand','viscosity'];
                foreach ($candidates as $col) {
                    if (Schema::hasColumn('oil_products',$col)) {
                        $q->orWhere($col,'like',"%$search%");
                    }
                }
            });
        }
        $orderColumn = Schema::hasColumn('oil_products','name') ? 'name' : (Schema::hasColumn('oil_products','code') ? 'code' : 'id');
        $products = $query->orderBy($orderColumn)->paginate(15)->withQueryString();
        return view('oil-products.index', compact('products','search'));
    }

    public function create()
    {
        $this->authorize('create', OilProduct::class);
        return view('oil-products.create');
    }

    public function store(OilProductStoreRequest $request)
    {
        $this->authorize('create', OilProduct::class);
        $data = $this->filterExistingColumns($request->validated());
        OilProduct::create($data);
        return redirect()->route('oil-products.index')->with('success','Produto de óleo criado com sucesso.');
    }

    public function edit(OilProduct $oilProduct)
    {
        $this->authorize('update', $oilProduct);
        return view('oil-products.edit', ['product' => $oilProduct]);
    }

    public function update(OilProductUpdateRequest $request, OilProduct $oilProduct)
    {
        $this->authorize('update', $oilProduct);
        $data = $this->filterExistingColumns($request->validated());
        $oilProduct->update($data);
        return redirect()->route('oil-products.index')->with('success','Produto atualizado com sucesso.');
    }

    public function destroy(OilProduct $oilProduct)
    {
        $this->authorize('delete', $oilProduct);
        $oilProduct->delete();
        return redirect()->route('oil-products.index')->with('success','Produto excluído com sucesso.');
    }

    public function history(OilProduct $oilProduct)
    {
        $this->authorize('view', $oilProduct);
        $table = $oilProduct->getTable();
        $logs = AuditLog::with('user')
            ->where('table_name', $table)
            ->where('record_id', $oilProduct->id)
            ->orderByDesc('id')
            ->paginate(30);
        $adjustments = $oilProduct->stockAdjustments()->with('user')->latest()->limit(50)->get();
        return view('oil-products.history', [
            'product' => $oilProduct,
            'logs' => $logs,
            'adjustments' => $adjustments,
        ]);
    }

    public function exportHistoryCsv(OilProduct $oilProduct): StreamedResponse
    {
        $this->authorize('view', $oilProduct);
        $table = $oilProduct->getTable();
        $logs = AuditLog::with('user')
            ->where('table_name', $table)
            ->where('record_id', $oilProduct->id)
            ->orderBy('id')
            ->get();
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="historico_produto_'.$oilProduct->id.'.csv"'
        ];
        return response()->stream(function() use ($logs) {
            $out = fopen('php://output','w');
            fputcsv($out, ['timestamp','acao','usuario','campo','valor_antigo','valor_novo','ip']);
            foreach ($logs as $log) {
                $old = $log->old_value ?? [];
                $new = $log->new_value ?? [];
                $fields = [];
                if ($log->action === 'update') {
                    foreach ($new as $k=>$v) {
                        $ov = $old[$k] ?? null;
                        if ($ov !== $v) $fields[$k] = [$ov,$v];
                    }
                } elseif ($log->action === 'create') {
                    foreach ($new as $k=>$v) { $fields[$k] = [null,$v]; }
                } elseif ($log->action === 'delete') {
                    foreach ($old as $k=>$v) { $fields[$k] = [$v,null]; }
                }
                if (empty($fields)) {
                    fputcsv($out, [
                        $log->created_at,
                        $log->action,
                        $log->user?->name,
                        null,null,null,
                        $log->ip_address
                    ]);
                } else {
                    foreach ($fields as $field=>$pair) {
                        [$ov,$nv] = $pair;
                        fputcsv($out, [
                            $log->created_at,
                            $log->action,
                            $log->user?->name,
                            $field,
                            is_scalar($ov)||$ov===null? $ov: json_encode($ov, JSON_UNESCAPED_UNICODE),
                            is_scalar($nv)||$nv===null? $nv: json_encode($nv, JSON_UNESCAPED_UNICODE),
                            $log->ip_address
                        ]);
                    }
                }
            }
            fclose($out);
        }, 200, $headers);
    }

    private function filterExistingColumns(array $data): array
    {
        $table = (new OilProduct)->getTable();
        return collect($data)->filter(fn($v,$k)=>Schema::hasColumn($table,$k))->all();
    }
}
