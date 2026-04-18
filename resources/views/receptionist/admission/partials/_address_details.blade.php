{{-- Permanent Address --}}
<div class="mb-6" x-data="{ permanentExpanded: false }">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold flex items-center justify-between cursor-pointer" @click="permanentExpanded = !permanentExpanded">
        <span>Permanent Address</span>
        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': permanentExpanded }"></i>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-b-lg border border-gray-200 dark:border-gray-700">
        {{-- Always Visible: First Row --}}
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Latitude
                    </label>
                    <input type="text" name="latitude" x-model="formData.latitude" placeholder="Enter Latitude"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Longitude
                    </label>
                    <input type="text" name="longitude" x-model="formData.longitude" placeholder="Enter Longitude"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>
        </div>

        {{-- Collapsible Details --}}
        <div x-show="permanentExpanded" x-collapse>
            <div class="px-6 pb-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Address <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="permanent_address" x-model="formData.permanent_address" @input="clearError('permanent_address')" placeholder="Enter Address"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                               :class="errors.permanent_address ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.permanent_address">
                            <p class="text-red-500 text-xs mt-1" x-text="errors.permanent_address[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Country <span class="text-red-500">*</span>
                        </label>
                        <select name="permanent_country_id"
                                x-model="formData.permanent_country_id"
                                @change="clearError('permanent_country_id')"
                                class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                                :class="errors.permanent_country_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                data-location-cascade="true"
                                data-country-select="true"
                                data-selected="{{ old('permanent_country_id', isset($student) ? $student->permanent_country_id : '') }}">
                            <option value="">Select Country</option>
                            @if(isset($student) && $student->permanent_country_id)
                                <option value="{{ $student->permanent_country_id }}" selected>{{ $student->permanentCountry->name ?? 'Selected Country' }}</option>
                            @elseif(old('permanent_country_id'))
                                <option value="{{ old('permanent_country_id') }}" selected>Selected Country</option>
                            @endif
                        </select>
                        <template x-if="errors.permanent_country_id">
                            <p class="text-red-500 text-xs mt-1" x-text="errors.permanent_country_id[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            State <span class="text-red-500">*</span>
                        </label>
                        <select name="permanent_state_id"
                                x-model="formData.permanent_state_id"
                                @change="clearError('permanent_state_id')"
                                class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                                :class="errors.permanent_state_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                data-state-select="true"
                                data-selected="{{ old('permanent_state_id', isset($student) ? $student->permanent_state_id : '') }}">
                            <option value="">Select State</option>
                            @if(isset($student) && $student->permanent_state_id)
                                <option value="{{ $student->permanent_state_id }}" selected>{{ $student->permanentState->name ?? 'Selected State' }}</option>
                            @elseif(old('permanent_state_id'))
                                <option value="{{ old('permanent_state_id') }}" selected>Selected State</option>
                            @endif
                        </select>
                        <template x-if="errors.permanent_state_id">
                            <p class="text-red-500 text-xs mt-1" x-text="errors.permanent_state_id[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            City <span class="text-red-500">*</span>
                        </label>
                        <select name="permanent_city_id"
                                x-model="formData.permanent_city_id"
                                @change="clearError('permanent_city_id')"
                                class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                                :class="errors.permanent_city_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                data-city-select="true"
                                data-selected="{{ old('permanent_city_id', isset($student) ? $student->permanent_city_id : '') }}">
                            <option value="">Select City</option>
                            @if(isset($student) && $student->permanent_city_id)
                                <option value="{{ $student->permanent_city_id }}" selected>{{ $student->permanentCity->name ?? 'Selected City' }}</option>
                            @elseif(old('permanent_city_id'))
                                <option value="{{ old('permanent_city_id') }}" selected>Selected City</option>
                            @endif
                        </select>
                        <template x-if="errors.permanent_city_id">
                            <p class="text-red-500 text-xs mt-1" x-text="errors.permanent_city_id[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Pin
                        </label>
                        <input type="text" name="permanent_pin" x-model="formData.permanent_pin" placeholder="Enter Pin"
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
                        <input type="text" name="railway_airport" value="{{ old('railway_airport', isset($student) ? $student->railway_airport : '') }}" placeholder="Enter Nearest Railway Station / Airport"
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
    </div>
</div>
