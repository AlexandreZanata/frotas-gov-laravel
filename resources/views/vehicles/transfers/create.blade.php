<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Solicitar Transferência de Veículo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('vehicles.transfers.store') }}" id="transfer-form">
                        @csrf

                        <div class="mb-6 relative">
                            <x-input-label for="vehicle_identifier" :value="__('Digite a Placa ou Prefixo do Veículo')" />
                            <div class="flex items-center mt-1">
                                <x-text-input id="vehicle_identifier" class="block w-full" type="text" name="vehicle_identifier_search" required autocomplete="off" />
                                <x-primary-button type="button" id="search-button" class="ms-2" aria-label="Buscar Veículo">
                                    <i class="fas fa-search"></i>
                                </x-primary-button>
                            </div>
                            <div id="vehicle-search-results" class="absolute hidden w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md mt-1 z-10 max-h-60 overflow-y-auto"></div>
                            <x-input-error :messages="$errors->get('vehicle_id')" class="mt-2" />
                            <div id="vehicle-search-error" class="text-sm text-red-600 dark:text-red-400 mt-2"></div>
                        </div>

                        <div id="vehicle-details-container" class="hidden mb-6 p-4 border rounded-md bg-gray-50 dark:bg-gray-700">
                            <h3 class="font-bold text-lg mb-2">Detalhes do Veículo Selecionado</h3>
                            <p><strong>Placa:</strong> <span id="vehicle-plate"></span></p>
                            <p><strong>Prefixo:</strong> <span id="vehicle-prefix"></span></p>
                            <p><strong>Nome:</strong> <span id="vehicle-name"></span></p>
                            <p><strong>Secretaria Atual:</strong> <span id="vehicle-secretariat"></span></p>
                            <input type="hidden" name="vehicle_id" id="vehicle_id">
                        </div>

                        <div id="transfer-details-form" class="hidden">
                            <div class="mb-4">
                                <x-input-label for="destination_secretariat_id" :value="__('Secretaria de Destino')" />
                                <select id="destination_secretariat_id" name="destination_secretariat_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">Selecione uma secretaria</option>
                                    @foreach($secretariats as $secretariat)
                                        <option value="{{ $secretariat->id }}">{{ $secretariat->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('destination_secretariat_id')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label :value="__('Tipo de Transferência')" />
                                <div class="mt-2 space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="transfer_type" value="permanent" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500" checked>
                                        <span class="ms-2">Permanente</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="transfer_type" value="temporary" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ms-2">Empréstimo Temporário</span>
                                    </label>
                                </div>
                            </div>

                            <div id="temporary-fields" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <x-input-label for="start_date" :value="__('Data e Hora de Início')" />
                                    <x-text-input id="start_date" class="block mt-1 w-full" type="datetime-local" name="start_date" />
                                    <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="end_date" :value="__('Data e Hora de Fim')" />
                                    <x-text-input id="end_date" class="block mt-1 w-full" type="datetime-local" name="end_date" />
                                    <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                                </div>
                            </div>

                            <div class="mb-4">
                                <x-input-label for="request_notes" :value="__('Observações (Opcional)')" />
                                <textarea id="request_notes" name="request_notes" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <x-primary-button>
                                    {{ __('Enviar Solicitação') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const identifierInput = document.getElementById('vehicle_identifier');
                const searchButton = document.getElementById('search-button');
                const errorDiv = document.getElementById('vehicle-search-error');
                const resultsContainer = document.getElementById('vehicle-search-results');
                const vehicleDetailsContainer = document.getElementById('vehicle-details-container');
                const transferForm = document.getElementById('transfer-details-form');
                let debounceTimer;

                const searchHandler = () => {
                    clearTimeout(debounceTimer);
                    const identifier = identifierInput.value;

                    if (identifier.length < 2) {
                        resultsContainer.innerHTML = '';
                        resultsContainer.classList.add('hidden');
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        searchVehicles(identifier);
                    }, 300); // Atraso de 300ms
                };

                const searchVehicles = async (identifier) => {
                    errorDiv.textContent = '';
                    resultsContainer.innerHTML = `<div class="p-3 text-gray-500 dark:text-gray-400">Buscando...</div>`;
                    resultsContainer.classList.remove('hidden');

                    try {
                        const response = await fetch('{{ route("vehicles.transfers.search") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ identifier: identifier })
                        });

                        if (!response.ok) {
                            const errorText = await response.text();
                            console.error("Backend Error:", errorText);
                            throw new Error('Erro do servidor. Verifique os logs do console do navegador.');
                        }

                        const vehicles = await response.json();
                        displayResults(vehicles);

                    } catch (error) {
                        console.error('Fetch Error:', error);
                        errorDiv.textContent = error.message || 'Nenhum veículo encontrado ou erro na busca.';
                        resultsContainer.innerHTML = '';
                        resultsContainer.classList.add('hidden');
                    }
                }

                const displayResults = (vehicles) => {
                    resultsContainer.innerHTML = '';
                    if (vehicles.length === 0) {
                        resultsContainer.innerHTML = `<div class="p-3 text-gray-500 dark:text-gray-400">Nenhum veículo encontrado.</div>`;
                        resultsContainer.classList.remove('hidden');
                        return;
                    }

                    const ul = document.createElement('ul');
                    ul.className = 'divide-y divide-gray-200 dark:divide-gray-600';

                    vehicles.forEach(vehicle => {
                        const li = document.createElement('li');
                        li.className = 'p-3 hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer';
                        li.innerHTML = `
                        <div class="font-bold">${vehicle.plate} / ${vehicle.prefix || 'N/A'}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">${vehicle.name}</div>
                    `;

                        li.addEventListener('click', () => selectVehicle(vehicle));

                        ul.appendChild(li);
                    });

                    resultsContainer.appendChild(ul);
                    resultsContainer.classList.remove('hidden');
                }

                const selectVehicle = (vehicle) => {
                    document.getElementById('vehicle-plate').textContent = vehicle.plate;
                    document.getElementById('vehicle-prefix').textContent = vehicle.prefix || 'N/A';
                    document.getElementById('vehicle-name').textContent = vehicle.name;
                    document.getElementById('vehicle-secretariat').textContent = vehicle.secretariat;
                    document.getElementById('vehicle_id').value = vehicle.id;

                    identifierInput.value = `${vehicle.plate || ''} / ${vehicle.prefix || ''}`.replace(/ \/ $/, '');

                    resultsContainer.innerHTML = '';
                    resultsContainer.classList.add('hidden');
                    vehicleDetailsContainer.classList.remove('hidden');
                    transferForm.classList.remove('hidden');
                }

                identifierInput.addEventListener('input', searchHandler);
                searchButton.addEventListener('click', () => {
                    clearTimeout(debounceTimer);
                    searchVehicles(identifierInput.value);
                });

                document.addEventListener('click', function(event) {
                    if (!resultsContainer.contains(event.target) && event.target !== identifierInput) {
                        resultsContainer.classList.add('hidden');
                    }
                });

                // Lógica para mostrar/ocultar campos de data
                const transferTypeRadios = document.querySelectorAll('input[name="transfer_type"]');
                const temporaryFields = document.getElementById('temporary-fields');

                transferTypeRadios.forEach(radio => {
                    radio.addEventListener('change', () => {
                        if (radio.value === 'temporary') {
                            temporaryFields.classList.remove('hidden');
                            document.getElementById('start_date').required = true;
                            document.getElementById('end_date').required = true;
                        } else {
                            temporaryFields.classList.add('hidden');
                            document.getElementById('start_date').required = false;
                            document.getElementById('end_date').required = false;
                        }
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
