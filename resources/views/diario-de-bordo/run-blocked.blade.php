@extends('layouts.app')
@section('header')
<h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2"><i class="fas fa-lock text-red-600"></i> Corrida Bloqueada</h2>
@endsection
@section('content')
<div class="max-w-xl mx-auto p-6 space-y-6">
    <div class="p-5 rounded bg-red-50 dark:bg-red-900/30 border border-red-300 dark:border-red-700 space-y-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 flex items-center justify-center rounded-full bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-300">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
            <div class="flex-1 space-y-2">
                <h1 class="text-lg font-semibold text-red-700 dark:text-red-200">Esta corrida foi bloqueada por um administrador</h1>
                <p class="text-sm text-red-700 dark:text-red-300 leading-relaxed">Enquanto estiver bloqueada você não poderá iniciar, alterar ou finalizar esta corrida. Aguarde o desbloqueio ou entre em contato com a administração do sistema.</p>
                <ul class="text-xs text-red-600 dark:text-red-300 space-y-1">
                    <li><strong>ID:</strong> {{ $run->id }}</li>
                    <li><strong>Status anterior:</strong> {{ $run->blocked_previous_status ?? 'Indefinido' }}</li>
                    <li><strong>Veículo:</strong> {{ $run->vehicle?->plate }} - {{ $run->vehicle?->model }}</li>
                    <li><strong>Motorista:</strong> {{ $run->driver?->name }}</li>
                    <li><strong>Bloqueada em:</strong> {{ optional($run->blocked_at)->format('d/m/Y H:i') }}</li>
                </ul>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('diario.index') }}" class="px-4 py-2 rounded bg-gray-300 dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-100">Voltar</a>
        </div>
    </div>
</div>
@endsection

