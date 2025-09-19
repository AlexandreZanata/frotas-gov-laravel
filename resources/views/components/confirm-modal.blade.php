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
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

    <div x-show="show" x-transition
         class="relative flex items-center justify-center min-h-screen p-4">
        <div @click.outside="closeModal()"
             class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md">

            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100" id="modal-title" x-text="title"></h3>

            <div class="mt-2">
                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="message"></p>
            </div>

            <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                <button @click="confirmAction()" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:col-start-2 sm:text-sm">
                    {{ $confirmText }}
                </button>
                <button @click="closeModal()" type="button" class="mt-3 inline-flex justify-center w-full rounded-md border border-gray-300 dark:border-gray-500 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                    {{ $cancelText }}
                </button>
            </div>
        </div>
    </div>
</div>
