@props([
    'placeholder' => 'Search...',
    'model' => 'search',
    'action' => 'handleSearch()',
    'clearAction' => 'clearSearch()',
])

<div class="relative flex-1 max-w-md">
    <input
        type="text"
        x-model="{{ $model }}"
        @input="{{ $action }}"
        @keyup.escape="{{ $clearAction }}"
        placeholder="{{ $placeholder }}"
        class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
    >
    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
    <button x-show="{{ $model }}.length > 0" @click="{{ $clearAction }}"
        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" x-cloak>
        <i class="fas fa-times"></i>
    </button>
</div>
