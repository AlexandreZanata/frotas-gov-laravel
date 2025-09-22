<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Editar Produto de Óleo</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded p-6">
            <form method="POST" action="{{ route('oil-products.update', $product) }}" class="space-y-6">
                @method('PUT')
                @include('oil-products._form', ['product' => $product])
            </form>
        </div>
    </div>
</x-app-layout>

