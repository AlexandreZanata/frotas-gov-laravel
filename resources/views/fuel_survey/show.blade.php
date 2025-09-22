@extends('layouts.app')
@section('header')
<h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2"><i class="fas fa-gas-pump text-amber-600"></i> Cotação {{ $survey->survey_date->format('d/m/Y') }}</h2>
@endsection
@section('content')
@php($fuels = \App\Models\FuelPriceSurvey::fuelKeys())
@php($labels = $fuelLabels)
<div class="max-w-7xl mx-auto p-4 space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded px-4 py-3 shadow-sm">
        <p class="text-xs text-gray-500 dark:text-gray-400">Resumo e comparação dos preços coletados versus preço médio com desconto.</p>
        <a href="{{ route('fuel-surveys.index') }}" class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-200 dark:bg-gray-700 rounded text-[11px] font-medium text-gray-700 dark:text-gray-200"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>

    <div class="grid md:grid-cols-4 gap-4">
        <div class="md:col-span-1 p-4 rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 space-y-3 shadow-sm text-sm">
            <div class="flex items-center justify-between">
                <span class="font-medium text-gray-700 dark:text-gray-200">Data</span>
                <span class="text-gray-800 dark:text-gray-100 font-semibold">{{ $survey->survey_date->format('d/m/Y') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="font-medium text-gray-700 dark:text-gray-200">Método</span>
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $survey->method==='custom' ? 'bg-indigo-600 text-white':'bg-gray-200 dark:bg-gray-700 dark:text-gray-200' }}">{{ $survey->method==='custom'?'Personalizado':'Simples' }}</span>
            </div>
            <div class="space-y-2 pt-1">
                <div class="font-medium text-xs text-gray-600 dark:text-gray-400">Descontos</div>
                <div class="grid grid-cols-2 gap-2">
                    @php $hasDisc=false; @endphp
                    @foreach($fuels as $f)
                        @php $val = $survey->{'discount_'.$f}; @endphp
                        @if(!is_null($val))
                            @php $hasDisc=true; @endphp
                            <div class="px-2 py-1 rounded bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-200 text-[11px] border border-amber-200 dark:border-amber-700 flex items-center justify-between">
                                <span>{{ $labels[$f] }}</span>
                                <span>{{ number_format($val,2,',','.') }}%</span>
                            </div>
                        @endif
                    @endforeach
                    @if(!$hasDisc)
                        <div class="col-span-2 text-[11px] italic text-gray-400">Nenhum desconto definido.</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="md:col-span-3 p-4 rounded bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 space-y-4 shadow-sm">
            <h2 class="font-semibold text-sm flex items-center gap-2 text-gray-700 dark:text-gray-200"><i class="fas fa-chart-bar"></i> Médias com Desconto</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-[13px]">
                @foreach($fuels as $f)
                    @php $discAvg = $comparison[$f]['discounted_average'] ?? null; @endphp
                    <div class="p-3 rounded border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 space-y-1">
                        <div class="font-medium text-[11px] text-gray-600 dark:text-gray-300">{{ $labels[$f] }}</div>
                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ is_null($discAvg)?'—':number_format($discAvg,3,',','.') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-10">
        @foreach($fuels as $f)
            @php $discAvg = $comparison[$f]['discounted_average'] ?? null; @endphp
            <div class="space-y-3">
                <div class="flex items-center flex-wrap gap-3">
                    <h3 class="font-semibold text-sm text-gray-700 dark:text-gray-200 flex items-center gap-2"><i class="fas fa-balance-scale"></i> {{ $labels[$f] }}</h3>
                    @if(!is_null($discAvg))
                        <span class="text-[11px] px-2 py-0.5 rounded bg-indigo-600 text-white font-medium shadow">Preço Médio c/ Desconto: {{ number_format($discAvg,3,',','.') }}</span>
                    @else
                        <span class="text-[11px] px-2 py-0.5 rounded bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200">Sem dados suficientes</span>
                    @endif
                </div>
                <div class="overflow-x-auto rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/40 text-[11px] text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="p-2 text-left">Posto</th>
                                <th class="p-2 text-left">Preço Bomba</th>
                                <th class="p-2 text-left">Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($comparison[$f]['rows'] as $row)
                                @php
                                    $color = $row['favorable']===null ? 'bg-white dark:bg-gray-800' : ($row['favorable'] ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20');
                                    $badge = $row['favorable']===null ? '—' : ($row['favorable'] ? 'Favorável' : 'Desfavorável');
                                @endphp
                                <tr class="border-t border-gray-200 dark:border-gray-700 {{ $color }} hover:bg-gray-50 dark:hover:bg-gray-700/40">
                                    <td class="p-2 text-gray-800 dark:text-gray-100">{{ $row['station'] }}</td>
                                    <td class="p-2 text-gray-700 dark:text-gray-300">{{ is_null($row['price'])?'—':number_format($row['price'],3,',','.') }}</td>
                                    <td class="p-2">
                                        @if($row['favorable']!==null)
                                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $row['favorable']?'bg-green-600 text-white':'bg-red-600 text-white' }}">{{ $badge }}</span>
                                        @else <span class="text-[11px] text-gray-400">—</span> @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="p-3 text-center text-gray-500 dark:text-gray-400 text-xs">Nenhum posto selecionado para comparação.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
