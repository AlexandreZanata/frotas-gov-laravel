{{-- resources/views/diario-de-bordo/start-run.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Diário de Bordo - Etapa 3 de 4
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('diario.startRun', $run) }}">
                    @csrf
                    @method('PATCH') {{-- <-- 1. Adiciona o método correto --}}
                    <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                            Iniciar Corrida com {{ $vehicle->model }} ({{ $vehicle->plate }})
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Confirme o KM atual e informe o destino da sua viagem.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="start_km" value="KM Atual do Veículo" />
                                <x-text-input id="start_km" class="block mt-1 w-full" type="number" name="start_km" :value="$start_km" required />
                                <x-input-error :messages="$errors->get('start_km')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="destination" value="Destino da Viagem" />
                                <x-text-input id="destination" class="block mt-1 w-full" type="text" name="destination" :value="old('destination')" required placeholder="Ex: Secretaria de Obras" />
                                <x-input-error :messages="$errors->get('destination')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-6">
                        <a href="{{ route('diario.checklist', $vehicle) }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">
                            ← Voltar ao Checklist
                        </a>
                        <x-primary-button type="submit">
                            {{ __('Iniciar Corrida') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
