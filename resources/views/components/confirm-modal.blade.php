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
        modalTitle: '{{ $title }}',
        modalMessage: '{{ $message }}',
        
        openModal(form, title = null, message = null) {
            this.formToSubmit = form;
            this.modalTitle = title || '{{ $title }}';
            this.modalMessage = message || '{{ $message }}';
            this.show = true;
        },
        
        confirmAction() {
            if (this.formToSubmit) {
                this.formToSubmit.submit();
            }
            this.closeModal();
        },
        
        closeModal() {
            this.show = false;
            this.formToSubmit = null;
        }
    }"
    @open-confirm-modal.window="openModal($event.detail.form, $event.detail.title, $event.detail.message)"
    x-cloak
>
    <!-- Modal Backdrop -->
    <div 
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center"
        @click.self="closeModal()"
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
            class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4"
            @click.stop
        >
            <!-- Icon & Title -->
            <div class="px-6 py-5 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2" x-text="modalTitle"></h3>
                <p class="text-sm text-gray-600" x-text="modalMessage"></p>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex items-center justify-center gap-3">
                <button 
                    type="button"
                    @click="closeModal()"
                    class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors font-medium"
                >
                    {{ $cancelText }}
                </button>
                <button 
                    type="button"
                    @click="confirmAction()"
                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors font-medium"
                >
                    {{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
</div>
