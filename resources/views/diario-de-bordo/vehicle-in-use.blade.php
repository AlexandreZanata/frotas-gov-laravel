{{-- resources/views/diario-de-bordo/vehicle-in-use.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Veículo Indisponível
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100 border-l-4 border-red-500">

                    <div class="flex items-center">
                        <svg class="w-12 h-12 text-red-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div>
                            <h3 class="text-xl font-bold text-red-700 dark:text-red-400">
                                Veículo em Uso!
                            </h3>
                            <p class="text-gray-700 dark:text-gray-300 mt-1">
                                O veículo <span class="font-semibold">{{ $vehicle->model }} ({{ $vehicle->plate }})</span> já está em uma corrida aberta.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <p class="font-semibold">Detalhes da corrida em andamento:</p>
                        <ul class="list-disc list-inside mt-2 text-gray-600 dark:text-gray-400">
                            <li><strong>Motorista:</strong> {{ $activeRun->driver->name }}</li>
                            <li><strong>Início:</strong> {{ \Carbon\Carbon::parse($activeRun->start_time)->format('d/m/Y \à\s H:i') }}</li>
                            <li><strong>Contato:</strong> {{ $activeRun->driver->phone ?? 'Não informado' }}</li>
                        </ul>
                        <p class="mt-4 text-sm">
                            Por favor, entre em contato com o motorista ou com o gestor da frota para liberar o veículo.
                        </p>
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('diario.selectVehicle') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-600 disabled:opacity-25 transition">
                            ← Voltar e escolher outro veículo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
