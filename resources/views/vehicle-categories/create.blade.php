<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Adicionar Nova Categoria') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('vehicle-categories.store') }}" method="POST" class="space-y-6">
                        @csrf

                        {{-- Nome da Categoria --}}
                        <div>
                            <x-input-label for="name" :value="__('Nome da Categoria')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        {{-- Campos Adicionais --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="layout_key" :value="__('Layout dos Pneus (Chave)')" />
                                <x-text-input id="layout_key" class="block mt-1 w-full" type="text" name="layout_key" value="car_2x2" :value="old('layout_key')" />
                                <x-input-error :messages="$errors->get('layout_key')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="oil_change_km" :value="__('Troca de Óleo (KM)')" />
                                <x-text-input id="oil_change_km" class="block mt-1 w-full" type="number" name="oil_change_km" value="10000" :value="old('oil_change_km')" required />
                                <x-input-error :messages="$errors->get('oil_change_km')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="oil_change_days" :value="__('Troca de Óleo (Dias)')" />
                                <x-text-input id="oil_change_days" class="block mt-1 w-full" type="number" name="oil_change_days" value="180" :value="old('oil_change_days')" required />
                                <x-input-error :messages="$errors->get('oil_change_days')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Novos Campos para Manutenção de Pneus --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="tire_change_km" :value="__('Troca / Revisão Pneus (KM)')" />
                                <x-text-input id="tire_change_km" class="block mt-1 w-full" type="number" name="tire_change_km" :value="old('tire_change_km')" placeholder="ex: 40000" />
                                <x-input-error :messages="$errors->get('tire_change_km')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="tire_change_days" :value="__('Troca / Revisão Pneus (Dias)')" />
                                <x-text-input id="tire_change_days" class="block mt-1 w-full" type="number" name="tire_change_days" :value="old('tire_change_days')" placeholder="ex: 365" />
                                <x-input-error :messages="$errors->get('tire_change_days')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="tire_layout_id" :value="__('Layout Visual de Pneus')" />
                                <select id="tire_layout_id" name="tire_layout_id" class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                                    <option value="">— Selecionar —</option>
                                    @foreach(($tireLayouts ?? []) as $l)
                                        <option value="{{ $l->id }}" @selected(old('tire_layout_id')==$l->id)>{{ $l->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('tire_layout_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4 gap-3">
                            <a href="{{ route('vehicle-categories.index') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 rounded text-sm">{{ __('Cancelar') }}</a>
                            <x-primary-button>
                                {{ __('Salvar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
