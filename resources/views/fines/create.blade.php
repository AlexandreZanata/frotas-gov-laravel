<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Nova Multa" icon="fas fa-gavel">
            <a href="{{ route('fines.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg text-sm font-semibold transition-colors duration-200">
                <i class="fas fa-arrow-left"></i>
                Voltar para Lista
            </a>
        </x-page-header>
    </x-slot>

    <x-page-container>
        <!-- Alertas de Erro -->
        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/30">
                <div class="flex items-center gap-3 mb-2">
                    <i class="fas fa-exclamation-triangle text-red-500 text-lg"></i>
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-200">Erros no formulário</h3>
                </div>
                <ul class="text-sm text-red-700 dark:text-red-300 space-y-1 ml-2">
                    @foreach($errors->all() as $error)
                        <li class="flex items-center gap-2">
                            <i class="fas fa-circle text-[8px]"></i>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('fines.store') }}" class="space-y-1 max-w-6xl" x-data="fineForm()">
            @csrf

            <!-- Card Principal -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Cabeçalho do Card -->
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-orange-600 rounded-lg flex items-center justify-center text-white">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Dados da Multa</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Preencha as informações da infração</p>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo do Formulário -->
                <div class="p-6 space-y-6">
                    <!-- Auto de Infração -->
                    <div class="space-y-2" x-data="{ focused: false }">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <i class="fas fa-hashtag text-gray-400 text-xs"></i>
                            Auto de Infração
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input x-model="auto_number"
                                       @focus="focused = true"
                                       @blur="setTimeout(() => focused = false, 200)"
                                       @input.debounce.300ms="searchAuto()"
                                       name="auto_number"
                                       required
                                       class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm text-gray-800 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors"
                                       placeholder="Ex: AIT-000123" />
                            </div>

                            <!-- Sugestões -->
                            <template x-if="autoSuggestions.length && focused">
                                <div class="absolute z-30 mt-2 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg overflow-hidden">
                                    <div class="max-h-60 overflow-y-auto">
                                        <template x-for="suggestion in autoSuggestions" :key="suggestion">
                                            <button type="button"
                                                    @click="auto_number = suggestion; autoSuggestions = []"
                                                    class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 last:border-b-0 transition-colors duration-150 flex items-center gap-3">
                                                <i class="fas fa-ticket text-blue-500 text-sm"></i>
                                                <span x-text="suggestion" class="font-medium"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Número único do auto de infração</p>
                    </div>

                    <!-- Veículo -->
                    <div class="space-y-2" x-data="{ focused: false }">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <i class="fas fa-car text-gray-400 text-xs"></i>
                            Veículo
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="hidden" name="vehicle_id" :value="vehicle?.id">

                        <div class="relative">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input x-model="vehicleQuery"
                                       @focus="focused = true"
                                       @blur="setTimeout(() => focused = false, 200)"
                                       @input.debounce.300ms="searchVehicles()"
                                       placeholder="Digite a placa ou prefixo do veículo"
                                       class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm text-gray-800 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors" />
                            </div>

                            <!-- Sugestões de Veículos -->
                            <template x-if="vehicleSuggestions.length && focused">
                                <div class="absolute z-30 mt-2 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg overflow-hidden">
                                    <div class="max-h-60 overflow-y-auto">
                                        <template x-for="vehicle in vehicleSuggestions" :key="vehicle.id">
                                            <button type="button"
                                                    @click="selectVehicle(vehicle)"
                                                    class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 last:border-b-0 transition-colors duration-150">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                                                             x-text="vehicle.plate"></div>
                                                        <div>
                                                            <div class="font-medium text-gray-800 dark:text-gray-200"
                                                                 x-text="vehicle.plate"></div>
                                                            <div class="text-xs text-gray-500 dark:text-gray-400"
                                                                 x-text="vehicle.model"></div>
                                                        </div>
                                                    </div>
                                                    <i class="fas fa-check text-green-500" x-show="vehicle?.id === vehicle.id"></i>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Veículo Selecionado -->
                        <template x-if="vehicle">
                            <div class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-check-circle text-green-500"></i>
                                        <div>
                                            <span class="text-sm font-medium text-green-800 dark:text-green-200">
                                                Veículo selecionado:
                                            </span>
                                            <span class="text-sm text-green-700 dark:text-green-300 ml-1"
                                                  x-text="vehicle.plate + ' - ' + vehicle.model"></span>
                                        </div>
                                    </div>
                                    <button type="button"
                                            @click="vehicle = null; vehicleQuery = ''"
                                            class="text-green-600 hover:text-green-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <p class="text-xs text-gray-500 dark:text-gray-400">Selecione o veículo envolvido na infração</p>
                    </div>

                    <!-- Condutor -->
                    <div class="space-y-2" x-data="{ focused: false }">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <i class="fas fa-user text-gray-400 text-xs"></i>
                            Condutor
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="hidden" name="driver_id" :value="driver?.id">

                        <div class="relative">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input x-model="driverQuery"
                                       @focus="focused = true"
                                       @blur="setTimeout(() => focused = false, 200)"
                                       @input.debounce.300ms="searchDrivers()"
                                       placeholder="Digite o nome do condutor"
                                       class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm text-gray-800 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors" />
                            </div>

                            <!-- Sugestões de Condutores -->
                            <template x-if="driverSuggestions.length && focused">
                                <div class="absolute z-30 mt-2 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg overflow-hidden">
                                    <div class="max-h-60 overflow-y-auto">
                                        <template x-for="driver in driverSuggestions" :key="driver.id">
                                            <button type="button"
                                                    @click="selectDriver(driver)"
                                                    class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 last:border-b-0 transition-colors duration-150">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-orange-600 rounded-full flex items-center justify-center text-white text-xs font-bold"
                                                             x-text="driver.name.charAt(0).toUpperCase()"></div>
                                                        <div>
                                                            <div class="font-medium text-gray-800 dark:text-gray-200"
                                                                 x-text="driver.name"></div>
                                                            <div class="text-xs text-gray-500 dark:text-gray-400"
                                                                 x-text="driver.license_number || 'Sem CNH informada'"></div>
                                                        </div>
                                                    </div>
                                                    <i class="fas fa-check text-green-500" x-show="driver?.id === driver.id"></i>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Condutor Selecionado -->
                        <template x-if="driver">
                            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-check-circle text-blue-500"></i>
                                        <div>
                                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                                Condutor selecionado:
                                            </span>
                                            <span class="text-sm text-blue-700 dark:text-blue-300 ml-1"
                                                  x-text="driver.name"></span>
                                        </div>
                                    </div>
                                    <button type="button"
                                            @click="driver = null; driverQuery = ''"
                                            class="text-blue-600 hover:text-blue-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <p class="text-xs text-gray-500 dark:text-gray-400">Selecione o condutor responsável pelo veículo</p>
                    </div>

                    <!-- Observações -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <i class="fas fa-sticky-note text-gray-400 text-xs"></i>
                            Observações
                        </label>
                        <div class="relative">
                            <textarea name="notes"
                                      rows="4"
                                      class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl text-sm text-gray-800 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-colors resize-none"
                                      placeholder="Informações adicionais sobre a infração, local, circunstâncias, etc."></textarea>
                            <div class="absolute bottom-3 right-3 text-xs text-gray-400">
                                <i class="fas fa-edit"></i>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Detalhes adicionais sobre a infração (opcional)</p>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                    <i class="fas fa-info-circle"></i>
                    <span>Campos marcados com <span class="text-red-500">*</span> são obrigatórios</span>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('fines.index') }}"
                       class="px-6 py-3 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 font-medium">
                        Cancelar
                    </a>

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold shadow-sm transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        <i class="fas fa-save"></i>
                        Salvar Multa
                    </button>
                </div>
            </div>
        </form>
    </x-page-container>

    <script>
        function fineForm() {
            return {
                auto_number: '',
                autoSuggestions: [],
                vehicleQuery: '',
                vehicle: null,
                vehicleSuggestions: [],
                driverQuery: '',
                driver: null,
                driverSuggestions: [],

                searchAuto() {
                    if (!this.auto_number) {
                        this.autoSuggestions = [];
                        return;
                    }
                    fetch('{{ route('fines.search.auto-numbers') }}?q=' + encodeURIComponent(this.auto_number))
                        .then(r => r.json())
                        .then(j => this.autoSuggestions = j.data || []);
                },

                searchVehicles() {
                    if (this.vehicleQuery.length < 2) {
                        this.vehicleSuggestions = [];
                        return;
                    }
                    fetch('{{ route('fines.search.vehicles') }}?q=' + encodeURIComponent(this.vehicleQuery))
                        .then(r => r.json())
                        .then(j => this.vehicleSuggestions = j.data || []);
                },

                searchDrivers() {
                    if (this.driverQuery.length < 2) {
                        this.driverSuggestions = [];
                        return;
                    }
                    fetch('{{ route('fines.search.drivers') }}?q=' + encodeURIComponent(this.driverQuery))
                        .then(r => r.json())
                        .then(j => this.driverSuggestions = j.data || []);
                },

                selectVehicle(v) {
                    this.vehicle = v;
                    this.vehicleQuery = v.plate;
                    this.vehicleSuggestions = [];
                },

                selectDriver(d) {
                    this.driver = d;
                    this.driverQuery = d.name;
                    this.driverSuggestions = [];
                }
            }
        }
    </script>
</x-app-layout>
