<div wire:loading wire:target="{{ $attributes->get('wire:target') }}" class="flex items-center justify-center p-4">
    <div class="flex flex-col items-center gap-3">
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-blue-200 border-t-blue-600"></div>
        <p class="text-sm text-gray-600">{{ $message }}</p>
    </div>
</div>
