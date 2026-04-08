@props([
    'prefix' => '',
    'selectedCountry' => null,
    'selectedState' => null,
    'selectedCity' => null,
    'label' => '',
    'countries' => []
])

@php
    $countryName = $prefix ? "{$prefix}_country_id" : "country_id";
    $stateName = $prefix ? "{$prefix}_state_id" : "state_id";
    $cityName = $prefix ? "{$prefix}_city_id" : "city_id";
    
    $countryIdAttr = $prefix ? "{$prefix}-country-select" : "country-select";
    $stateIdAttr = $prefix ? "{$prefix}-state-select" : "state-select";
    $cityIdAttr = $prefix ? "{$prefix}-city-select" : "city-select";
    
    $inputClasses = "w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white disabled:bg-gray-100 disabled:cursor-not-allowed";
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 location-selector-container" 
     data-prefix="{{ $prefix }}" 
     data-selected-country="{{ $selectedCountry }}" 
     data-selected-state="{{ $selectedState }}" 
     data-selected-city="{{ $selectedCity }}">
    
    <div>
        <label for="{{ $countryIdAttr }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label ? $label . ' ' : '' }}Country <span class="text-red-500">*</span>
        </label>
        <select name="{{ $countryName }}" id="{{ $countryIdAttr }}" class="{{ $inputClasses }} country-select" required>
            <option value="">Select Country</option>
            @foreach($countries as $country)
                <option value="{{ $country['id'] }}" {{ $selectedCountry == $country['id'] ? 'selected' : '' }}>
                    {{ $country['name'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="{{ $stateIdAttr }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label ? $label . ' ' : '' }}State <span class="text-red-500">*</span>
        </label>
        <select name="{{ $stateName }}" id="{{ $stateIdAttr }}" class="{{ $inputClasses }} state-select" required {{ !$selectedCountry ? 'disabled' : '' }}>
            <option value="">Select State</option>
        </select>
    </div>

    <div>
        <label for="{{ $cityIdAttr }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label ? $label . ' ' : '' }}City <span class="text-red-500">*</span>
        </label>
        <select name="{{ $cityName }}" id="{{ $cityIdAttr }}" class="{{ $inputClasses }} city-select" required {{ !$selectedState ? 'disabled' : '' }}>
            <option value="">Select City</option>
        </select>
    </div>
</div>

@once
    @push('scripts')
        <script src="{{ asset('js/location-selector.js') }}"></script>
    @endpush
@endonce
