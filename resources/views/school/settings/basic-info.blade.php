@extends('layouts.school')

@section('title', 'Basic Information - ' . $school->name)

@section('content')
<div class="w-full space-y-6 animate-in fade-in duration-500"
     x-data="basicInfoSettings()">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-1">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm font-bold uppercase tracking-wider text-gray-400">
                    <li class="inline-flex items-center">
                        <a href="{{ route('school.dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[11px] text-gray-300"></i>
                            <span>Basic Information</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Basic Information</h1>
            <p class="text-base text-gray-500 mt-1 flex items-center font-medium">
                <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                School identity, contact and location details
            </p>
        </div>
    </div>

    <form @submit.prevent="submitForm()" novalidate class="space-y-6">
        @csrf

        {{-- School Identity --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-university text-indigo-600"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">School Identity</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Core details used across all documents and reports.</p>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                {{-- School Name --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-gray-700">
                        School Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name"
                           x-model="formData.name"
                           @input="clearError('name')"
                           placeholder="e.g. Delhi Public School"
                           class="w-full px-4 py-2.5 bg-gray-50 border rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                           :class="errors.name ? 'border-red-500 bg-red-50' : 'border-gray-200'">
                    <template x-if="errors.name">
                        <p class="text-xs text-red-500 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.name[0]"></span>
                        </p>
                    </template>
                </div>

                {{-- Email --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-gray-700">
                        Official Email <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 pointer-events-none">
                            <i class="fas fa-envelope text-xs"></i>
                        </span>
                        <input type="email" name="email"
                               x-model="formData.email"
                               @input="clearError('email')"
                               placeholder="admin@school.com"
                               class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                               :class="errors.email ? 'border-red-500 bg-red-50' : 'border-gray-200'">
                    </div>
                    <template x-if="errors.email">
                        <p class="text-xs text-red-500 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.email[0]"></span>
                        </p>
                    </template>
                </div>

                {{-- Phone --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-gray-700">Contact Number</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 pointer-events-none">
                            <i class="fas fa-phone text-xs"></i>
                        </span>
                        <input type="text" name="phone"
                               x-model="formData.phone"
                               @input="clearError('phone')"
                               placeholder="+91 98765 43210"
                               class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                               :class="errors.phone ? 'border-red-500 bg-red-50' : 'border-gray-200'">
                    </div>
                    <template x-if="errors.phone">
                        <p class="text-xs text-red-500 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.phone[0]"></span>
                        </p>
                    </template>
                </div>

                {{-- Website --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-gray-700">Official Website</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 pointer-events-none">
                            <i class="fas fa-globe text-xs"></i>
                        </span>
                        <input type="url" name="website"
                               x-model="formData.website"
                               @input="clearError('website')"
                               placeholder="https://www.school.com"
                               class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                               :class="errors.website ? 'border-red-500 bg-red-50' : 'border-gray-200'">
                    </div>
                    <template x-if="errors.website">
                        <p class="text-xs text-red-500 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.website[0]"></span>
                        </p>
                    </template>
                </div>

            </div>
        </div>

        {{-- Address & Location --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900">Address & Location</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Used on receipts, ID cards and official correspondence.</p>
                </div>
            </div>

            <div class="p-6 space-y-6">

                {{-- Full Address --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-gray-700">Full Address</label>
                    <div class="relative">
                        <span class="absolute top-3 left-4 text-gray-400 pointer-events-none">
                            <i class="fas fa-map-pin text-xs"></i>
                        </span>
                        <textarea name="address" rows="2"
                                  x-model="formData.address"
                                  @input="clearError('address')"
                                  placeholder="Street, Area, Landmark..."
                                  class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all resize-none"
                                  :class="errors.address ? 'border-red-500 bg-red-50' : 'border-gray-200'"></textarea>
                    </div>
                    <template x-if="errors.address">
                        <p class="text-xs text-red-500 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.address[0]"></span>
                        </p>
                    </template>
                </div>

                {{-- Country / State / City cascade --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-location-selector
                        :countries="$countries"
                        :selectedCountry="old('country_id', $school->country_id)"
                        :selectedState="old('state_id', $school->state_id)"
                        :selectedCity="old('city_id', $school->city_id)"
                        :required="false"
                    />
                </div>

                {{-- Pincode --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-gray-700">PIN / ZIP Code</label>
                        <input type="text" name="pincode"
                               x-model="formData.pincode"
                               @input="clearError('pincode')"
                               placeholder="e.g. 110001"
                               class="w-full px-4 py-2.5 bg-gray-50 border rounded-xl text-sm font-medium text-gray-900 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                               :class="errors.pincode ? 'border-red-500 bg-red-50' : 'border-gray-200'">
                        <template x-if="errors.pincode">
                            <p class="text-xs text-red-500 flex items-center gap-1">
                                <i class="fas fa-exclamation-circle"></i>
                                <span x-text="errors.pincode[0]"></span>
                            </p>
                        </template>
                    </div>
                </div>

            </div>
        </div>

        {{-- Save Button --}}
        <div class="flex items-center justify-end pt-1">
            <button type="submit" :disabled="submitting"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <template x-if="submitting">
                    <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                </template>
                <template x-if="!submitting">
                    <i class="fas fa-save text-xs"></i>
                </template>
                <span x-text="submitting ? 'Saving...' : 'Save Changes'"></span>
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
function basicInfoSettings() {
    return {
        submitting: false,
        errors: {},
        formData: {
            name:    @js(old('name', $school->name)),
            email:   @js(old('email', $school->email)),
            phone:   @js(old('phone', $school->phone)),
            address: @js(old('address', $school->address)),
            pincode: @js(old('pincode', $school->pincode)),
            website: @js(old('website', $school->website)),
        },

        clearError(field) {
            if (this.errors && this.errors[field]) {
                const e = { ...this.errors };
                delete e[field];
                this.errors = e;
            }
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};

            // Collect location selects (handled outside Alpine via location-cascade.js)
            const form = this.$el.closest('form') ?? this.$el.querySelector('form');
            const countryId = document.querySelector('[name="country_id"]')?.value ?? '';
            const stateId   = document.querySelector('[name="state_id"]')?.value ?? '';
            const cityId    = document.querySelector('[name="city_id"]')?.value ?? '';

            try {
                const response = await fetch('{{ route('school.settings.basic-info.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ...this.formData,
                        country_id: countryId,
                        state_id:   stateId,
                        city_id:    cityId,
                        _method: 'PUT'
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: result.message || 'Changes saved successfully.', type: 'success' }
                    }));
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Something went wrong.');
                }
            } catch (error) {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: error.message, type: 'error' }
                }));
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush
@endsection
