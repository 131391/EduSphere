@props([
    'prefix' => '',
    'selectedCountry' => null,
    'selectedState' => null,
    'selectedCity' => null,
    'label' => '',
    'countries' => [],
    'required' => true
])

@php
    $selectedCountry = $selectedCountry ?: 102;

    $countryName = $prefix ? "{$prefix}_country_id" : "country_id";
    $stateName = $prefix ? "{$prefix}_state_id" : "state_id";
    $cityName = $prefix ? "{$prefix}_city_id" : "city_id";
    
    $countryIdAttr = $prefix ? "{$prefix}-country-select" : "country-select";
    $stateIdAttr = $prefix ? "{$prefix}-state-select" : "state-select";
    $cityIdAttr = $prefix ? "{$prefix}-city-select" : "city-select";
    
    $baseClasses = "w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white disabled:bg-gray-100 disabled:cursor-not-allowed";
    $normalBorder = "border-gray-300 dark:border-gray-600";
    $errorBorder = "border-red-500";
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 location-selector-container" 
     data-prefix="{{ $prefix }}" 
     data-selected-country="{{ $selectedCountry }}" 
     data-selected-state="{{ $selectedState }}" 
     data-selected-city="{{ $selectedCity }}">
    
    <div>
        <label for="{{ $countryIdAttr }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label ? $label . ' ' : '' }}Country @if($required)<span class="text-red-500">*</span>@endif
        </label>
        <select name="{{ $countryName }}" id="{{ $countryIdAttr }}" class="{{ $baseClasses }} @error($countryName) {{ $errorBorder }} @else {{ $normalBorder }} @enderror country-select">
            <option value="">Select Country</option>
            @foreach($countries as $country)
                <option value="{{ $country['id'] }}" {{ $selectedCountry == $country['id'] ? 'selected' : '' }}>
                    {{ $country['name'] }}
                </option>
            @endforeach
        </select>
        @error($countryName)
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="{{ $stateIdAttr }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label ? $label . ' ' : '' }}State @if($required)<span class="text-red-500">*</span>@endif
        </label>
        <select name="{{ $stateName }}" id="{{ $stateIdAttr }}" class="{{ $baseClasses }} @error($stateName) {{ $errorBorder }} @else {{ $normalBorder }} @enderror state-select" {{ !$selectedCountry ? 'disabled' : '' }}>
            <option value="">Select State</option>
        </select>
        @error($stateName)
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="{{ $cityIdAttr }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label ? $label . ' ' : '' }}City @if($required)<span class="text-red-500">*</span>@endif
        </label>
        <select name="{{ $cityName }}" id="{{ $cityIdAttr }}" class="{{ $baseClasses }} @error($cityName) {{ $errorBorder }} @else {{ $normalBorder }} @enderror city-select" {{ !$selectedState ? 'disabled' : '' }}>
            <option value="">Select City</option>
        </select>
        @error($cityName)
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

@once
    @push('scripts')
        <script src="{{ asset('js/location-selector.js') }}"></script>
    @endpush
@endonce
