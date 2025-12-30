{{-- Correspondence Address --}}
<div class="mb-6" x-data="{ correspondenceExpanded: true, sameAsPermanent: false }">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold flex items-center justify-between cursor-pointer" @click="correspondenceExpanded = !correspondenceExpanded">
        <span>Correspondence Address Information</span>
        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': correspondenceExpanded }"></i>
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700" x-show="correspondenceExpanded" x-collapse>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Address <span class="text-red-500">*</span>
                </label>
                <input type="text" name="correspondence_address" value="{{ old('correspondence_address', isset($student) ? $student->correspondence_address : '') }}" placeholder="Enter Address"
                       class="w-full px-4 py-2 border {{ $errors->has('correspondence_address') ? 'border-red-500' : 'border-gray-300 dark:border-gray-600' }} rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                @error('correspondence_address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Country
                </label>
                <select name="correspondence_country_id"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                        data-location-cascade="true"
                        data-country-select="true">
                    <option value="">Select Country</option>
                    @if(isset($student) && $student->correspondence_country_id)
                        <option value="{{ $student->correspondence_country_id }}" selected>{{ $student->correspondenceCountry->name ?? 'Selected Country' }}</option>
                    @elseif(old('correspondence_country_id'))
                        <option value="{{ old('correspondence_country_id') }}" selected>Selected Country</option>
                    @endif
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    State
                </label>
                <select name="correspondence_state_id"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                        data-state-select="true"
                        data-selected="{{ old('correspondence_state_id', isset($student) ? $student->correspondence_state_id : '') }}">
                    <option value="">Select State</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    City
                </label>
                <select name="correspondence_city_id"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                        data-city-select="true"
                        data-selected="{{ old('correspondence_city_id', isset($student) ? $student->correspondence_city_id : '') }}">
                    <option value="">Select City</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Pin
                </label>
                <input type="text" name="correspondence_pin" value="{{ old('correspondence_pin', isset($student) ? $student->correspondence_pin : '') }}" placeholder="Enter Pin"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Location
                </label>
                <input type="text" name="correspondence_location" value="{{ old('correspondence_location', isset($student) ? $student->correspondence_location : '') }}" placeholder="Enter Location"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Distance From School
                </label>
                <input type="text" name="distance_from_school" value="{{ old('distance_from_school', isset($student) ? $student->distance_from_school : '') }}" placeholder="Enter Distance From School"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>
        </div>
    </div>
</div>
