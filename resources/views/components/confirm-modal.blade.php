@props([
    'id' => 'confirm-modal',
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to proceed?',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'confirmClass' => 'bg-red-600 hover:bg-red-700',
    'cancelClass' => 'bg-gray-300 dark:bg-gray-600 hover:bg-gray-400 dark:hover:bg-gray-500',
])

<div 
    id="{{ $id }}"
    x-data="{ 
        show: false, 
        formToSubmit: null,
        modalTitle: '{{ $title }}',
        modalMessage: '{{ $message }}',
        confirmCallback: null,
        
        openModal(form, title = null, message = null, callback = null) {
            this.formToSubmit = form;
            this.modalTitle = title || '{{ $title }}';
            this.modalMessage = message || '{{ $message }}';
            this.confirmCallback = callback;
            this.show = true;
            // Prevent body scroll when modal is open
            document.body.style.overflow = 'hidden';
            // Ensure modal is visible and on top
            this.$nextTick(() => {
                if (this.$el) {
                    this.$el.style.display = 'block';
                    this.$el.style.zIndex = '99999';
                    // Close any other modals that might be open
                    const otherModals = document.querySelectorAll('[x-show*=\'show\'], [class*=\'modal\'], [class*=\'z-50\']');
                    otherModals.forEach(modal => {
                        if (modal !== this.$el && modal.closest('[x-data*=\'enquiryManagement\']')) {
                            // Don't close modals that are part of the same component
                            return;
                        }
                    });
                }
            });
        },
        
        confirmAction() {
            if (this.confirmCallback && typeof this.confirmCallback === 'function') {
                this.confirmCallback();
            } else if (this.formToSubmit) {
                this.formToSubmit.submit();
            }
            this.closeModal();
        },
        
        closeModal() {
            this.show = false;
            this.formToSubmit = null;
            this.confirmCallback = null;
            // Restore body scroll
            document.body.style.overflow = '';
        }
    }"
    @open-confirm-modal.window="openModal($event.detail.form, $event.detail.title, $event.detail.message, $event.detail.callback)"
    x-show="show"
    x-cloak
    style="z-index: 99999 !important;"
>
    <!-- Modal Backdrop - Full screen with very high z-index to appear above all modals -->
    <div 
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 flex items-center justify-center p-4"
        style="z-index: 99999 !important; position: fixed !important;"
        @click.self="closeModal()"
        @keydown.escape.window="closeModal()"
    >
        <!-- Modal Content -->
        <div 
            x-show="show"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 transform scale-95 translate-y-4"
            class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl max-w-md w-full mx-4 relative"
            style="z-index: 100000 !important; position: relative !important;"
            @click.stop
        >
            <!-- Icon & Title -->
            <div class="px-6 py-5 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200 mb-2" x-text="modalTitle"></h3>
                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="modalMessage"></p>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 rounded-b-lg flex items-center justify-center gap-3">
                <button 
                    type="button"
                    @click="closeModal()"
                    class="px-6 py-2 {{ $cancelClass }} text-gray-700 dark:text-gray-200 rounded-md transition-colors font-medium"
                >
                    {{ $cancelText }}
                </button>
                <button 
                    type="button"
                    @click="confirmAction()"
                    class="px-6 py-2 {{ $confirmClass }} text-white rounded-md transition-colors font-medium"
                >
                    {{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
</div>
