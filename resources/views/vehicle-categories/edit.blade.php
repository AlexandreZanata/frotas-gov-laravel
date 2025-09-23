<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Editar Categoria') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('vehicle-categories.update', $category) }}" method="POST" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="md:col-span-2">
                                <x-input-label for="name" :value="__('Nome da Categoria')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $category->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <x-input-label for="layout_key" :value="__('Layout (Legacy Key)')" />
                                <x-text-input id="layout_key" class="block mt-1 w-full" type="text" name="layout_key" :value="old('layout_key', $category->layout_key)" />
                                <x-input-error :messages="$errors->get('layout_key')" class="mt-2" />
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">Campo antigo opcional (pode ser deixado em branco se usar layout visual).</span>
                            </div>
                        </div>

                        <fieldset class="border border-gray-200 dark:border-gray-700 rounded p-4 space-y-4">
                            <legend class="px-2 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Intervalos de Óleo</legend>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="oil_change_km" :value="__('Troca de Óleo (KM)')" />
                                    <x-text-input id="oil_change_km" class="block mt-1 w-full" type="number" name="oil_change_km" :value="old('oil_change_km', $category->oil_change_km)" required />
                                    <x-input-error :messages="$errors->get('oil_change_km')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="oil_change_days" :value="__('Troca de Óleo (Dias)')" />
                                    <x-text-input id="oil_change_days" class="block mt-1 w-full" type="number" name="oil_change_days" :value="old('oil_change_days', $category->oil_change_days)" required />
                                    <x-input-error :messages="$errors->get('oil_change_days')" class="mt-2" />
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="border border-indigo-200 dark:border-indigo-700 rounded p-4 space-y-4">
                            <legend class="px-2 text-xs uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Manutenção de Pneus</legend>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="tire_change_km" :value="__('Revisão / Troca Pneus (KM)')" />
                                    <x-text-input id="tire_change_km" class="block mt-1 w-full" type="number" name="tire_change_km" :value="old('tire_change_km', $category->tire_change_km)" />
                                    <x-input-error :messages="$errors->get('tire_change_km')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="tire_change_days" :value="__('Revisão / Troca Pneus (Dias)')" />
                                    <x-text-input id="tire_change_days" class="block mt-1 w-full" type="number" name="tire_change_days" :value="old('tire_change_days', $category->tire_change_days)" />
                                    <x-input-error :messages="$errors->get('tire_change_days')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="tire_layout_id" :value="__('Layout Visual de Pneus')" />
                                    <select id="tire_layout_id" name="tire_layout_id" class="mt-1 block w-full rounded border-gray-300 dark:bg-gray-800 dark:border-gray-700">
                                        <option value="">— Selecionar —</option>
                                        @foreach(($tireLayouts ?? []) as $l)
                                            <option value="{{ $l->id }}" @selected(old('tire_layout_id', $category->tire_layout_id)==$l->id)>{{ $l->name }} ({{ is_array($l->positions)?count($l->positions):'0' }})</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('tire_layout_id')" class="mt-2" />
                                    <div class="mt-1 flex justify-end">
                                        <a href="{{ route('tire-layouts.index') }}" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline">Gerenciar Layouts</a>
                                    </div>
                                </div>
                            </div>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">Defina limites de quilometragem e/ou dias para gerar indicadores futuros de revisão de pneus.</p>
                        </fieldset>

                        <div class="flex items-center justify-end mt-4 gap-3">
                            <a href="{{ route('vehicle-categories.index') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 rounded text-sm text-gray-800 dark:text-gray-100">Cancelar</a>
                            <x-primary-button>{{ __('Atualizar') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
