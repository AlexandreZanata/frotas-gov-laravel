<div
    x-data="{
        show: false,
        targetFormId: null,
        userName: '',
        openModal(event) {
            this.targetFormId = event.detail.targetFormId;
            this.userName = event.detail.userName;
            this.show = true;
        },
        submitForm(withBackup) {
            const form = document.getElementById(this.targetFormId);
            if (form) {
                form.querySelector('input[name=backup]').value = withBackup ? 'true' : 'false';
                form.submit();
            }
            this.show = false;
        }
    }"
    @open-delete-user-modal.window="openModal($event)"
    x-show="show"
    x-on:keydown.escape.window="show = false"
    style="display: none;"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title" role="dialog" aria-modal="true"
>
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm"></div>

    <div x-show="show" x-transition
         class="relative flex items-center justify-center min-h-screen p-4">
        <div @click.outside="show = false"
             class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-6 w-full max-w-lg text-left border border-gray-200 dark:border-gray-700">

            <div class="flex items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-500" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="modal-title">
                        Confirmar Exclusão de Usuário
                    </h3>
                    <div class="mt-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Atenção! Esta ação é irreversível e excluirá o usuário <span class="font-medium text-red-600 dark:text-red-400" x-text="userName"></span> junto com TODOS os seus registros associados.
                        </p>
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                            Você deseja criar um backup em PDF dos dados antes de prosseguir?
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 sm:mt-5 sm:flex sm:flex-row-reverse space-y-3 sm:space-y-0">
                <button @click="submitForm(true)" type="button" class="w-full inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-red-600 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200 sm:ml-3 sm:w-auto">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Salvar Backup e Excluir
                </button>
                <button @click="submitForm(false)" type="button" class="w-full inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-amber-500 text-sm font-medium text-white hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 transition-colors duration-200 sm:mt-0 sm:w-auto">
                    Excluir Sem Backup
                </button>
                <button @click="show = false" type="button" class="w-full inline-flex justify-center items-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2.5 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200 sm:mt-0 sm:w-auto">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
