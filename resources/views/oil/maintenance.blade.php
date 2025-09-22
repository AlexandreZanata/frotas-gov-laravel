<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard de Manutenção de Óleo" icon="fas fa-gauge">
            <a href="{{ route('oil.logs') }}" class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium rounded bg-indigo-600 hover:bg-indigo-700 text-white shadow">
                <i class="fas fa-list"></i> Histórico
            </a>
        </x-page-header>
    </x-slot>

    <x-page-container x-data="{ openForm: null }">
        <div class="mb-6 grid gap-4 md:grid-cols-3">
            <form method="GET" class="flex gap-2 col-span-2">
                <div class="flex-1 relative">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Buscar: placa, modelo ou prefixo" class="w-full pl-10 pr-3 py-2 rounded border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-search"></i></span>
                </div>
                <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 shadow">Filtrar</button>
                @if($search)
                    <a href="{{ route('oil.maintenance') }}" class="px-3 py-2 bg-gray-300 dark:bg-gray-700 dark:text-gray-100 rounded text-sm">Limpar</a>
                @endif
            </form>
            <div class="flex flex-wrap items-center gap-2 justify-start md:justify-end">
                @php($palette=[ 'Em Dia'=>'bg-green-600','Atenção'=>'bg-yellow-500','Crítico'=>'bg-red-500','Vencido'=>'bg-red-700','Sem Registro'=>'bg-gray-500'])
                @foreach($stats as $label => $count)
                    <span class="flex items-center gap-2 text-xs font-medium px-2.5 py-1.5 rounded text-white shadow {{ $palette[$label] ?? 'bg-gray-500' }}">
                        <span>{{ $label }}</span>
                        <span class="bg-white/20 rounded px-1">{{ $count }}</span>
                    </span>
                @endforeach
            </div>
        </div>

        @if($lowStockProducts->count())
            <div class="mb-6 p-4 rounded border border-amber-300 dark:border-amber-600 bg-amber-50 dark:bg-amber-900/30">
                <div class="flex items-start gap-3">
                    <div class="text-amber-500"><i class="fas fa-triangle-exclamation"></i></div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-amber-800 dark:text-amber-300 mb-1">Estoque Baixo</h3>
                        <ul class="text-xs text-amber-900 dark:text-amber-200 grid gap-1 md:grid-cols-2">
                            @foreach($lowStockProducts as $p)
                                <li class="flex justify-between"><span>{{ $p->display_name }}</span><span class="font-semibold">{{ $p->stock_quantity }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($vehicles as $vehicle)
                @php($status = $vehicle->oil_maintenance_status)
                <div class="relative group bg-white/80 dark:bg-gray-800/70 backdrop-blur rounded-lg border border-gray-200 dark:border-gray-700 shadow hover:shadow-md transition flex flex-col p-5 overflow-hidden">
                    <div class="absolute inset-0 pointer-events-none opacity-0 group-hover:opacity-100 transition" style="background: radial-gradient(circle at 30% 20%, rgba(59,130,246,0.12), transparent 70%)"></div>
                    <div class="flex justify-between items-start">
                        <div class="space-y-0.5">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-lg tracking-tight">{{ $vehicle->brand }} {{ $vehicle->model }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Placa <span class="font-semibold">{{ $vehicle->plate }}</span></p>
                            @if($vehicle->prefix)
                                <p class="text-[10px] text-gray-500 dark:text-gray-500">Prefixo: {{ $vehicle->prefix }}</p>
                            @endif
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="text-[10px] font-bold text-white px-2 py-1 rounded {{ $status['color'] }} shadow">{{ $status['label'] }}</span>
                            <button @click="openForm === {{ $vehicle->id }} ? openForm = null : openForm = {{ $vehicle->id }}" class="text-xs px-2 py-1 rounded border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white transition">Troca</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mt-4 text-[11px] text-gray-700 dark:text-gray-300">
                        <div class="flex flex-col bg-gray-50 dark:bg-gray-900/40 rounded p-2">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400">KM Atual</span>
                            <span class="font-semibold tracking-tight">{{ number_format($vehicle->current_km,0,'','.') }}</span>
                        </div>
                        <div class="flex flex-col bg-gray-50 dark:bg-gray-900/40 rounded p-2">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400">Próx. KM</span>
                            <span class="font-semibold tracking-tight">{{ $status['next_km'] ? number_format($status['next_km'],0,'','.') : '—' }}</span>
                        </div>
                        <div class="flex flex-col bg-gray-50 dark:bg-gray-900/40 rounded p-2">
                            <span class="text-[10px] text-gray-500 dark:text-gray-400">Próx. Data</span>
                            <span class="font-semibold tracking-tight">{{ $status['next_date'] ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="space-y-3 mt-4">
                        @if(!is_null($status['km_progress']))
                            <div>
                                <div class="flex justify-between text-[10px] mb-1 text-gray-500 dark:text-gray-400"><span>Progresso KM</span><span>{{ $status['km_progress'] }}%</span></div>
                                <div class="h-2 rounded bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                    <div class="h-2 {{ $status['color'] }} transition-all" style="width: {{ $status['km_progress'] }}%"></div>
                                </div>
                            </div>
                        @endif
                        @if(!is_null($status['days_progress']))
                            <div>
                                <div class="flex justify-between text-[10px] mb-1 text-gray-500 dark:text-gray-400"><span>Progresso Dias</span><span>{{ $status['days_progress'] }}%</span></div>
                                <div class="h-2 rounded bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                    <div class="h-2 {{ $status['color'] }} transition-all" style="width: {{ $status['days_progress'] }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div x-show="openForm === {{ $vehicle->id }}" x-transition class="mt-5 border-t pt-4 space-y-3">
                        <h5 class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Registrar Troca</h5>
                        <form method="POST" action="{{ route('oil.logs.store') }}" class="space-y-3 text-[11px]">
                            @csrf
                            <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}" />
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex flex-col gap-1">Produto
                                    <select name="oil_product_id" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 text-xs">
                                        <option value="">-- Selecionar --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}">{{ $p->display_name }} @if($p->stock_quantity !== null) ({{ $p->stock_quantity }}) @endif</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="flex flex-col gap-1">Data
                                    <input type="date" name="change_date" value="{{ date('Y-m-d') }}" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200" required />
                                </label>
                                <label class="flex flex-col gap-1">Odômetro (KM)
                                    <input type="number" name="odometer_km" value="{{ $vehicle->current_km }}" min="0" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200" required />
                                </label>
                                <label class="flex flex-col gap-1">Quilometragem da Próxima Troca (KM)
                                    <input type="number" name="next_km" value="{{ $status['next_km'] }}" min="0" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200" required />
                                </label>
                            </div>
                            <div class="flex flex-col gap-1">
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 shadow">Registrar Troca</button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-10">
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Nenhum veículo encontrado.</p>
                </div>
            @endforelse
        </div>
    </x-page-container>
</x-app-layout>
