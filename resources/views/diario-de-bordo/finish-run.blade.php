{{-- resources/views/diario-de-bordo/finish-run.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Diário de Bordo - Finalizar Corrida
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('diario.updateRun', $run) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

                    <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                            Finalizando a corrida com {{ $run->vehicle->model }} ({{ $run->vehicle->plate }})
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Preencha os dados abaixo para concluir a sua viagem.
                        </p>

                        {{-- Dados da Corrida --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="end_km" value="KM Final do Veículo" />
                                <x-text-input id="end_km" class="block mt-1 w-full" type="number" name="end_km" :value="old('end_km', $run->start_km)" required />
                                <x-input-error :messages="$errors->get('end_km')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="stop_point" value="Ponto de Parada" />
                                <x-text-input id="stop_point" class="block mt-1 w-full" type="text" name="stop_point" :value="old('stop_point')" required placeholder="Ex: Pátio da Prefeitura" />
                                <x-input-error :messages="$errors->get('stop_point')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Seção de Abastecimento com Alpine.js --}}
                        <div x-data="{ fueling: false, manual: false }" class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="fueling_added" value="1" x-model="fueling" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-gray-800 dark:text-gray-200">Adicionar Abastecimento</span>
                            </label>

                            <div x-show="fueling" x-transition class="mt-4 space-y-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center">
                                        <input type="radio" name="is_manual" value="0" @change="manual = false" checked>
                                        <span class="ml-2">Posto Credenciado</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="is_manual" value="1" @change="manual = true">
                                        <span class="ml-2">Abastecimento Manual</span>
                                    </label>
                                </div>

                                {{-- Campos Comuns --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <x-input-label for="fueling_km" value="KM do Abastecimento" />
                                        <x-text-input id="fueling_km" name="fueling_km" type="number" class="mt-1 block w-full"/>
                                    </div>
                                    <div>
                                        <x-input-label for="liters" value="Litros" />
                                        <x-text-input id="liters" name="liters" type="number" step="0.01" class="mt-1 block w-full"/>
                                    </div>
                                    <div>
                                        <x-input-label for="fuel_type_id" value="Combustível" />
                                        <select name="fuel_type_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                            @foreach($fuelTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Campos para Posto Credenciado --}}
                                <div x-show="!manual">
                                    <x-input-label for="gas_station_id" value="Posto de Gasolina" />
                                    <select name="gas_station_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">
                                        @foreach($gasStations as $station)
                                            <option value="{{ $station->id }}">{{ $station->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Campos para Abastecimento Manual --}}
                                <div x-show="manual" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="gas_station_name" value="Nome do Posto" />
                                        <x-text-input id="gas_station_name" name="gas_station_name" type="text" class="mt-1 block w-full"/>
                                    </div>
                                    <div>
                                        <x-input-label for="total_value" value="Valor Total (R$)" />
                                        <x-text-input id="total_value" name="total_value" type="number" step="0.01" class="mt-1 block w-full"/>
                                    </div>
                                </div>

                                {{-- Upload da Nota Fiscal --}}
                                <div>
                                    <x-input-label for="invoice_path" value="Nota Fiscal (Opcional)" />
                                    <input type="file" name="invoice_path" id="invoice_path" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="flex items-center justify-end bg-gray-50 dark:bg-gray-900 p-6">
                        <x-primary-button type="submit">
                            {{ __('Finalizar Corrida') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
