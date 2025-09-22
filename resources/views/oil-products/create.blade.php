<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Novo Produto de Óleo</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded p-6">
            <form method="POST" action="{{ route('oil-products.store') }}" class="space-y-6">
                @include('oil-products._form')
            </form>
        </div>
    </div>
</x-app-layout>

