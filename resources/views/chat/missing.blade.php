@extends('layouts.app')
@section('header')
<h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2"><i class="fas fa-comments text-blue-600"></i> Chat</h2>
@endsection
@section('content')
<div class="max-w-xl mx-auto p-6 space-y-6">
    <div class="p-5 rounded bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700">
        <h1 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 flex items-center gap-2">
            <i class="fas fa-database"></i> Módulo de Chat Não Migrado
        </h1>
        <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-300 leading-relaxed">
            As tabelas necessárias para o módulo de <strong>Chat</strong> ainda não foram criadas. Execute as migrations pendentes para habilitar o recurso.
        </p>
        <ul class="mt-3 text-xs list-disc ml-5 text-yellow-700 dark:text-yellow-300 space-y-1">
            <li>chat_conversations</li>
            <li>chat_conversation_participants</li>
            <li>chat_messages</li>
            <li>chat_message_reads</li>
        </ul>
        <div class="mt-4 bg-gray-900 text-gray-100 rounded p-3 text-xs font-mono overflow-auto">
<pre>php artisan migrate</pre>
        </div>
        <p class="mt-3 text-[11px] text-yellow-600 dark:text-yellow-400">Após migrar, atualize esta página.</p>
        <a href="{{ route('chat.index') }}" class="inline-flex mt-4 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded text-xs font-medium">
            Recarregar
        </a>
    </div>
</div>
@endsection

