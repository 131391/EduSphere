<div class="flex items-center">
    @if($logo)
    <img src="{{ $logo }}" alt="{{ $school->name }}" class="w-10 h-10 rounded-full mr-3 object-cover">
    @else
    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
        <i class="fas fa-school text-blue-600"></i>
    </div>
    @endif
    <div>
        <div class="text-sm font-medium text-gray-900">{{ $school->name }}</div>
        @if($cityState)
        <div class="text-sm text-gray-500">{{ $cityState }}</div>
        @endif
    </div>
</div>

