@props([
    'colspan' => 8,
    'icon' => 'fas fa-inbox',
    'message' => 'No records found.',
    'showCondition' => 'rows.length === 0 && !initialLoad',
])

<tr x-show="{{ $showCondition }}">
    <td colspan="{{ $colspan }}" class="px-6 py-16 text-center">
        <div class="flex flex-col items-center">
            <i class="{{ $icon }} text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
            <p class="text-gray-500 dark:text-gray-400">{{ $message }}</p>
        </div>
    </td>
</tr>
