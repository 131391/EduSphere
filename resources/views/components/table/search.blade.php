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
        class="w-full pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-medium placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500/20 transition-all"
    >
    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
    <button x-show="{{ $model }}.length > 0" @click="{{ $clearAction }}"
        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600" x-cloak>
        <i class="fas fa-times"></i>
    </button>
</div>
