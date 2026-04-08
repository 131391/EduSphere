<div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
    <div class="flex flex-col items-center">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Livewire Connectivity Test</h3>
        <span class="text-4xl font-bold text-indigo-600 mb-6">{{ $count }}</span>
        
        <div class="flex space-x-4">
            <button wire:click="decrement" class="px-6 py-2 bg-red-100 text-red-700 font-medium rounded-full hover:bg-red-200 transition-all">
                - Decrease
            </button>
            <button wire:click="increment" class="px-6 py-2 bg-green-100 text-green-700 font-medium rounded-full hover:bg-green-200 transition-all">
                + Increase
            </button>
        </div>
        
        <p class="mt-4 text-sm text-gray-500 italic">If the numbers update without a page refresh, Livewire is working correctly!</p>
    </div>
</div>
