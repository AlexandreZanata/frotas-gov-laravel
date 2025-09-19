<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Criar Nova Senha Padrão') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.default-passwords.store') }}">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Nome de Identificação (Ex: Padrão 2025)')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_plain" :value="__('Senha')" />
                            <x-text-input id="password_plain" class="block mt-1 w-full" type="text" name="password_plain" required />
                            <x-input-error :messages="$errors->get('password_plain')" class="mt-2" />
                        </div>

                        <div class="block mt-4">
                            <label for="is_active" class="inline-flex items-center">
                                <input id="is_active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="is_active" checked>
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Ativo') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.default-passwords.index') }}">
                                <x-secondary-button class="ms-3">
                                    {{ __('Cancelar') }}
                                </x-secondary-button>
                            </a>
                            <x-primary-button class="ms-3">
                                {{ __('Salvar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
