@props([
    'name' => 'phone',
    'value' => '',
    'placeholder' => 'Enter 10 digit mobile number',
    'id' => null,
])

@php
    $inputId = $id ?? $name;
    $errorClass = $errors->has($name) ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500';
@endphp

<div class="relative phone-input-wrapper">
    <!-- Country Code Prefix -->
    <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none z-10">
        <span class="px-3 py-2 text-gray-700 dark:text-gray-300 font-medium bg-white dark:bg-gray-600 border-r border-gray-300 dark:border-gray-500 rounded-l-md h-full flex items-center select-none">
            +91
        </span>
    </div>
    
    <!-- Phone Number Input -->
    <input 
        type="tel" 
        name="{{ $name }}" 
        id="{{ $inputId }}"
        value="{{ old($name, $value) }}" 
        placeholder="{{ $placeholder }}"
        inputmode="numeric"
        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
        {{ $attributes->merge(['class' => "w-full pl-[70px] pr-3 py-2 border $errorClass rounded-md focus:outline-none focus:ring-2 transition-all duration-200"]) }}
    >
    
    <!-- Character Counter -->
    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <span class="text-xs text-gray-400 dark:text-gray-500 font-medium" id="{{ $inputId }}_counter">0/10</span>
    </div>
</div>

@error($name)
    <p class="modal-error-message">{{ $message }}</p>
@enderror

@once
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all phone inputs
        document.querySelectorAll('.phone-input-wrapper input[type="tel"]').forEach(function(input) {
            const counter = document.getElementById(input.id + '_counter');
            
            // Update counter on input
            function updateCounter() {
                if (counter) {
                    const length = input.value.length;
                    counter.textContent = length + '/10';
                    
                    // Change color based on length
                    if (length === 10) {
                        counter.classList.remove('text-gray-400', 'text-yellow-500');
                        counter.classList.add('text-green-500');
                    } else if (length > 0) {
                        counter.classList.remove('text-gray-400', 'text-green-500');
                        counter.classList.add('text-yellow-500');
                    } else {
                        counter.classList.remove('text-yellow-500', 'text-green-500');
                        counter.classList.add('text-gray-400');
                    }
                }
            }
            
            // Initialize counter
            updateCounter();
            
            // Update on input
            input.addEventListener('input', updateCounter);
            
            // Prevent pasting non-numeric content
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedText = (e.clipboardData || window.clipboardData).getData('text');
                const numericOnly = pastedText.replace(/[^0-9]/g, '').slice(0, 10);
                input.value = numericOnly;
                updateCounter();
            });
        });
    });
</script>
@endpush
@endonce
