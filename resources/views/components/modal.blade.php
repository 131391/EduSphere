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
            if (typeof $ !== 'undefined') {
                $el.querySelectorAll('select.select2-hidden-accessible').forEach(select => {
                    $(select).val('').trigger('change');
                });
            }
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
        <div class="absolute inset-0 modal-backdrop-premium"></div>
    </div>

    <!-- Modal -->
    <div x-show="show" class="mb-6 bg-white rounded-3xl shadow-2xl editorial-shadow transform transition-all sm:w-full sm:mx-auto {{ $maxWidthClass }} mt-12 flex flex-col"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        
        @if(isset($alpineTitle) || (isset($title) && !empty($title)))
        <div class="modal-header-premium !rounded-t-3xl px-10 py-6">
            <h3 class="modal-title-premium text-xl">
                @if(isset($title) && !empty($title))
                    {{ $title }}
                @elseif(isset($alpineTitle))
                    <span x-text="{{ $alpineTitle }}"></span>
                @endif
            </h3>
            <button x-on:click="show = false" class="w-10 h-10 flex items-center justify-center bg-white/10 hover:bg-white/20 rounded-full text-white transition-all focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        <div class="px-10 py-8 modal-premium-content">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <div class="modal-footer-premium px-10 py-6 !rounded-b-3xl">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
