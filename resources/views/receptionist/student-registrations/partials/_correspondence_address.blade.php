{{-- Correspondence Address Information --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold">
        Correspondence Address Information
    </div>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-b-lg border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Address
                </label>
                <input type="text" name="correspondence_address" value="{{ old('correspondence_address', $studentRegistration->correspondence_address ?? '') }}" placeholder="Enter Address"
                       @input="delete errors.correspondence_address"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.correspondence_address ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.correspondence_address">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.correspondence_address[0]"></p>
                </template>
                @error('correspondence_address')
                    <p class="text-red-500 text-xs mt-1 hidden">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <x-location-selector 
                    prefix="correspondence" 
                    label="Correspondence" 
                    :countries="$countries"
                    :required="false"
                    :selectedCountry="old('correspondence_country_id', $studentRegistration->correspondence_country_id ?? 102)"
                    :selectedState="old('correspondence_state_id', $studentRegistration->correspondence_state_id ?? '')"
                    :selectedCity="old('correspondence_city_id', $studentRegistration->correspondence_city_id ?? '')"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Pin
                </label>
                <input type="text" name="correspondence_pin" value="{{ old('correspondence_pin', $studentRegistration->correspondence_pin ?? '') }}" placeholder="Enter Pin"
                       @input="delete errors.correspondence_pin"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.correspondence_pin ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.correspondence_pin">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.correspondence_pin[0]"></p>
                </template>
                @error('correspondence_pin')
                    <p class="text-red-500 text-xs mt-1 hidden">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Location
                </label>
                <input type="text" name="correspondence_location" value="{{ old('correspondence_location', $studentRegistration->correspondence_location ?? '') }}" placeholder="Enter Location"
                       @input="delete errors.correspondence_location"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.correspondence_location ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.correspondence_location">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.correspondence_location[0]"></p>
                </template>
                @error('correspondence_location')
                    <p class="text-red-500 text-xs mt-1 hidden">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Distance From School
                </label>
                <input type="text" name="distance_from_school" value="{{ old('distance_from_school', $studentRegistration->distance_from_school ?? '') }}" placeholder="Enter Distance From School"
                       @input="delete errors.distance_from_school"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.distance_from_school ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.distance_from_school">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.distance_from_school[0]"></p>
                </template>
                @error('distance_from_school')
                    <p class="text-red-500 text-xs mt-1 hidden">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    landmark
                </label>
                <input type="text" name="correspondence_landmark" value="{{ old('correspondence_landmark', $studentRegistration->correspondence_landmark ?? '') }}" placeholder="Enter landmark"
                       @input="delete errors.correspondence_landmark"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                       :class="errors.correspondence_landmark ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                <template x-if="errors.correspondence_landmark">
                    <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.correspondence_landmark[0]"></p>
                </template>
                @error('correspondence_landmark')
                    <p class="text-red-500 text-xs mt-1 hidden">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>
