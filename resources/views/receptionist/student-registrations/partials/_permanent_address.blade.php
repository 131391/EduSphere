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
                <input type="text" name="permanent_address" value="{{ old('permanent_address', $studentRegistration->permanent_address ?? '') }}" required placeholder="Enter Address"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Country <span class="text-red-500">*</span>
                </label>
                <input type="text" name="permanent_country" value="{{ old('permanent_country', $studentRegistration->permanent_country ?? 'India') }}" required
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    State <span class="text-red-500">*</span>
                </label>
                <input type="text" name="permanent_state" value="{{ old('permanent_state', $studentRegistration->permanent_state ?? '') }}" required placeholder="Enter State"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    City <span class="text-red-500">*</span>
                </label>
                <input type="text" name="permanent_city" value="{{ old('permanent_city', $studentRegistration->permanent_city ?? '') }}" required placeholder="Enter City Name"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Pin <span class="text-red-500">*</span>
                </label>
                <input type="text" name="permanent_pin" value="{{ old('permanent_pin', $studentRegistration->permanent_pin ?? '') }}" required placeholder="Enter Pin"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
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
