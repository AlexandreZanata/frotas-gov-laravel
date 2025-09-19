@props([
    'title' => 'Confirmar Ação',
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
])

<div
    x-data="{
        show: false,
        title: '',
        message: '',
        targetFormId: null,
        onConfirm: null,
        openModal(event) {
            this.title = event.detail.title || '{{ $title }}';
            this.message = event.detail.message;
            this.targetFormId = event.detail.targetFormId;
            this.show = true;
        },
        confirmAction() {
            if (this.targetFormId) {
                const form = document.getElementById(this.targetFormId);
                if(form) form.submit();
            }
            this.closeModal();
        },
        closeModal() {
            this.show = false;
        }
    }"
    @open-confirm-modal.window="openModal($event)"
    x-show="show"
    x-on:keydown.escape.window="closeModal()"
    style="display: none;"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
>
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-70 backdrop-blur-sm"></div>

    <div x-show="show" x-transition
         class="relative flex items-center justify-center min-h-screen p-4">
        <div @click.outside="closeModal()"
             class="relative bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-6 w-full max-w-md text-left border border-gray-200 dark:border-gray-700">

            <div class="flex items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="modal-title" x-text="title"></h3>
                    <div class="mt-3">
                        <p class="text-sm text-gray-600 dark:text-gray-400" x-text="message"></p>
                    </div>
                </div>
            </div>

            <div class="mt-6 sm:mt-5 sm:flex sm:flex-row-reverse space-y-3 sm:space-y-0 sm:space-x-3">
                <button @click="confirmAction()" type="button" class="w-full inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-4 py-2.5 bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 sm:w-auto">
                    {{ $confirmText }}
                </button>
                <button @click="closeModal()" type="button" class="w-full inline-flex justify-center items-center rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2.5 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200 sm:w-auto">
                    {{ $cancelText }}
                </button>
            </div>
        </div>
    </div>
</div>
