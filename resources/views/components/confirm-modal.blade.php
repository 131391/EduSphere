@props([
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'confirmText' => 'OK',
    'cancelText' => 'Cancel',
])

<div 
    x-data="{ 
        show: false, 
        formToSubmit: null,
        actionCallback: null,
        modalTitle: '{{ $title }}',
        modalMessage: '{{ $message }}',
        modalConfirmText: '{{ $confirmText }}',
        
        openModal(target, title = null, message = null, confirmText = null) {
            if (typeof target === 'function') {
                this.actionCallback = target;
                this.formToSubmit = null;
            } else {
                this.formToSubmit = target;
                this.actionCallback = null;
            }
            this.modalTitle = title || '{{ $title }}';
            this.modalMessage = message || '{{ $message }}';
            this.modalConfirmText = confirmText || '{{ $confirmText }}';
            this.show = true;
        },
        
        confirmAction() {
            if (this.actionCallback) {
                this.actionCallback();
            } else if (this.formToSubmit) {
                this.formToSubmit.submit();
            }
            this.closeModal();
        },
        
        closeModal() {
            this.show = false;
            this.formToSubmit = null;
            this.actionCallback = null;
        }
    }"
    @open-confirm-modal.window="openModal($event.detail.callback || $event.detail.form, $event.detail.title, $event.detail.message, $event.detail.confirmText)"
    x-cloak
>
    <!-- Modal Backdrop -->
    <div 
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[110] flex items-center justify-center modal-backdrop-premium"
        @click.self="closeModal()"
    >
        <!-- Modal Content -->
        <div 
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 transform scale-95 translate-y-4"
            class="bg-white rounded-2xl shadow-2xl max-w-sm w-full mx-4 overflow-hidden border border-gray-100"
            @click.stop
        >
            <!-- Red danger stripe at top -->
            <div class="h-1.5 w-full bg-gradient-to-r from-red-500 to-red-600"></div>

            <!-- Header -->
            <div class="flex items-center justify-between px-6 pt-5 pb-0">
                <span class="text-sm font-semibold text-red-600 uppercase tracking-widest">Destructive Action</span>
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors rounded-full p-1 hover:bg-gray-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Icon & Message -->
            <div class="px-8 pt-6 pb-8 text-center">
                <div class="relative mx-auto flex items-center justify-center h-20 w-20 mb-6">
                    <span class="absolute inset-0 rounded-full bg-red-100 animate-ping opacity-30"></span>
                    <span class="absolute inset-1 rounded-full bg-red-50"></span>
                    <div class="relative flex items-center justify-center h-16 w-16 rounded-full bg-red-100 ring-4 ring-red-50">
                        <i class="fas fa-trash-alt text-red-500 text-2xl"></i>
                    </div>
                </div>
                <h4 class="text-gray-900 font-bold text-xl mb-2" x-text="modalTitle"></h4>
                <p class="text-gray-500 text-sm leading-relaxed" x-text="modalMessage"></p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100">
                <button 
                    type="button"
                    @click="closeModal()"
                    class="btn-premium-cancel !px-6"
                >
                    {{ $cancelText }}
                </button>
                <button 
                    type="button"
                    @click="confirmAction()"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-red-200 transition-all duration-200 hover:shadow-lg hover:shadow-red-300 active:scale-95"
                >
                    <i class="fas fa-trash-alt text-xs"></i>
                    <span x-text="modalConfirmText"></span>
                </button>
            </div>
        </div>
    </div>
</div>
