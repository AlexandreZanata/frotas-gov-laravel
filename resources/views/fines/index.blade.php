<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Multas" icon="fas fa-gavel">
            @can('create', App\Models\Fine::class)
                <a href="{{ route('fines.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold shadow-sm transition-all duration-200 hover:scale-105">
                    <i class="fas fa-plus"></i>
                    Nova Multa
                </a>
            @endcan
            <a href="{{ route('fines.index',['q'=>request('q'),'status'=>'pago']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-sm font-semibold transition-colors duration-200">
                <i class="fas fa-check-circle"></i>
                Pagas
            </a>
            <a href="{{ route('fines.index',['q'=>request('q'),'status'=>'aguardando_pagamento']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white rounded-lg text-sm font-semibold transition-colors duration-200">
                <i class="fas fa-clock"></i>
                Aguardando
            </a>
        </x-page-header>
    </x-slot>

    <x-page-container>
        <!-- Filtros e Busca -->
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <form method="get" class="flex gap-3 w-full md:w-auto">
                <div class="relative flex-1 md:w-80">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text"
                               name="q"
                               value="{{ $search }}"
                               placeholder="Buscar: Auto, Veículo, Condutor"
                               class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm text-gray-800 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors" />
                    </div>
                </div>
                <button class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition-colors duration-200">
                    <i class="fas fa-filter"></i>
                    Filtrar
                </button>
                @if($search)
                    <a href="{{ route('fines.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-3 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors duration-200">
                        <i class="fas fa-times"></i>
                        Limpar
                    </a>
                @endif
            </form>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium">
                    <i class="fas fa-list mr-1"></i>
                    Total: {{ $fines->total() }}
                </span>
            </div>
        </div>

        <!-- Alertas -->
        @if(session('success'))
            <x-alert-messages type="success" :message="session('success')" />
        @endif

        <!-- Tabela de Multas -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <i class="fas fa-hashtag mr-2"></i>ID
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <i class="fas fa-ticket mr-2"></i>Auto
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <i class="fas fa-car mr-2"></i>Veículo
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <i class="fas fa-user mr-2"></i>Condutor
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <i class="fas fa-circle mr-2"></i>Status
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <i class="fas fa-dollar-sign mr-2"></i>Valor
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                            <i class="fas fa-cog mr-2"></i>Ações
                        </th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($fines as $fine)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition-colors duration-150">
                            <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-gray-200">
                                #{{ $fine->id }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $fine->auto_number }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                @if($fine->vehicle)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-purple-600 rounded flex items-center justify-center text-white text-xs font-bold">
                                            {{ substr($fine->vehicle->plate, -1) }}
                                        </div>
                                        {{ $fine->vehicle->plate }}
                                    </div>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                @if($fine->driver)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-gradient-to-br from-amber-500 to-orange-600 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                            {{ substr($fine->driver->name, 0, 1) }}
                                        </div>
                                        {{ $fine->driver->name }}
                                    </div>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <x-fine-status-badge :status="$fine->status" />
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900 dark:text-gray-100">
                                R$ {{ number_format($fine->total_amount, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-right space-x-4">
                                <a href="{{ route('fines.show', $fine) }}"
                                   class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200">
                                    <i class="fas fa-eye text-xs"></i>
                                    Ver
                                </a>
                                @can('update', $fine)
                                    <a href="{{ route('fines.edit', $fine) }}"
                                       class="inline-flex items-center gap-1 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 transition-colors duration-200">
                                        <i class="fas fa-edit text-xs"></i>
                                        Editar
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-3"></i>
                                    <p class="text-sm font-medium">Nenhuma multa encontrada</p>
                                    <p class="text-xs mt-1">Tente ajustar os filtros de busca</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginação -->
        <div class="mt-6">
            {{ $fines->links() }}
        </div>
    </x-page-container>
</x-app-layout>
