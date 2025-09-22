<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Novo Pneu" icon="fas fa-circle">
            <a href="{{ route('tires.index') }}" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-sm rounded text-gray-800 dark:text-gray-100">Voltar</a>
        </x-page-header>
    </x-slot>

    <x-page-container>
        <div class="max-w-4xl mx-auto p-4">
            <form method="POST" action="{{ route('tires.store') }}" class="space-y-6 bg-white dark:bg-gray-800 p-4 rounded shadow">
                @csrf
                @include('tires._form')
            </form>
        </div>
    </x-page-container>
</x-app-layout>
