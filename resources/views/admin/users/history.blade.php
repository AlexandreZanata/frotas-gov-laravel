<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Histórico de Alterações: ') }} {{ $user->name }}
            </h2>
            <a href="{{ route('admin.users.index') }}">
                <x-secondary-button>
                    <i class="fas fa-arrow-left mr-2"></i>
                    {{ __('Voltar para a Lista') }}
                </x-secondary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ação</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Administrador</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valores Antigos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valores Novos</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                        @foreach ($logs as $log)
                            <tr>
                                <td class="px-6 py-4">{{ $log->action }}</td>
                                <td class="px-6 py-4">{{ $log->actor->name ?? 'Sistema' }}</td>
                                <td class="px-6 py-4">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 text-xs font-mono">
                                    @php
                                        $old = json_decode($log->old_value, true);
                                        if (isset($old['password'])) $old['password'] = '********';
                                    @endphp
                                    <pre>{{ json_encode($old, JSON_PRETTY_PRINT) }}</pre>
                                </td>
                                <td class="px-6 py-4 text-xs font-mono">
                                    @php
                                        $new = json_decode($log->new_value, true);
                                        if (isset($new['password'])) $new['password'] = '********';
                                    @endphp
                                    <pre>{{ json_encode($new, JSON_PRETTY_PRINT) }}</pre>
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
