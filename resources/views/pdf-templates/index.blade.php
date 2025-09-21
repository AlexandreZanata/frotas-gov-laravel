<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Modelos de PDF') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <a href="{{ route('pdf-templates.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Criar Novo Modelo</a>

                    <table class="min-w-full divide-y divide-gray-200 mt-6">
                        <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs leading-4 font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs leading-4 font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                        @foreach ($templates as $template)
                            <tr>
                                <td class="px-6 py-4 whitespace-no-wrap">{{ $template->name }}</td>
                                <td class="px-6 py-4 whitespace-no-wrap">
                                    <a href="{{ route('pdf-templates.edit', $template) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                    <a href="{{ route('pdf-templates.preview', $template) }}" class="text-green-600 hover:text-green-900 ml-4" target="_blank">Preview</a>
                                    <form action="{{ route('pdf-templates.destroy', $template) }}" method="POST" class="inline-block ml-4">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
