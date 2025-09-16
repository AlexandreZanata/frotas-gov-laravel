<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Histórico de Alterações (Auditoria)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Usuário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ação</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Registro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Detalhes</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($logs as $log)
                            <tr>
                                <td class="px-6 py-4">{{ $log->created_at }}</td>
                                <td class="px-6 py-4">{{ $log->user->name }}</td>
                                <td class="px-6 py-4">{{ ucfirst($log->action) }}</td>
                                <td class="px-6 py-4">{{ $log->table_name }} (ID: {{ $log->record_id }})</td>
                                <td class="px-6 py-4 text-xs">
                                    @if($log->old_value)
                                        <strong>Antes:</strong> <pre class="whitespace-pre-wrap">{{ json_encode($log->old_value, JSON_PRETTY_PRINT) }}</pre>
                                    @endif
                                    @if($log->new_value)
                                        <strong>Depois:</strong> <pre class="whitespace-pre-wrap">{{ json_encode($log->new_value, JSON_PRETTY_PRINT) }}</pre>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $logs->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
