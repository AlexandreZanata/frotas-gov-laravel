<tbody id="user-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
@forelse ($results as $user)
    <tr>
        {{-- Colunas da tabela (Nome, Email, etc.) --}}
        <td class="px-6 py-4 ...">{{ $user->name }}</td>
        <td class="px-6 py-4 ...">{{ $user->email }}</td>
        <td class="px-6 py-4 ...">{{ $user->role->name ?? 'N/A' }}</td>
        <td class="px-6 py-4 ...">
            @if($user->status == 'active')
                <span class="px-2 ... bg-green-100 text-green-800">Ativo</span>
            @else
                <span class="px-2 ... bg-red-100 text-red-800">Inativo</span>
            @endif
        </td>
        <td class="px-6 py-4 ... text-right space-x-2">
            {{-- Links de Ações --}}
            <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 ...">Editar</a>
            <a href="{{ route('admin.users.history', $user) }}" class="text-gray-600 ...">Histórico</a>
            {{-- Formulário de Exclusão --}}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
            Nenhum usuário encontrado.
        </td>
    </tr>
@endforelse
</tbody>

{{-- Seção de Paginação --}}
<tfoot>
<tr>
    <td colspan="5" class="p-4">
        {{ $results->links() }}
    </td>
</tr>
</tfoot>
