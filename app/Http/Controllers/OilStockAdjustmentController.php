<?php
namespace App\Http\Controllers;

use App\Models\{OilProduct, OilStockAdjustment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OilStockAdjustmentController extends Controller
{
    public function store(Request $request, OilProduct $oilProduct)
    {
        // Autorização simples (poderia ter Policy dedicada futuramente)
        if (!auth()->check() || auth()->user()->role_id > 2) {
            abort(403,'Não autorizado');
        }
        $data = $request->validate([
            'type' => 'required|in:in,out',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost_at_time' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:255'
        ]);
        $data['oil_product_id'] = $oilProduct->id;
        $data['user_id'] = auth()->id();

        DB::transaction(function() use ($data, $oilProduct) {
            OilStockAdjustment::create($data);
            // Atualiza o estoque agregado, se coluna existir
            if ($oilProduct->getConnection()->getSchemaBuilder()->hasColumn($oilProduct->getTable(),'stock_quantity')) {
                if ($data['type'] === 'in') {
                    $oilProduct->stock_quantity = (float)($oilProduct->stock_quantity ?? 0) + (float)$data['quantity'];
                } else {
                    $oilProduct->stock_quantity = max(0, (float)($oilProduct->stock_quantity ?? 0) - (float)$data['quantity']);
                }
                $oilProduct->save();
            }
        });

        $message = $data['type'] === 'in' ? 'Entrada registrada.' : 'Saída registrada.';
        if ($request->expectsJson()) {
            return response()->json(['success'=>true,'message'=>$message]);
        }
        return back()->with('success', $message);
    }
}

