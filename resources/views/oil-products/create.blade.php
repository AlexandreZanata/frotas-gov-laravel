<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Novo Produto de Óleo" icon="fas fa-oil-can">
            <a href="{{ route('oil-products.index') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-sm rounded text-gray-800 dark:text-gray-100">Voltar</a>
        </x-page-header>
    </x-slot>

    <x-page-container>
        <div class="max-w-5xl mx-auto">
            <div class="bg-white dark:bg-gray-800 shadow rounded p-6">
                <form method="POST" action="{{ route('oil-products.store') }}" class="space-y-6">
                    @csrf
                    @include('oil-products._form')
                </form>
            </div>
        </div>
    </x-page-container>
</x-app-layout>
