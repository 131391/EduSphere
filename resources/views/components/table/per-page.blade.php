@props([
    'options' => [10, 15, 25, 50, 100],
    'model' => 'perPage',
    'action' => 'changePerPage($event.target.value)',
    'default' => null,
])

<select
    x-model="{{ $model }}"
    @change="{{ $action }}"
    class="no-select2 h-11 pl-4 pr-10 bg-white border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500/20 transition-all appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%236B7280%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_0.75rem_center]"
>
    @foreach($options as $option)
        <option value="{{ $option }}" @if($default !== null && (int)$default === (int)$option) selected @endif>{{ $option }} Row{{ $option > 1 ? 's' : '' }}</option>
    @endforeach
</select>
