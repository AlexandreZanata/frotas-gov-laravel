<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Editar Pneu" icon="fas fa-circle">
            <a href="{{ route('tires.index') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-sm rounded text-gray-800 dark:text-gray-100">Voltar</a>
        </x-page-header>
    </x-slot>

    <x-page-container>
        <div class="max-w-5xl mx-auto p-4 space-y-8"> {{-- conteúdo original --}}
            @if(session('success'))
                <div class="p-3 rounded bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-3 rounded bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 text-sm">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <form method="POST" action="{{ route('tires.update', $tire) }}" class="space-y-6 bg-white dark:bg-gray-800 p-4 rounded shadow">
                        @csrf
                        @method('PUT')
                        @include('tires._form', ['tire' => $tire])
                    </form>
                </div>
                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 p-4 rounded shadow space-y-2">
                        <h2 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2 text-sm uppercase tracking-wide"><i class="fas fa-info-circle text-indigo-500"></i> Status do Pneu</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Status atual: <span class="font-medium">{{ ucfirst(str_replace('_',' ',$tire->status)) }}</span></p>
                        @if($tire->life_usage_percent !== null)
                            <p class="text-sm text-gray-600 dark:text-gray-300">Uso estimado: <span class="font-medium">{{ $tire->life_usage_percent }}%</span></p>
                        @endif
                        @if($tire->current_vehicle_id)
                            <p class="text-sm text-gray-600 dark:text-gray-300">Instalado em: <span class="font-medium">{{ $tire->vehicle?->plate ?? $tire->vehicle?->prefix }}</span> ({{ $tire->position }})</p>
                        @else
                            <p class="text-sm text-gray-600 dark:text-gray-300">Local: <span class="font-medium">{{ $tire->status==='recap_out' ? 'Em recapagem' : 'Estoque' }}</span></p>
                        @endif
                    </div>

                    @can('action',$tire)
                        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow space-y-4">
                            <h2 class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2 text-sm uppercase tracking-wide"><i class="fas fa-recycle text-purple-500"></i> Recapagem</h2>
                            @if($tire->status !== 'recap_out')
                                <form method="POST" action="{{ route('tires.retread.send',$tire) }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Notas (opcional)</label>
                                        <textarea name="notes" rows="2" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-sm text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Observações sobre o envio..."></textarea>
                                    </div>
                                    <button class="w-full px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded text-xs font-medium">Enviar para Recapagem</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('tires.retread.receive',$tire) }}" class="space-y-3">
                                    @csrf
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Novo Sulco (mm)</label>
                                            <input type="number" step="0.01" name="new_tread_depth_mm" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-xs text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Vida Esperada (Km)</label>
                                            <input type="number" name="expected_tread_life_km" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-xs text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Notas (opcional)</label>
                                        <textarea name="notes" rows="2" class="mt-1 w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700 text-xs text-gray-800 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Informações sobre o retorno..."></textarea>
                                    </div>
                                    <button class="w-full px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-medium">Registrar Retorno</button>
                                </form>
                            @endif
                        </div>
                    @endcan
                </div>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
