{{-- resources/views/auth/forgot-password.blade.php --}}
<x-guest-layout>
    <div class="flex flex-wrap">
        <div class="w-full md:w-1/2 flex flex-col justify-center items-center p-12 bg-gradient-to-br from-blue-600 to-blue-800 dark:from-blue-800 dark:to-blue-900 text-white text-center min-h-[250px] md:min-h-[500px]">
            <div class="max-w-sm">
                <h2 class="text-3xl font-bold mb-4">Esqueceu sua senha?</h2>
                <p class="text-blue-100 opacity-90">Não se preocupe. Informe seu e-mail que enviaremos um link para você cadastrar uma nova senha.</p>
            </div>
        </div>

        <div class="w-full md:w-1/2 flex flex-col justify-center p-8 sm:p-12">
            <div class="w-full max-w-md mx-auto">
                <div class="text-center mb-6">
                    <a href="/">
                        <x-application-logo class="w-40 h-20 mx-auto" />
                    </a>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-4">
                        {{ __('Basta nos informar seu endereço de e-mail e nós lhe enviaremos um link de redefinição de senha que permitirá que você escolha uma nova.') }}
                    </p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="seu@email.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button class="w-full text-center">
                            <span class="w-full">{{ __('Enviar Link de Redefinição') }}</span>
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
