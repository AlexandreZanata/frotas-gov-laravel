<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Gerenciamento de Pneus - Frota" description="Visão geral de todos os veículos e status dos pneus." icon="fas fa-car">
            @if(auth()->user()->role_id == 1)
                <a href="{{ route('tire-layouts.index') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-sm flex items-center gap-2"><i class="fas fa-object-group"></i> Layouts</a>
                <a href="{{ route('vehicle-categories.index') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-sm rounded text-gray-800 dark:text-gray-100">Categorias</a>
            @endif
        </x-page-header>
    </x-slot>

    <x-page-container>
        {{-- KPIs --}}
        <section class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="p-4 rounded bg-white dark:bg-gray-800 shadow">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Total Pneus</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ $stats['total_tires'] }}</p>
            </div>
            <div class="p-4 rounded bg-white dark:bg-gray-800 shadow">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Críticos</p>
                <p class="text-2xl font-semibold text-red-600">{{ $stats['critical'] }}</p>
            </div>
            <div class="p-4 rounded bg-white dark:bg-gray-800 shadow">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Atenção</p>
                <p class="text-2xl font-semibold text-yellow-600">{{ $stats['attention'] }}</p>
            </div>
            <div class="p-4 rounded bg-white dark:bg-gray-800 shadow">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Veículos Monitorados</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ $stats['vehicles_monitored'] }}</p>
            </div>
            <div class="p-4 rounded bg-white dark:bg-gray-800 shadow">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Vida Útil Média</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-gray-100">{{ $stats['avg_life_usage'] !== null ? $stats['avg_life_usage'].'%' : '—' }}</p>
            </div>
        </section>

        {{-- Filtro --}}
        <form method="GET" action="{{ route('tires.dashboard') }}" class="flex flex-wrap gap-3 items-end mb-8">
            <div>
                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Buscar Veículo</label>
                <input name="q" value="{{ $search }}" placeholder="Placa / Prefixo / Modelo" class="mt-1 rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
            </div>
            <div class="flex gap-2 mt-4 md:mt-0">
                <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-sm">Filtrar</button>
                @if($search)
                    <a href="{{ route('tires.dashboard') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 rounded text-sm text-gray-800 dark:text-gray-100">Limpar</a>
                @endif
            </div>
        </form>

        {{-- Cards --}}
        <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($vehicles as $v)
                @php
                    $tireCat = $v->category;
                    $tv = $v->tires;
                    $countTotal = $tv->count();
                    $countCritical = $tv->where('status','critical')->count();
                    $countAttention = $tv->where('status','attention')->count();
                    $countRecap = $tv->where('status','recap_out')->count();
                @endphp
                @php
                    try { $tStatus = $v->tire_maintenance_status ?? null; } catch (Throwable $e) { $tStatus = ['label'=>'N/D','color'=>'bg-gray-400','km_progress'=>0,'days_progress'=>0,'next_km'=>null,'next_date'=>null]; }
                    if(!$tStatus){ $tStatus = ['label'=>'N/D','color'=>'bg-gray-400','km_progress'=>0,'days_progress'=>0,'next_km'=>null,'next_date'=>null]; }
                    $prog = max($tStatus['km_progress'] ?? 0, $tStatus['days_progress'] ?? 0);
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded shadow p-4 flex flex-col">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100 text-lg">{{ $v->plate ?? $v->prefix ?? '—' }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $v->brand }} {{ $v->model }}</p>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">Categoria: {{ $tireCat?->name ?? '—' }}</p>
                        </div>
                        <div class="text-right space-y-1 min-w-[100px]">
                            <div class="text-[11px] inline-flex items-center gap-1 px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">{{ $countTotal }} pneus</div>
                            @if($countCritical)<div class="text-[11px] text-red-500">Críticos: {{ $countCritical }}</div>@endif
                            @if($countAttention)<div class="text-[11px] text-yellow-500">Atenção: {{ $countAttention }}</div>@endif
                            @if($countRecap)<div class="text-[11px] text-purple-500">Recap: {{ $countRecap }}</div>@endif
                        </div>
                    </div>
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-medium text-gray-600 dark:text-gray-300 flex items-center gap-1">Próx. Revisão Pneus
                                <span class="px-1.5 py-0.5 rounded {{ $tStatus['color'] }} text-white text-[10px]">{{ $tStatus['label'] }}</span>
                            </span>
                            <span class="text-gray-500 dark:text-gray-400">
                                @if($tStatus['next_km']) {{ number_format($tStatus['next_km'],0,'','.') }} km @endif
                                @if($tStatus['next_km'] && $tStatus['next_date']) • @endif
                                @if($tStatus['next_date']) {{ $tStatus['next_date'] }} @endif
                            </span>
                        </div>
                        <div class="w-full h-2 rounded bg-gray-200 dark:bg-gray-700 overflow-hidden">
                            <div class="h-full {{ $tStatus['color'] }} transition-all" style="width: {{ $prog }}%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-500 dark:text-gray-400">
                            <span>{{ $prog }}%</span>
                            <span>Base: {{ $v->tire_service_base_km ?? $v->current_km }} km</span>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('tires.vehicle.layout',$v) }}" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-medium">Layout / Troca</a>
                        <a href="{{ route('tires.attention') }}" class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-medium">Críticos</a>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center text-gray-500 dark:text-gray-400">Nenhum veículo encontrado.</p>
            @endforelse
        </section>
        <div>{{ $vehicles->links() }}</div>
    </x-page-container>
</x-app-layout>
