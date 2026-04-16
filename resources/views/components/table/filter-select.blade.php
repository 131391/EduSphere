@props([
    'model',
    'action',
    'placeholder' => 'Filter',
    'options' => [],
])

<select
    x-model="{{ $model }}"
    @change="{{ $action }}"
    class="no-select2 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
>
    <option value="">{{ $placeholder }}</option>
    @foreach($options as $value => $label)
        <option value="{{ $value }}">{{ $label }}</option>
    @endforeach
</select>
