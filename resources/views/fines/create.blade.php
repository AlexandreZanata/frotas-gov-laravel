<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Nova Multa" icon="fas fa-gavel">
            <a href="{{ route('fines.index') }}" class="inline-flex items-center gap-2 px-3 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded text-xs font-semibold"><i class="fas fa-arrow-left"></i> Voltar</a>
        </x-page-header>
    </x-slot>

    <x-page-container>
        @if($errors->any())
            <div class="mb-6 p-4 rounded border border-red-300/40 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-200 text-sm">
                <ul class="list-disc ml-4 space-y-1">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('fines.store') }}" class="space-y-8 max-w-3xl" x-data="fineForm()">
            @csrf
            <div class="bg-white dark:bg-gray-800 rounded shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-sm font-semibold tracking-wide uppercase text-gray-600 dark:text-gray-300 flex items-center gap-2"><i class="fas fa-file-circle-plus text-gray-400"></i> Dados da Multa</h2>
                </div>
                <div class="px-6 py-6 grid grid-cols-1 gap-6">
                    <div class="space-y-1" x-data="{focused:false}">
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Auto de Infração</label>
                        <div class="relative">
                            <input x-model="auto_number" @focus="focused=true" @blur="setTimeout(()=>focused=false,200)" @input.debounce.300ms="searchAuto()" name="auto_number" required class="w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder:text-gray-400" placeholder="Ex: AIT-000123" />
                            <template x-if="autoSuggestions.length && focused">
                                <div class="absolute z-30 mt-1 w-full rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg max-h-56 overflow-y-auto text-sm divide-y divide-gray-100 dark:divide-gray-700">
                                    <template x-for="s in autoSuggestions" :key="s">
                                        <button type="button" @click="auto_number=s;autoSuggestions=[]" class="w-full text-left px-3 py-2 hover:bg-indigo-50 dark:hover:bg-gray-700/60 text-gray-700 dark:text-gray-200" x-text="s"></button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-1" x-data="{focused:false}">
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Veículo</label>
                        <input type="hidden" name="vehicle_id" :value="vehicle?.id">
                        <div class="relative">
                            <input x-model="vehicleQuery" @focus="focused=true" @blur="setTimeout(()=>focused=false,200)" @input.debounce.300ms="searchVehicles()" placeholder="Placa / Prefixo" class="w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder:text-gray-400" />
                            <template x-if="vehicleSuggestions.length && focused">
                                <div class="absolute z-30 mt-1 w-full rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg max-h-56 overflow-y-auto text-sm divide-y divide-gray-100 dark:divide-gray-700">
                                    <template x-for="v in vehicleSuggestions" :key="v.id">
                                        <button type="button" @click="selectVehicle(v)" class="w-full text-left px-3 py-2 hover:bg-indigo-50 dark:hover:bg-gray-700/60 flex justify-between gap-4">
                                            <span x-text="v.plate"></span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400" x-text="v.model"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-show="vehicle">Selecionado: <span class="font-semibold text-gray-700 dark:text-gray-200" x-text="vehicle.plate"></span></p>
                    </div>

                    <div class="space-y-1" x-data="{focused:false}">
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Condutor</label>
                        <input type="hidden" name="driver_id" :value="driver?.id">
                        <div class="relative">
                            <input x-model="driverQuery" @focus="focused=true" @blur="setTimeout(()=>focused=false,200)" @input.debounce.300ms="searchDrivers()" placeholder="Nome do condutor" class="w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder:text-gray-400" />
                            <template x-if="driverSuggestions.length && focused">
                                <div class="absolute z-30 mt-1 w-full rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg max-h-56 overflow-y-auto text-sm divide-y divide-gray-100 dark:divide-gray-700">
                                    <template x-for="d in driverSuggestions" :key="d.id">
                                        <button type="button" @click="selectDriver(d)" class="w-full text-left px-3 py-2 hover:bg-indigo-50 dark:hover:bg-gray-700/60" x-text="d.name"></button>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400" x-show="driver">Selecionado: <span class="font-semibold text-gray-700 dark:text-gray-200" x-text="driver.name"></span></p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">Observações</label>
                        <textarea name="notes" rows="4" class="w-full rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 placeholder:text-gray-400" placeholder="Anotações adicionais"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-md bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-900"><i class="fas fa-save"></i> Salvar</button>
                <a href="{{ route('fines.index') }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Cancelar</a>
            </div>
        </form>
    </x-page-container>

    <script>
        function fineForm(){
            return {
                auto_number:'', autoSuggestions:[],
                vehicleQuery:'', vehicle:null, vehicleSuggestions:[],
                driverQuery:'', driver:null, driverSuggestions:[],
                searchAuto(){ if(!this.auto_number) { this.autoSuggestions=[]; return; } fetch('{{ route('fines.search.auto-numbers') }}?q='+encodeURIComponent(this.auto_number)).then(r=>r.json()).then(j=>this.autoSuggestions=j.data||[]); },
                searchVehicles(){ if(this.vehicleQuery.length<1){this.vehicleSuggestions=[];return;} fetch('{{ route('fines.search.vehicles') }}?q='+encodeURIComponent(this.vehicleQuery)).then(r=>r.json()).then(j=>this.vehicleSuggestions=j.data||[]); },
                searchDrivers(){ if(this.driverQuery.length<1){this.driverSuggestions=[];return;} fetch('{{ route('fines.search.drivers') }}?q='+encodeURIComponent(this.driverQuery)).then(r=>r.json()).then(j=>this.driverSuggestions=j.data||[]); },
                selectVehicle(v){ this.vehicle=v; this.vehicleQuery=v.plate; this.vehicleSuggestions=[]; },
                selectDriver(d){ this.driver=d; this.driverQuery=d.name; this.driverSuggestions=[]; }
            }
        }
    </script>
</x-app-layout>
