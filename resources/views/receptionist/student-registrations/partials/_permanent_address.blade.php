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
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Longitude
                </label>
                <input type="text" name="permanent_longitude" value="{{ old('permanent_longitude', $studentRegistration->permanent_longitude ?? '') }}" placeholder="Enter Longitude"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Address <span class="text-red-500">*</span>
                </label>
                <input type="text" name="permanent_address" value="{{ old('permanent_address', $studentRegistration->permanent_address ?? '') }}" placeholder="Enter Address"
                       class="w-full px-4 py-2 border {{ $errors->has('permanent_address') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('permanent_address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Country <span class="text-red-500">*</span>
                </label>
                <select name="permanent_country_id"
                        class="w-full px-4 py-2 border {{ $errors->has('permanent_country_id') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    @foreach(config('countries') as $id => $name)
                        <option value="{{ $id }}" {{ old('permanent_country_id', $studentRegistration->permanent_country_id ?? 1) == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                @error('permanent_country_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    State <span class="text-red-500">*</span>
                </label>
                <input type="text" name="permanent_state" value="{{ old('permanent_state', $studentRegistration->permanent_state ?? '') }}" placeholder="Enter State"
                       class="w-full px-4 py-2 border {{ $errors->has('permanent_state') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('permanent_state')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    City <span class="text-red-500">*</span>
                </label>
                <input type="text" name="permanent_city" value="{{ old('permanent_city', $studentRegistration->permanent_city ?? '') }}" placeholder="Enter City Name"
                       class="w-full px-4 py-2 border {{ $errors->has('permanent_city') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('permanent_city')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Pin <span class="text-red-500">*</span>
                </label>
                <input type="text" name="permanent_pin" value="{{ old('permanent_pin', $studentRegistration->permanent_pin ?? '') }}" placeholder="Enter Pin"
                       class="w-full px-4 py-2 border {{ $errors->has('permanent_pin') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('permanent_pin')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    State of Domicile
                </label>
                <input type="text" name="permanent_state_of_domicile" value="{{ old('permanent_state_of_domicile', $studentRegistration->permanent_state_of_domicile ?? '') }}" placeholder="Enter State of Domicile"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Railway / Airport
                </label>
                <input type="text" name="permanent_railway_airport" value="{{ old('permanent_railway_airport', $studentRegistration->permanent_railway_airport ?? '') }}" placeholder="Enter Nearest Railway Station / Airport"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
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
