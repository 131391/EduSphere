{{-- Permanent Address --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold">
        Permanent Address
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Latitude
                </label>
                <input type="text" name="permanent_latitude" value="{{ old('permanent_latitude', $studentRegistration->permanent_latitude ?? '') }}" placeholder="Enter Latitude"
                       class="w-full px-4 py-2 border @error('permanent_latitude') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('permanent_latitude')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Longitude
                </label>
                <input type="text" name="permanent_longitude" value="{{ old('permanent_longitude', $studentRegistration->permanent_longitude ?? '') }}" placeholder="Enter Longitude"
                       class="w-full px-4 py-2 border @error('permanent_longitude') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('permanent_longitude')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Address
                </label>
                <textarea name="permanent_address"  rows="3" placeholder="Enter House No, Street, Landmark"
                        class="w-full px-4 py-2 border @error('permanent_address') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">{{ old('permanent_address', $studentRegistration->permanent_address ?? '') }}</textarea>
                @error('permanent_address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <x-location-selector 
                    prefix="permanent" 
                    label="Permanent" 
                    :countries="$countries"
                    :selectedCountry="old('permanent_country_id', $studentRegistration->permanent_country_id ?? '')"
                    :selectedState="old('permanent_state_id', $studentRegistration->permanent_state_id ?? '')"
                    :selectedCity="old('permanent_city_id', $studentRegistration->permanent_city_id ?? '')"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Pin
                </label>
                <input type="text" name="permanent_pin" value="{{ old('permanent_pin', $studentRegistration->permanent_pin ?? '') }}" placeholder="Enter Pin Code"
                       class="w-full px-4 py-2 border @error('permanent_pin') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('permanent_pin')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    State of Domicile
                </label>
                <input type="text" name="permanent_state_of_domicile" value="{{ old('permanent_state_of_domicile', $studentRegistration->permanent_state_of_domicile ?? '') }}" placeholder="Enter State of Domicile"
                       class="w-full px-4 py-2 border @error('permanent_state_of_domicile') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('permanent_state_of_domicile')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Railway / Airport
                </label>
                <input type="text" name="permanent_railway_airport" value="{{ old('permanent_railway_airport', $studentRegistration->permanent_railway_airport ?? '') }}" placeholder="Enter Nearest Railway Station / Airport"
                       class="w-full px-4 py-2 border @error('permanent_railway_airport') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('permanent_railway_airport')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Correspondence Add
                </label>
                <select name="permanent_correspondence_address" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Choose Same As Correspondence Address</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </div>
        </div>
    </div>
</div>
