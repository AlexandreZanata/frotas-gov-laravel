<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\OilProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class OilMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');
        $query = Vehicle::query();
        if (Schema::hasTable('oil_change_logs') && Schema::hasColumn('oil_change_logs','vehicle_id')) {
            $query->with(['latestOilChangeLog.product']);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('plate','like',"%$search%")
                  ->orWhere('brand','like',"%$search%")
                  ->orWhere('model','like',"%$search%")
                  ->orWhere('prefix','like',"%$search%");
            });
        }
        $vehicles = $query->paginate(12)->withQueryString();

        $stats = [ 'Em Dia'=>0,'Atenção'=>0,'Crítico'=>0,'Vencido'=>0,'Sem Registro'=>0 ];
        foreach($vehicles as $v){
            if (!(Schema::hasTable('oil_change_logs') && Schema::hasColumn('oil_change_logs','vehicle_id'))) { $stats['Sem Registro']++; continue; }
            $label = $v->oil_maintenance_status['label'] ?? 'Sem Registro';
            $stats[$label] = ($stats[$label] ?? 0) + 1;
        }

        $lowStockProducts = collect();
        $products = collect();
        if (Schema::hasTable('oil_products')) {
            $base = OilProduct::query();
            $orderCol = Schema::hasColumn('oil_products','name') ? 'name' : (Schema::hasColumn('oil_products','code') ? 'code' : 'id');
            $base->orderBy($orderCol);
            $products = $base->get();
            $lowStockProducts = $products->filter->isLowStock();
        }
        return view('oil.maintenance', compact('vehicles','stats','lowStockProducts','search','products'));
    }

    public function logs(Request $request)
    {
        if (!(Schema::hasTable('oil_change_logs') && Schema::hasColumn('oil_change_logs','vehicle_id'))) {
            $logs = collect();
        } else {
            $logsQuery = \App\Models\OilChangeLog::with(['vehicle','product','user']);
            if (Schema::hasColumn('oil_change_logs','change_date')) {
                $logsQuery->orderByDesc('change_date');
            } else {
                $logsQuery->orderByDesc('id');
            }
            if ($request->filled('vehicle_id')) $logsQuery->where('vehicle_id',$request->integer('vehicle_id'));
            if ($request->filled('oil_product_id')) $logsQuery->where('oil_product_id',$request->integer('oil_product_id'));
            $logs = $logsQuery->paginate(25)->withQueryString();
        }
        $vehicles = Vehicle::select('id','plate','brand','model')->orderBy('plate')->get();
        $products = collect();
        if (Schema::hasTable('oil_products')) {
            $query = OilProduct::query();
            // não seleciona 'name' se não existir
            $selectCols = ['id'];
            if (Schema::hasColumn('oil_products','name')) $selectCols[] = 'name';
            if (Schema::hasColumn('oil_products','code')) $selectCols[] = 'code';
            $orderCol = Schema::hasColumn('oil_products','name') ? 'name' : (Schema::hasColumn('oil_products','code') ? 'code' : 'id');
            $products = $query->select($selectCols)->orderBy($orderCol)->get();
        }
        return view('oil.logs', compact('logs','vehicles','products'));
    }
}
