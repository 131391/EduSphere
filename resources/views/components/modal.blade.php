@props(['name', 'title', 'alpineTitle' => null, 'focusable' => false, 'maxWidth' => 'lg'])

@php
$maxWidthClass = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    '3xl' => 'sm:max-w-3xl',
    '4xl' => 'sm:max-w-4xl',
    '5xl' => 'sm:max-w-5xl',
    '6xl' => 'sm:max-w-6xl',
    '7xl' => 'sm:max-w-7xl',
][$maxWidth] ?? 'sm:max-w-lg';
@endphp

<div
    x-data="{
        show: false,
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // Guard against being able to focus on content hidden by state.
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.focusables().indexOf(document.activeElement) + 1] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.focusables().indexOf(document.activeElement) - 1] || this.lastFocusable() },
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            {{ $focusable ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
        } else {
            document.body.classList.remove('overflow-y-hidden');
            // Clear validation errors when modal is closed
            $el.querySelectorAll('.text-red-500, .text-red-600').forEach(el => {
                if (el.tagName === 'P' || el.tagName === 'SPAN' || el.tagName === 'DIV') {
                    el.style.display = 'none';
                }
            });
            $el.querySelectorAll('.border-red-500, .border-red-600').forEach(el => {
                el.classList.remove('border-red-500', 'border-red-600');
                el.classList.add('border-gray-300');
            });
        }
    })"
    x-on:open-modal.window="if (typeof $event.detail === 'string' && $event.detail == '{{ $name }}') { show = true } else if (typeof $event.detail === 'object' && $event.detail.name == '{{ $name }}') { show = true }"
    x-on:close-modal.window="if ($event.detail == '{{ $name }}') show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="fixed inset-0 z-[100] overflow-y-auto"
    style="display: none;"
>
    <!-- Backdrop -->
    <div x-show="show" class="fixed inset-0 transform transition-all" x-on:click="show = false" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <!-- Modal -->
    <div x-show="show" class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto {{ $maxWidthClass }} mt-20"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        
        @if(isset($title) || isset($alpineTitle) || (isset($title) && $title->isNotEmpty()))
        <div class="bg-teal-500 px-4 py-3 flex justify-between items-center">
            <h3 class="text-lg font-medium text-white">
                @if(isset($title) && $title->isNotEmpty())
                    {{ $title }}
                @elseif(isset($alpineTitle))
                    <span x-text="{{ $alpineTitle }}"></span>
                @else
                    {{ $title ?? '' }}
                @endif
            </h3>
            <button x-on:click="show = false" class="text-white hover:text-gray-200 focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        <div class="px-4 py-5 sm:p-6">
            {{ $slot }}
        </div>
    </div>
</div>
