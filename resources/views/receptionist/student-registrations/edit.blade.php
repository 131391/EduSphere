@extends('layouts.receptionist')

@section('content')
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Edit Student Registration</h1>
                <p class="text-gray-600 dark:text-gray-400">Update the details for registration:
                    {{ $studentRegistration->registration_no }}</p>
            </div>
            <a href="{{ route('receptionist.student-registrations.index') }}"
                class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>

        <div x-data="registrationManagement('{{ route('receptionist.student-registrations.update', $studentRegistration->id) }}', 'PUT')" 
             class="space-y-6">
            
            <!-- Centralized Validation Summary -->
            <template x-if="Object.keys(errors).length > 0">
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm mb-6 animate-pulse-subtle">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <h3 class="text-sm font-bold text-red-800 uppercase tracking-wider">Validation Errors Detected</h3>
                    </div>
                    <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                        <template x-for="(messages, field) in errors" :key="field">
                            <li x-text="messages[0]"></li>
                        </template>
                    </ul>
                </div>
            </template>

            <form @submit.prevent="submitForm">
                @csrf
                @method('PUT')

                {{-- Admission Status (Only in Edit) --}}
                <div class="mb-6 bg-white dark:bg-gray-800 p-6 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Admission Status <span class="text-red-500">*</span>
                    </label>
                    <select name="admission_status" @change="delete errors.admission_status"
                        class="w-full md:w-1/3 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 dark:bg-gray-700 dark:text-white transition-all shadow-sm"
                        :class="errors.admission_status ? 'border-red-500 ring-red-500/5 bg-red-50/20' : 'border-gray-300 dark:border-gray-600'">
                        @foreach(\App\Enums\AdmissionStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ old('admission_status', $studentRegistration->admission_status->value ?? $studentRegistration->admission_status) == $status->value ? 'selected' : '' }}>{{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                    <template x-if="errors.admission_status">
                        <p class="text-red-500 text-[10px] font-bold mt-1 uppercase tracking-tight" x-text="errors.admission_status[0]"></p>
                    </template>
                </div>

                @include('receptionist.student-registrations.partials.form')

                <!-- Submit Section -->
                <div class="mt-8 flex items-center justify-end gap-4 p-6 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-800">
                    <a href="{{ route('receptionist.student-registrations.index') }}" 
                       class="px-6 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                        Cancel
                    </a>
                    <button type="submit" 
                            :disabled="loading"
                            class="px-10 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-bold shadow-lg shadow-teal-500/20 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="!loading">
                            <span>Update Registration</span>
                        </template>
                        <template x-if="loading">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i>
                                Updating...
                            </span>
                        </template>
                    </button>
                </div>
            </form>
        </div>

        <script>
            function registrationManagement(actionUrl, method = 'POST') {
                return {
                    errors: {},
                    loading: false,
                    
                    async submitForm(e) {
                        this.loading = true;
                        this.errors = {};
                        
                        const form = e.target;
                        const formData = new FormData(form);
                        
                        try {
                            const response = await fetch(actionUrl, {
                                method: 'POST', // Use POST for both store and update, handled by @method
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });
                            
                            const result = await response.json();
                            
                            if (response.status === 422) {
                                this.errors = result.errors;
                                // Scroll to first error
                                this.$nextTick(() => {
                                    const firstError = document.querySelector('.text-red-500');
                                    if (firstError) {
                                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                });
                            } else if (response.ok) {
                                // Redirect or show success
                                window.location.href = result.redirect || '{{ route('receptionist.student-registrations.index') }}';
                            } else {
                                alert(result.message || 'Something went wrong. Please try again.');
                            }
                        } catch (error) {
                            console.error('Form submission error:', error);
                            alert('A network error occurred. Please try again.');
                        } finally {
                            this.loading = false;
                        }
                    }
                };
            }
        </script>
    </div>
@endsection