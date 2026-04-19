@props([
    'colspan' => 8,
    'icon' => 'fas fa-inbox',
    'message' => 'No records found.',
    'showCondition' => 'rows.length === 0 && !initialLoad',
])

<template x-if="{{ $showCondition }}">
    <tr>
        <td colspan="{{ $colspan }}" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center">
                <i class="{{ $icon }} text-4xl text-gray-300 mb-4"></i>
                <p class="text-lg text-gray-500">{{ $message }}</p>
            </div>
        </td>
    </tr>
</template>
