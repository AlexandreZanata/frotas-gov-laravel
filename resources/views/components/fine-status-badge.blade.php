@props(['status'])
@php
    $map = [
        'draft' => ['Rascunho','bg-gray-300 text-gray-800 dark:bg-gray-600 dark:text-gray-100'],
        'aguardando_pagamento' => ['Aguardando Pagamento','bg-yellow-200 text-yellow-900 dark:bg-yellow-500/20 dark:text-yellow-300'],
        'pago' => ['Pago','bg-green-600 text-white dark:bg-green-500 dark:text-white'],
        'cancelado' => ['Cancelado','bg-red-600 text-white dark:bg-red-500 dark:text-white'],
        'arquivado' => ['Arquivado','bg-indigo-600 text-white dark:bg-indigo-500 dark:text-white'],
    ];
    [$label,$classes] = $map[$status] ?? [$status,'bg-gray-400 text-white'];
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold tracking-wide {{ $classes }}">{{ $label }}</span>

