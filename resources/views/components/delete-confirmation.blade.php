<div
    x-data="{
        show: false,
        formId: null,
        message: 'Are you sure you want to delete this record?',
        confirm() {
            if (this.formId) {
                const form = document.getElementById(this.formId);
                if (form) {
                    form.submit();
                }
            }
            this.show = false;
        }
    }"
    x-on:confirm-delete.window="formId = $event.detail.formId; message = $event.detail.message || 'Are you sure you want to delete this record?'; show = true"
    x-show="show"
    class="fixed inset-0 z-[100] overflow-y-auto"
    style="display: none;"
    x-cloak
>
    <!-- Backdrop -->
    <div x-show="show" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 transform transition-all" 
         x-on:click="show = false">
        <div class="absolute inset-0 bg-gray-900 opacity-50"></div>
    </div>

    <!-- Modal -->
    <div x-show="show" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto sm:max-w-md mt-40">
        
        <div class="bg-red-600 px-4 py-3 flex justify-between items-center">
            <h3 class="text-lg font-medium text-white">Confirm Delete</h3>
            <button x-on:click="show = false" class="text-white hover:text-gray-200 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6 text-center">
            <div class="mb-4 text-red-500">
                <i class="fas fa-exclamation-triangle text-5xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-2" x-text="message"></h3>
            <p class="text-sm text-gray-500 mb-6">This action cannot be undone. All associated data will be permanently removed.</p>
            
            <div class="flex justify-center space-x-3">
                <button type="button" @click="show = false" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-6 rounded transition-colors">
                    Cancel
                </button>
                <button type="button" @click="confirm()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded shadow-md transition-all active:scale-95">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
