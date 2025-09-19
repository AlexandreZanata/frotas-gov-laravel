<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Veículo: ') . $vehicle->brand . ' ' . $vehicle->model }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Exibição de Erros de Validação --}}
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Opa!</strong>
                            <span class="block sm:inline">Existem erros no seu formulário.</span>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('vehicles.update', $vehicle->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="brand" :value="__('Marca')" />
                                    <x-text-input id="brand" class="block mt-1 w-full" type="text" name="brand" :value="old('brand', $vehicle->brand)" required autofocus />
                                </div>
                                <div>
                                    <x-input-label for="model" :value="__('Modelo')" />
                                    <x-text-input id="model" class="block mt-1 w-full" type="text" name="model" :value="old('model', $vehicle->model)" required />
                                </div>
                                <div>
                                    <x-input-label for="plate" :value="__('Placa')" />
                                    <x-text-input id="plate" class="block mt-1 w-full" type="text" name="plate" :value="old('plate', $vehicle->plate)" required />
                                </div>
                                <div>
                                    <x-input-label for="prefix" :value="__('Prefixo')" />
                                    <x-text-input id="prefix" class="block mt-1 w-full" type="text" name="prefix" :value="old('prefix', $vehicle->prefix)" />
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="renavam" :value="__('Renavam')" />
                                    <x-text-input id="renavam" class="block mt-1 w-full" type="text" name="renavam" :value="old('renavam', $vehicle->renavam)" required />
                                </div>
                                <div>
                                    <x-input-label for="year" :value="__('Ano')" />
                                    <x-text-input id="year" class="block mt-1 w-full" type="number" name="year" :value="old('year', $vehicle->year)" required />
                                </div>
                                <div>
                                    <x-input-label for="current_km" :value="__('KM Atual')" />
                                    <x-text-input id="current_km" class="block mt-1 w-full" type="number" name="current_km" :value="old('current_km', $vehicle->current_km)" required />
                                </div>
                                <div>
                                    <x-input-label for="avg_km_per_liter" :value="__('Consumo Médio (Km/L)')" />
                                    <x-text-input id="avg_km_per_liter" class="block mt-1 w-full" type="number" step="0.01" name="avg_km_per_liter" :value="old('avg_km_per_liter', $vehicle->avg_km_per_liter)" />
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="tank_capacity" :value="__('Capacidade do Tanque (L)')" />
                                    <x-text-input id="tank_capacity" class="block mt-1 w-full" type="number" step="0.01" name="tank_capacity" :value="old('tank_capacity', $vehicle->tank_capacity)" required />
                                </div>
                                <div>
                                    <x-input-label for="fuel_type" :value="__('Tipo de Combustível')" />
                                    <x-text-input id="fuel_type" class="block mt-1 w-full" type="text" name="fuel_type" :value="old('fuel_type', $vehicle->fuel_type)" required />
                                </div>
                                <div>
                                    <x-input-label for="category_id" :value="__('Categoria')" />
                                    <select name="category_id" id="category_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Selecione uma categoria</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $vehicle->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="current_secretariat_id" :value="__('Secretaria')" />
                                    <select name="current_secretariat_id" id="current_secretariat_id" required class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="">Selecione uma secretaria</option>
                                        @foreach($secretariats as $secretariat)
                                            <option value="{{ $secretariat->id }}" {{ old('current_secretariat_id', $vehicle->current_secretariat_id) == $secretariat->id ? 'selected' : '' }}>{{ $secretariat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select name="status" id="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="Disponível" {{ old('status', $vehicle->status) == 'Disponível' ? 'selected' : '' }}>Disponível</option>
                                    <option value="Em uso" {{ old('status', $vehicle->status) == 'Em uso' ? 'selected' : '' }}>Em uso</option>
                                    <option value="Manutenção" {{ old('status', $vehicle->status) == 'Manutenção' ? 'selected' : '' }}>Manutenção</option>
                                    <option value="Inativo" {{ old('status', $vehicle->status) == 'Inativo' ? 'selected' : '' }}>Inativo</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="document" :value="__('Novo Documento (Opcional)')" />
                                <input id="document" name="document" type="file" class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                                @if($vehicle->document_path)
                                    <p class="text-sm mt-2">Documento atual: <a href="{{ asset('storage/' . $vehicle->document_path) }}" target="_blank" class="text-indigo-400 hover:text-indigo-600">Ver documento</a></p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-secondary-button onclick="window.history.back();" class="mr-4">
                                {{ __('Cancelar') }}
                            </x-secondary-button>
                            <x-primary-button>
                                {{ __('Atualizar Veículo') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
