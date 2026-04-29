{{-- Step 4: Address --}}
{{-- NOTE: rendered with x-show (not x-if) in parent so the location cascade DOM stays alive --}}

{{-- Permanent Address --}}
<div class="mb-8">
    <h3 class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
        <i class="fas fa-home text-teal-500"></i> Permanent Address <span class="text-red-500 font-normal normal-case tracking-normal">*</span>
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Address <span class="text-red-500">*</span>
            </label>
            <textarea name="permanent_address" x-model="formData.permanent_address" rows="2"
                      @input="clearError('permanent_address')"
                      placeholder="House No, Street, Landmark"
                      :class="errors.permanent_address ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                      class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white"></textarea>
            <template x-if="errors.permanent_address">
                <template x-if="errors.permanent_address[0]"><p class="modal-error-message" x-text="errors.permanent_address[0]"></p></template>
            </template>
        </div>

        <div class="md:col-span-2">
            <x-location-selector
                prefix="permanent"
                label="Permanent"
                :countries="$countries"
                :selectedCountry="old('permanent_country_id', isset($student) ? ($student->permanent_country_id ?? 102) : 102)"
                :selectedState="old('permanent_state_id', isset($student) ? ($student->permanent_state_id ?? '') : '')"
                :selectedCity="old('permanent_city_id', isset($student) ? ($student->permanent_city_id ?? '') : '')"
            />
            <template x-if="errors.permanent_country_id || errors.permanent_state_id || errors.permanent_city_id">
                <p class="modal-error-message">Please select country, state and city.</p>
            </template>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Pin Code <span class="text-red-500">*</span>
            </label>
            <input type="text" name="permanent_pin" x-model="formData.permanent_pin"
                   @input="clearError('permanent_pin')"
                   placeholder="PIN / ZIP code"
                   :class="errors.permanent_pin ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                   class="w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
            <template x-if="errors.permanent_pin">
                <template x-if="errors.permanent_pin[0]"><p class="modal-error-message" x-text="errors.permanent_pin[0]"></p></template>
            </template>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">State of Domicile</label>
            <input type="text" name="state_of_domicile" x-model="formData.state_of_domicile"
                   placeholder="State of domicile"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nearest Railway / Airport</label>
            <input type="text" name="railway_airport" x-model="formData.railway_airport"
                   placeholder="Nearest station or airport"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>

    </div>
</div>

{{-- Correspondence Address (collapsible) --}}
<div>
    <button type="button" @click="correspondenceExpanded = !correspondenceExpanded"
            class="flex items-center gap-2 text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4 hover:text-teal-600 transition-colors">
        <i class="fas fa-map-marker-alt text-teal-500"></i>
        Correspondence Address
        <span class="text-xs font-normal normal-case tracking-normal text-gray-400">(optional)</span>
        <i class="fas text-xs ml-1" :class="correspondenceExpanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
    </button>

    <div x-show="correspondenceExpanded"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Address</label>
            <input type="text" name="correspondence_address" x-model="formData.correspondence_address"
                   placeholder="Correspondence address"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>

        <div class="md:col-span-2">
            <x-location-selector
                prefix="correspondence"
                label="Correspondence"
                :countries="$countries"
                :required="false"
                :selectedCountry="old('correspondence_country_id', isset($student) ? ($student->correspondence_country_id ?? 102) : 102)"
                :selectedState="old('correspondence_state_id', isset($student) ? ($student->correspondence_state_id ?? '') : '')"
                :selectedCity="old('correspondence_city_id', isset($student) ? ($student->correspondence_city_id ?? '') : '')"
            />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Pin Code</label>
            <input type="text" name="correspondence_pin" x-model="formData.correspondence_pin"
                   placeholder="PIN / ZIP code"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Location</label>
            <input type="text" name="correspondence_location" x-model="formData.correspondence_location"
                   placeholder="Area / locality"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Distance from School</label>
            <input type="text" name="distance_from_school" x-model="formData.distance_from_school"
                   placeholder="e.g. 5 km"
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-800 dark:text-white">
        </div>

    </div>
</div>
