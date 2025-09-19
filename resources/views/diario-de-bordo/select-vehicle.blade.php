<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Diário de Bordo - Etapa 1 de 4
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">

                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">
                        Escolha o Veículo
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        Digite o prefixo, placa ou modelo do veículo para iniciar uma nova corrida.
                    </p>

                    <form id="vehicle-form" method="GET" action="#">
                        <div>
                            <x-input-label for="vehicle_search" value="Prefixo, Placa ou Modelo do Veículo" />
                            <x-text-input id="vehicle_search" class="block mt-1 w-full" type="text" name="vehicle_search" required autofocus autocomplete="off" placeholder="Digite para buscar..."/>
                            {{-- Resultados da busca aparecerão aqui --}}
                            <div id="search-results" class="mt-2 border-gray-300 dark:border-gray-700 rounded-md shadow-sm hidden"></div>
                        </div>

                        {{-- Campos preenchidos automaticamente --}}
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="plate" value="Placa" />
                                <x-text-input id="plate" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="text" readonly />
                            </div>
                            <div>
                                <x-input-label for="secretariat" value="Secretaria" />
                                <x-text-input id="secretariat" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="text" readonly />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="name" value="Nome do Veículo" />
                                <x-text-input id="name" class="block mt-1 w-full bg-gray-100 dark:bg-gray-700" type="text" readonly />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <x-primary-button id="next-step-btn" disabled>
                                {{ __('Avançar para Checklist') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('vehicle_search');
                const resultsContainer = document.getElementById('search-results');
                const vehicleForm = document.getElementById('vehicle-form');
                const nextButton = document.getElementById('next-step-btn');

                const plateInput = document.getElementById('plate');
                const nameInput = document.getElementById('name');
                const secretariatInput = document.getElementById('secretariat');

                let selectedVehicleId = null;

                searchInput.addEventListener('keyup', function() {
                    const query = searchInput.value;

                    if (query.length < 2) {
                        resultsContainer.innerHTML = '';
                        resultsContainer.classList.add('hidden');
                        clearVehicleFields();
                        return;
                    }

                    fetch(`{{ route('api.vehicles.search') }}?query=${query}`)
                        .then(response => response.json())
                        .then(data => {
                            resultsContainer.innerHTML = ''; // Limpa resultados anteriores
                            resultsContainer.classList.remove('hidden'); // Mostra o container

                            if (data.length > 0) {
                                data.forEach(vehicle => {
                                    const resultItem = document.createElement('div');

                                    // NOVO: Verifica se o veículo é selecionável
                                    if (vehicle.selectable) {
                                        // Se for, cria um item clicável
                                        resultItem.innerHTML = `
                                            <div class="font-medium">${vehicle.prefix} - ${vehicle.plate}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">${vehicle.model}</div>
                                        `;
                                        resultItem.classList.add('p-3', 'cursor-pointer', 'hover:bg-gray-200', 'dark:hover:bg-gray-600', 'border-b', 'dark:border-gray-600');
                                        resultItem.addEventListener('click', () => selectVehicle(vehicle));
                                    } else {
                                        // Se não for, cria um item informativo e desabilitado
                                        resultItem.innerHTML = `
                                            <div>
                                                <span class="font-medium text-gray-400 dark:text-gray-500">${vehicle.prefix} - ${vehicle.plate}</span>
                                            </div>
                                            <div class="text-sm text-red-500 font-semibold">${vehicle.reason}</div>
                                        `;
                                        resultItem.classList.add('p-3', 'opacity-70', 'border-b', 'dark:border-gray-600');
                                    }
                                    resultsContainer.appendChild(resultItem);
                                });
                            } else {
                                // Mensagem se nenhum veículo for encontrado
                                resultsContainer.innerHTML = '<div class="p-3 text-center text-gray-500">Nenhum veículo encontrado.</div>';
                            }
                        });
                });

                function selectVehicle(vehicle) {
                    // Preenche os campos do formulário
                    searchInput.value = `${vehicle.prefix} - ${vehicle.model}`;
                    plateInput.value = vehicle.plate;
                    nameInput.value = vehicle.model;
                    secretariatInput.value = vehicle.secretariat ? vehicle.secretariat.name : 'N/A';
                    selectedVehicleId = vehicle.id;

                    // Esconde os resultados e limpa o container
                    resultsContainer.classList.add('hidden');
                    resultsContainer.innerHTML = '';

                    // Habilita o botão para avançar
                    nextButton.disabled = false;

                    // Define a URL da próxima etapa
                    const nextUrl = `{{ url('/diario-de-bordo') }}/${selectedVehicleId}/checklist`;
                    vehicleForm.action = nextUrl;
                }

                function clearVehicleFields() {
                    plateInput.value = '';
                    nameInput.value = '';
                    secretariatInput.value = '';
                    selectedVehicleId = null;
                    nextButton.disabled = true;
                    vehicleForm.action = '#';
                }
            });
        </script>
    @endpush
</x-app-layout>
