<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Histórico de Trocas de Óleo</h2>
            <a href="{{ route('oil.maintenance') }}" class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium rounded bg-blue-600 hover:bg-blue-700 text-white shadow">
                <i class="fas fa-gauge"></i> Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="{ filtersOpen:false }">
        <form method="GET" class="mb-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow p-4 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Filtros</h3>
                <button type="button" @click="filtersOpen=!filtersOpen" class="text-xs px-2 py-1 rounded border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Toggle</button>
            </div>
            <div x-show="filtersOpen" x-collapse class="grid md:grid-cols-4 gap-4 text-xs">
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-gray-600 dark:text-gray-400">Veículo</span>
                    <select name="vehicle_id" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">Todos</option>
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}" @selected(request('vehicle_id')==$v->id)>{{ $v->plate }} - {{ $v->brand }} {{ $v->model }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-gray-600 dark:text-gray-400">Produto</span>
                    <select name="oil_product_id" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">Todos</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" @selected(request('oil_product_id')==$p->id)>{{ $p->display_name ?? ($p->name ?? $p->code ?? '#'.$p->id) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-gray-600 dark:text-gray-400">Data Inicial</span>
                    <input type="date" name="date_start" value="{{ request('date_start') }}" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200" />
                </label>
                <label class="flex flex-col gap-1">
                    <span class="font-medium text-gray-600 dark:text-gray-400">Data Final</span>
                    <input type="date" name="date_end" value="{{ request('date_end') }}" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200" />
                </label>
                <div class="md:col-span-4 flex gap-2 pt-2">
                    <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-medium">Aplicar</button>
                    <a href="{{ route('oil.logs') }}" class="px-3 py-2 text-xs rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200">Limpar</a>
                </div>
            </div>
        </form>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 uppercase tracking-wide text-[11px]">
                        <tr>
                            <th class="px-3 py-2 text-left">Data</th>
                            <th class="px-3 py-2 text-left">Veículo</th>
                            <th class="px-3 py-2 text-left">Produto</th>
                            <th class="px-3 py-2 text-right">Odômetro</th>
                            <th class="px-3 py-2 text-right">Qtd (L)</th>
                            <th class="px-3 py-2 text-right">Custo (R$)</th>
                            <th class="px-3 py-2 text-right">Próx. KM</th>
                            <th class="px-3 py-2 text-left">Usuário</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ optional($log->change_date)->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-2">
                                @if($log->vehicle)
                                    <span class="font-medium text-gray-800 dark:text-gray-100">{{ $log->vehicle->plate }}</span>
                                    <span class="block text-[10px] text-gray-500 dark:text-gray-400">{{ $log->vehicle->brand }} {{ $log->vehicle->model }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $log->product?->display_name ?? '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $log->odometer_km ? number_format($log->odometer_km,0,'','.') : '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $log->quantity_used ? number_format($log->quantity_used,1,',','.') : '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $log->total_cost ? number_format($log->total_cost,2,',','.') : '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ $log->next_change_km ? number_format($log->next_change_km,0,'','.') : '—' }}</td>
                            <td class="px-3 py-2">{{ $log->user?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-10 text-center text-gray-500 dark:text-gray-400">Nenhum registro encontrado.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($logs,'links'))
                <div class="p-3 border-t border-gray-200 dark:border-gray-700">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>

