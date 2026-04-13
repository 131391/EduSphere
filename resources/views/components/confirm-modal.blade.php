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
        
        openModal(target, title = null, message = null) {
            if (typeof target === 'function') {
                this.actionCallback = target;
                this.formToSubmit = null;
            } else {
                this.formToSubmit = target;
                this.actionCallback = null;
            }
            this.modalTitle = title || '{{ $title }}';
            this.modalMessage = message || '{{ $message }}';
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
    @open-confirm-modal.window="openModal($event.detail.callback || $event.detail.form, $event.detail.title, $event.detail.message)"
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
            class="bg-white rounded-lg shadow-2xl max-w-md w-full mx-4 overflow-hidden"
            @click.stop
        >
            <div class="modal-header-premium">
                <h3 class="modal-title-premium" x-text="modalTitle"></h3>
                <button @click="closeModal()" class="text-white opacity-80 hover:opacity-100 transition-opacity">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Icon & Message -->
            <div class="px-6 py-8 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <p class="text-gray-600 font-medium" x-text="modalMessage"></p>
            </div>

            <!-- Actions -->
            <div class="modal-footer-premium">
                <button 
                    type="button"
                    @click="closeModal()"
                    class="btn-premium-cancel"
                >
                    {{ $cancelText }}
                </button>
                <button 
                    type="button"
                    @click="confirmAction()"
                    class="btn-premium-primary !bg-red-500 hover:!bg-red-600 !shadow-red-200"
                >
                    {{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
</div>
