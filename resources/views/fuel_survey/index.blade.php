@extends('layouts.app')
@section('header')
<h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2"><i class="fas fa-gas-pump text-amber-600"></i> Cotações de Combustível</h2>
@endsection
@section('content')
<div class="max-w-6xl mx-auto p-4 space-y-6">
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500 dark:text-gray-400">Histórico de cotações registradas.</div>
        <a href="{{ route('fuel-surveys.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-medium shadow">
            <i class="fas fa-plus-circle"></i> Nova Cotação
        </a>
    </div>

    @if(session('success'))
        <div class="p-3 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded text-sm border border-green-200 dark:border-green-700 flex items-center gap-2"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    <div class="overflow-hidden rounded border border-gray-200 dark:border-gray-700 shadow bg-white dark:bg-gray-800">
        <div class="hidden md:grid md:grid-cols-6 px-4 py-2 text-[11px] font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/40">
            <div>Data</div>
            <div>Método</div>
            <div class="col-span-2">Descontos</div>
            <div>Postos</div>
            <div></div>
        </div>
        @forelse($surveys as $s)
            <div class="grid grid-cols-1 md:grid-cols-6 px-4 py-3 text-sm items-center gap-1 border-t first:border-t-0 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                <div class="font-medium text-gray-800 dark:text-gray-100">{{ $s->survey_date->format('d/m/Y') }}</div>
                <div>
                    <span class="px-2 py-0.5 rounded text-[11px] font-medium {{ $s->method==='custom' ? 'bg-indigo-600 text-white':'bg-gray-200 dark:bg-gray-700 dark:text-gray-200' }}">{{ $s->method==='custom'?'Personalizado':'Simples' }}</span>
                </div>
                <div class="md:col-span-2 flex flex-wrap gap-1 text-[11px]">
                    @php $fuels=['diesel_s500'=>'S500','diesel_s10'=>'S10','gasoline'=>'GAS','ethanol'=>'ET']; @endphp
                    @foreach($fuels as $k=>$lbl)
                        @if(!is_null($s->{'discount_'.$k}))
                            <span class="px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200 border border-amber-200 dark:border-amber-700">{{ $lbl }}: {{ number_format($s->{'discount_'.$k},2,',','.') }}%</span>
                        @endif
                    @endforeach
                    @if(collect($fuels)->filter(fn($_,$k)=>!is_null($s->{'discount_'.$k}))->isEmpty())
                        <span class="text-xs italic text-gray-400">—</span>
                    @endif
                </div>
                <div class="text-gray-700 dark:text-gray-300">{{ $s->stationPrices()->count() }}</div>
                <div class="text-right">
                    <a href="{{ route('fuel-surveys.show',$s) }}" class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 hover:underline text-xs"><i class="fas fa-eye"></i> Ver</a>
                </div>
            </div>
        @empty
            <div class="p-6 text-sm text-gray-600 dark:text-gray-300">Nenhuma cotação cadastrada.</div>
        @endforelse
    </div>
    <div>{{ $surveys->links() }}</div>
</div>
@endsection
