@props([
    'prefix' => '',
    'selectedCountry' => null,
    'selectedState' => null,
    'selectedCity' => null,
    'label' => '',
    'countries' => [],
    'states' => [],
    'cities' => [],
    'required' => true
])

@php
    $countryName = $prefix ? "{$prefix}_country_id" : "country_id";
    $stateName = $prefix ? "{$prefix}_state_id" : "state_id";
    $cityName = $prefix ? "{$prefix}_city_id" : "city_id";
    
    $countryIdAttr = $prefix ? "{$prefix}-country-select" : "country-select";
    $stateIdAttr = $prefix ? "{$prefix}-state-select" : "state-select";
    $cityIdAttr = $prefix ? "{$prefix}-city-select" : "city-select";
    
    $baseClasses = "w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-500 dark:disabled:text-gray-400 disabled:cursor-not-allowed";
    $normalBorder = "border-gray-300 dark:border-gray-600";
    $errorBorder = "border-red-500";

    $countryIds = collect($countries)->pluck('id')->map(fn ($id) => (string) $id)->all();
    $selectedCountry = $selectedCountry !== null && in_array((string) $selectedCountry, $countryIds, true)
        ? $selectedCountry
        : null;

    if (!$selectedCountry) {
        $india = collect($countries)->firstWhere('name', 'India');
        $selectedCountry = $india['id'] ?? null;
    }

    $stateIds = collect($states)->pluck('id')->map(fn ($id) => (string) $id)->all();
    $selectedState = $selectedState !== null && in_array((string) $selectedState, $stateIds, true)
        ? $selectedState
        : null;

    $cityIds = collect($cities)->pluck('id')->map(fn ($id) => (string) $id)->all();
    $selectedCity = $selectedCity !== null && in_array((string) $selectedCity, $cityIds, true)
        ? $selectedCity
        : null;
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
        <select
            name="{{ $countryName }}"
            id="{{ $countryIdAttr }}"
            data-location-cascade="true"
            data-selected="{{ $selectedCountry }}"
            data-placeholder="Select Country"
            class="{{ $baseClasses }} @error($countryName) {{ $errorBorder }} @else {{ $normalBorder }} @enderror country-select"
        >
            <option value="">Select Country</option>
            @foreach($countries as $country)
                <option value="{{ $country['id'] }}" {{ $selectedCountry == $country['id'] ? 'selected' : '' }}>
                    {{ $country['name'] }}
                </option>
            @endforeach
        </select>
        @error($countryName)
            <p class="modal-error-message">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="{{ $stateIdAttr }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label ? $label . ' ' : '' }}State @if($required)<span class="text-red-500">*</span>@endif
        </label>
        <select
            name="{{ $stateName }}"
            id="{{ $stateIdAttr }}"
            data-state-select
            data-selected="{{ $selectedState }}"
            data-placeholder="Select State"
            class="{{ $baseClasses }} @error($stateName) {{ $errorBorder }} @else {{ $normalBorder }} @enderror state-select"
            {{ !$selectedCountry ? 'disabled' : '' }}
        >
            <option value="">Select State</option>
            @foreach($states as $state)
                <option value="{{ $state['id'] }}" {{ $selectedState == $state['id'] ? 'selected' : '' }}>
                    {{ $state['name'] }}
                </option>
            @endforeach
        </select>
        @error($stateName)
            <p class="modal-error-message">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="{{ $cityIdAttr }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label ? $label . ' ' : '' }}City @if($required)<span class="text-red-500">*</span>@endif
        </label>
        <select
            name="{{ $cityName }}"
            id="{{ $cityIdAttr }}"
            data-city-select
            data-selected="{{ $selectedCity }}"
            data-placeholder="Select City"
            class="{{ $baseClasses }} @error($cityName) {{ $errorBorder }} @else {{ $normalBorder }} @enderror city-select"
            {{ !$selectedState ? 'disabled' : '' }}
        >
            <option value="">Select City</option>
            @foreach($cities as $city)
                <option value="{{ $city['id'] }}" {{ $selectedCity == $city['id'] ? 'selected' : '' }}>
                    {{ $city['name'] }}
                </option>
            @endforeach
        </select>
        @error($cityName)
            <p class="modal-error-message">{{ $message }}</p>
        @enderror
    </div>
</div>
