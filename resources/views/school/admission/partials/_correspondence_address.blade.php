{{-- Correspondence Address --}}
<div class="mb-6">
    <div class="bg-teal-500 text-white px-4 py-3 rounded-t-lg font-semibold flex items-center justify-between cursor-pointer" @click="correspondenceExpanded = !correspondenceExpanded">
        <span>Correspondence Address Information</span>
        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': correspondenceExpanded }"></i>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-b-lg border border-gray-200 dark:border-gray-700">
        {{-- Always Visible: First Row --}}
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Address <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="correspondence_address" 
                           x-model="formData.correspondence_address"
                           @input="clearError('correspondence_address')"
                           placeholder="Enter Address"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                           :class="errors.correspondence_address ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                    <template x-if="errors.correspondence_address">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.correspondence_address[0]"></p>
                    </template>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Country
                    </label>
                        <select name="correspondence_country_id"
                                x-model="formData.correspondence_country_id"
                                @change="clearError('correspondence_country_id')"
                                class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                                :class="errors.correspondence_country_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                data-location-cascade="true"
                                data-country-select="true"
                                data-selected="{{ old('correspondence_country_id', isset($student) ? $student->correspondence_country_id : '') }}">
                            <option value="">Select Country</option>
                            @if(isset($student) && $student->correspondence_country_id)
                                <option value="{{ $student->correspondence_country_id }}" selected>{{ $student->correspondenceCountry->name ?? 'Selected Country' }}</option>
                            @elseif(old('correspondence_country_id'))
                                <option value="{{ old('correspondence_country_id') }}" selected>Selected Country</option>
                            @endif
                        </select>
                        <template x-if="errors.correspondence_country_id">
                            <p class="text-red-500 text-xs mt-1" x-text="errors.correspondence_country_id[0]"></p>
                        </template>
                </div>
            </div>
        </div>

        {{-- Collapsible Details --}}
        <div x-show="correspondenceExpanded" x-collapse>
            <div class="px-6 pb-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            State
                        </label>
                        <select name="correspondence_state_id"
                                x-model="formData.correspondence_state_id"
                                @change="clearError('correspondence_state_id')"
                                class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                                :class="errors.correspondence_state_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                data-state-select="true"
                                data-selected="{{ old('correspondence_state_id', isset($student) ? $student->correspondence_state_id : '') }}">
                            <option value="">Select State</option>
                            @if(isset($student) && $student->correspondence_state_id)
                                <option value="{{ $student->correspondence_state_id }}" selected>{{ $student->correspondenceState->name ?? 'Selected State' }}</option>
                            @elseif(old('correspondence_state_id'))
                                <option value="{{ old('correspondence_state_id') }}" selected>Selected State</option>
                            @endif
                        </select>
                        <template x-if="errors.correspondence_state_id">
                            <p class="text-red-500 text-xs mt-1" x-text="errors.correspondence_state_id[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            City
                        </label>
                        <select name="correspondence_city_id"
                                x-model="formData.correspondence_city_id"
                                @change="clearError('correspondence_city_id')"
                                class="no-select2 w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white"
                                :class="errors.correspondence_city_id ? 'border-red-500' : 'border-gray-300 dark:border-gray-600'"
                                data-city-select="true"
                                data-selected="{{ old('correspondence_city_id', isset($student) ? $student->correspondence_city_id : '') }}">
                            <option value="">Select City</option>
                            @if(isset($student) && $student->correspondence_city_id)
                                <option value="{{ $student->correspondence_city_id }}" selected>{{ $student->correspondenceCity->name ?? 'Selected City' }}</option>
                            @elseif(old('correspondence_city_id'))
                                <option value="{{ old('correspondence_city_id') }}" selected>Selected City</option>
                            @endif
                        </select>
                        <template x-if="errors.correspondence_city_id">
                            <p class="text-red-500 text-xs mt-1" x-text="errors.correspondence_city_id[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Pin
                        </label>
                        <input type="text" name="correspondence_pin" x-model="formData.correspondence_pin" 
                               @input="clearError('correspondence_pin')"
                               placeholder="Enter Pin"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                               :class="errors.correspondence_pin ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.correspondence_pin">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.correspondence_pin[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Location
                        </label>
                        <input type="text" name="correspondence_location" x-model="formData.correspondence_location" 
                               @input="clearError('correspondence_location')"
                               placeholder="Enter Location"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                               :class="errors.correspondence_location ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.correspondence_location">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.correspondence_location[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Distance From School
                        </label>
                        <input type="text" name="distance_from_school" x-model="formData.distance_from_school" 
                               @input="clearError('distance_from_school')"
                               placeholder="Enter Distance From School"
                               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                               :class="errors.distance_from_school ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                        <template x-if="errors.distance_from_school">
                            <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.distance_from_school[0]"></p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
