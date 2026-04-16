@props([
    'options' => [10, 15, 25, 50, 100],
    'model' => 'perPage',
    'action' => 'changePerPage($event.target.value)'
])

<select
    x-model="{{ $model }}"
    @change="{{ $action }}"
    class="no-select2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
>
    @foreach($options as $option)
        <option value="{{ $option }}">{{ $option }}</option>
    @endforeach
</select>
