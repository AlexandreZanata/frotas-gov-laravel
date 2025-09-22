<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Fueling, Vehicle, FuelType, GasStation, PdfTemplate};
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FuelConsumptionReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAccess($request);
        $filters = $this->extractFilters($request);
        $query = Fueling::query()->with(['vehicle','fuelType','gasStation']);

        if ($filters['date_from']) { $query->whereDate('created_at','>=',$filters['date_from']); }
        if ($filters['date_to']) { $query->whereDate('created_at','<=',$filters['date_to']); }
        if ($filters['vehicle_id']) { $query->where('vehicle_id',$filters['vehicle_id']); }
        if ($filters['fuel_type_id']) { $query->where('fuel_type_id',$filters['fuel_type_id']); }

        $fuelings = $query->orderByDesc('created_at')->limit(500)->get();

        $summary = [
            'total_liters' => (float)$fuelings->sum('liters'),
            'total_value' => (float)$fuelings->sum('total_value'),
            'by_fuel_type' => $fuelings->groupBy('fuelType.name')->map(fn($c)=>[
                'liters'=>(float)$c->sum('liters'),
                'value'=>(float)$c->sum('total_value'),
                'avg_price_liter'=> $c->sum('liters')>0 ? round($c->sum('total_value')/$c->sum('liters'),2) : null
            ])->sortKeys()->toArray(),
            'by_vehicle' => $fuelings->groupBy(fn($f)=>$f->vehicle?->plate)->map(fn($c)=>[
                'liters'=>(float)$c->sum('liters'),
                'value'=>(float)$c->sum('total_value')
            ])->sortKeys()->toArray()
        ];

        $vehicles = Vehicle::orderBy('plate')->get(['id','plate']);
        $fuelTypes = FuelType::orderBy('name')->get(['id','name']);
        $templates = PdfTemplate::orderBy('name')->get(['id','name']);

        return view('reports.fuel.index', compact('fuelings','summary','vehicles','fuelTypes','templates','filters'));
    }

    public function pdf(Request $request): StreamedResponse
    {
        $this->authorizeAccess($request);
        $data = $request->validate([
            'date_from' => ['nullable','date'],
            'date_to' => ['nullable','date','after_or_equal:date_from'],
            'vehicle_id' => ['nullable','integer'],
            'fuel_type_id' => ['nullable','integer'],
            'template_id' => ['nullable','integer','exists:pdf_templates,id']
        ]);
        $filters = $this->extractFilters($request);
        $query = Fueling::query()->with(['vehicle','fuelType','gasStation']);
        if ($filters['date_from']) { $query->whereDate('created_at','>=',$filters['date_from']); }
        if ($filters['date_to']) { $query->whereDate('created_at','<=',$filters['date_to']); }
        if ($filters['vehicle_id']) { $query->where('vehicle_id',$filters['vehicle_id']); }
        if ($filters['fuel_type_id']) { $query->where('fuel_type_id',$filters['fuel_type_id']); }
        $fuelings = $query->orderBy('created_at')->get();

        $summary = [
            'total_liters' => (float)$fuelings->sum('liters'),
            'total_value' => (float)$fuelings->sum('total_value'),
        ];

        $template = null;
        if (!empty($data['template_id'])) {
            $template = PdfTemplate::find($data['template_id']);
        }

        $pdf = new \TCPDF();
        $pdf->AddPage();
        $title = 'Relatório de Combustível';
        $html = '<h1 style="font-size:16px;">'.e($title).'</h1>';
        $html .= '<p><strong>Período:</strong> '.($filters['date_from']?e($filters['date_from']):'—').' até '.($filters['date_to']?e($filters['date_to']):'—').'</p>';
        $html .= '<p><strong>Total de Litros:</strong> '.number_format($summary['total_liters'],2,',','.').' | <strong>Valor Total:</strong> R$ '.number_format($summary['total_value'],2,',','.').'</p>';

        $html .= '<table border="1" cellpadding="3" cellspacing="0" width="100%"><thead><tr style="background:#f0f0f0;"><th align="left">Data</th><th align="left">Veículo</th><th align="left">Combustível</th><th align="right">Litros</th><th align="right">Valor</th><th align="left">Posto</th></tr></thead><tbody>';
        foreach ($fuelings as $f) {
            $html .= '<tr>';
            $html .= '<td>'.e(optional($f->created_at)->format('d/m/Y H:i')).'</td>';
            $html .= '<td>'.e($f->vehicle?->plate).'</td>';
            $html .= '<td>'.e($f->fuelType?->name).'</td>';
            $html .= '<td align="right">'.number_format($f->liters,2,',','.').'</td>';
            $html .= '<td align="right">'.number_format($f->total_value,2,',','.').'</td>';
            $html .= '<td>'.e($f->gasStation?->name ?? $f->gas_station_name).'</td>';
            $html .= '</tr>';
        }
        if ($fuelings->isEmpty()) {
            $html .= '<tr><td colspan="6" align="center">Sem registros</td></tr>';
        }
        $html .= '</tbody></table>';

        if ($template && $template->after_table_text) {
            $html .= '<p style="margin-top:12px;">'.nl2br(e($template->after_table_text)).'</p>';
        }
        $pdf->writeHTML($html);
        return response()->streamDownload(fn()=> $pdf->Output('relatorio-combustivel.pdf','I'), 'relatorio-combustivel.pdf');
    }

    private function authorizeAccess(Request $request): void
    {
        // Somente gestores/admin (role_id <=2) geram relatórios; ajuste conforme política
        if (!$request->user() || $request->user()->role_id > 2) {
            abort(403,'Acesso negado ao relatório de combustível.');
        }
    }

    private function extractFilters(Request $request): array
    {
        return [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'vehicle_id' => $request->get('vehicle_id'),
            'fuel_type_id' => $request->get('fuel_type_id'),
        ];
    }
}

