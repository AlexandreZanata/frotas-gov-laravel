@forelse ($users as $user)
    <tr>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->email }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                {{ $user->role->name ?? 'N/A' }}
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
            @if($user->status == 'active')
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
            @else
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">Editar</a>
            <div x-data class="inline">
                <button type="button"
                        @click="$dispatch('open-delete-user-modal', { targetFormId: 'delete-form-{{ $user->id }}', userName: '{{ addslashes($user->name) }}' })"
                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200 cursor-pointer">
                    Excluir
                </button>
            </div>
        </td>
    </tr>
@empty
    {{-- Esta linha é importante para dar feedback quando não há resultados --}}
    <tr>
        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
            Nenhum usuário encontrado para esta busca.
        </td>
    </tr>
@endforelse
