@extends('layouts.app')
@section('header')
<h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2"><i class="fas fa-chart-line text-blue-600"></i> Relatório de Combustível</h2>
@endsection
@section('content')
<div class="max-w-7xl mx-auto p-4 space-y-8" x-data="fuelReport()" x-init="init()">
    <form method="GET" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-sm p-4 grid gap-4 md:grid-cols-6 items-end">
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Data Inicial</label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-sm" />
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Data Final</label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-sm" />
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Veículo</label>
            <select name="vehicle_id" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-sm">
                <option value="">Todos</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" @selected($filters['vehicle_id']==$v->id)>{{ $v->plate }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400">Combustível</label>
            <select name="fuel_type_id" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-sm">
                <option value="">Todos</option>
                @foreach($fuelTypes as $ft)
                    <option value="{{ $ft->id }}" @selected($filters['fuel_type_id']==$ft->id)>{{ $ft->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2 flex gap-2">
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-medium flex items-center gap-2"><i class="fas fa-search"></i> Filtrar</button>
            <a href="{{ route('reports.fuel.index') }}" class="px-3 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded text-sm">Limpar</a>
        </div>
    </form>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            <div class="p-4 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2"><i class="fas fa-list"></i> Registros (máx. 500)</h3>
                    <form method="POST" action="{{ route('reports.fuel.pdf') }}" target="_blank" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="date_from" value="{{ $filters['date_from'] }}" />
                        <input type="hidden" name="date_to" value="{{ $filters['date_to'] }}" />
                        <input type="hidden" name="vehicle_id" value="{{ $filters['vehicle_id'] }}" />
                        <input type="hidden" name="fuel_type_id" value="{{ $filters['fuel_type_id'] }}" />
                        <select name="template_id" class="rounded border-gray-300 dark:bg-gray-900 dark:border-gray-700 text-xs">
                            <option value="">Modelo PDF (opcional)</option>
                            @foreach($templates as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                        <button class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-medium flex items-center gap-1"><i class="fas fa-file-pdf"></i> PDF</button>
                    </form>
                </div>
                <div class="overflow-x-auto rounded border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/40 text-[11px] text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="p-2 text-left">Data</th>
                                <th class="p-2 text-left">Veículo</th>
                                <th class="p-2 text-left">Comb.</th>
                                <th class="p-2 text-right">Litros</th>
                                <th class="p-2 text-right">Valor</th>
                                <th class="p-2 text-left">Posto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fuelings as $f)
                                <tr class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="p-2 text-gray-700 dark:text-gray-300">{{ optional($f->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="p-2 text-gray-700 dark:text-gray-300">{{ $f->vehicle?->plate }}</td>
                                    <td class="p-2 text-gray-700 dark:text-gray-300">{{ $f->fuelType?->name }}</td>
                                    <td class="p-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($f->liters,2,',','.') }}</td>
                                    <td class="p-2 text-right text-gray-700 dark:text-gray-300">{{ number_format($f->total_value,2,',','.') }}</td>
                                    <td class="p-2 text-gray-700 dark:text-gray-300">{{ $f->gasStation?->name ?? $f->gas_station_name }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-4 text-center text-gray-500 dark:text-gray-400 text-xs">Sem registros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="space-y-6">
            <div class="p-4 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm space-y-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2"><i class="fas fa-chart-pie"></i> Resumo</h3>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
                        <div class="text-[10px] text-gray-500 dark:text-gray-400">Total Litros</div>
                        <div class="font-semibold text-gray-800 dark:text-gray-100 text-sm">{{ number_format($summary['total_liters'],2,',','.') }}</div>
                    </div>
                    <div class="p-3 rounded bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700">
                        <div class="text-[10px] text-gray-500 dark:text-gray-400">Valor Total</div>
                        <div class="font-semibold text-gray-800 dark:text-gray-100 text-sm">R$ {{ number_format($summary['total_value'],2,',','.') }}</div>
                    </div>
                </div>
                <div class="space-y-2">
                    <h4 class="text-[11px] font-semibold text-gray-600 dark:text-gray-300">Por Combustível</h4>
                    <div class="space-y-1 text-[11px]">
                        @forelse($summary['by_fuel_type'] as $name=>$row)
                            <div class="flex justify-between border-b border-dashed border-gray-200 dark:border-gray-700 pb-1">
                                <span class="text-gray-600 dark:text-gray-300">{{ $name }}</span>
                                <span class="text-gray-800 dark:text-gray-100 font-medium">{{ number_format($row['liters'],2,',','.') }} L / R$ {{ number_format($row['value'],2,',','.') }}</span>
                            </div>
                        @empty
                            <p class="text-gray-400 italic">Sem dados.</p>
                        @endforelse
                    </div>
                </div>
                <div class="space-y-2">
                    <h4 class="text-[11px] font-semibold text-gray-600 dark:text-gray-300">Por Veículo</h4>
                    <div class="space-y-1 text-[11px] max-h-48 overflow-auto custom-scroll">
                        @forelse($summary['by_vehicle'] as $plate=>$row)
                            <div class="flex justify-between border-b border-dashed border-gray-200 dark:border-gray-700 pb-1">
                                <span class="text-gray-600 dark:text-gray-300">{{ $plate }}</span>
                                <span class="text-gray-800 dark:text-gray-100 font-medium">{{ number_format($row['liters'],2,',','.') }} L</span>
                            </div>
                        @empty
                            <p class="text-gray-400 italic">Sem dados.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function fuelReport(){ return { init(){} } }
</script>
@endsection
