{{-- Permanent Address --}}
<div class="mb-6" x-data="{ permanentExpanded: true }">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold flex items-center justify-between cursor-pointer" @click="permanentExpanded = !permanentExpanded">
        <span>Permanent Address</span>
        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': permanentExpanded }"></i>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700" x-show="permanentExpanded" x-collapse>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Latitude
                </label>
                <input type="text" name="latitude" value="{{ old('latitude', isset($student) ? $student->latitude : '') }}" placeholder="Enter Latitude"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Longitude
                </label>
                <input type="text" name="longitude" value="{{ old('longitude', isset($student) ? $student->longitude : '') }}" placeholder="Enter Longitude"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Address <span class="text-red-500">*</span>
                </label>
                <input type="text" name="permanent_address" value="{{ old('permanent_address', isset($student) ? $student->permanent_address : '') }}" placeholder="Enter Address"
                       class="w-full px-4 py-2 border {{ $errors->has('permanent_address') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('permanent_address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Country
                </label>
                <select name="permanent_country_id"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                    @foreach(config('countries') as $id => $name)
                        <option value="{{ $id }}" {{ old('permanent_country_id', isset($student) ? $student->permanent_country_id : 1) == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    State
                </label>
                <input type="text" name="permanent_state" value="{{ old('permanent_state', isset($student) ? $student->permanent_state : '') }}" placeholder="Enter State"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    City
                </label>
                <input type="text" name="permanent_city" value="{{ old('permanent_city', isset($student) ? $student->permanent_city : '') }}" placeholder="Enter City"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Pin
                </label>
                <input type="text" name="permanent_pin" value="{{ old('permanent_pin', isset($student) ? $student->permanent_pin : '') }}" placeholder="Enter Pin"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    State of Domicile
                </label>
                <input type="text" name="state_of_domicile" value="{{ old('state_of_domicile', isset($student) ? $student->state_of_domicile : '') }}" placeholder="Enter State of Domicile"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Railway / Airport
                </label>
                <input type="text" name="railway_airport" value="{{ old('railway_airport') }}" placeholder="Enter Nearest Railway Station / Airport"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="flex items-center mt-6">
                    <input type="checkbox" name="same_as_correspondence" class="rounded border-gray-300 text-teal-500 focus:ring-teal-500">
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Same As Correspondence Address</span>
                </label>
            </div>
        </div>
    </div>
</div>
