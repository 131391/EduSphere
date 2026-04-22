@extends('layouts.admin')

@section('title', 'Change Password')

@section('content')
<div class="w-full space-y-6" x-data="adminChangePassword()">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Change Password</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1 flex items-center">
                <i class="fas fa-shield-alt mr-2 text-indigo-500"></i>
                Secure your account with a strong password
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Guidelines -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-3xl p-6 text-white shadow-lg shadow-blue-200">
                <h3 class="text-lg font-bold mb-4 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i> Password Guidelines
                </h3>
                <ul class="space-y-4 text-sm text-blue-50">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mt-0.5 mr-3 text-blue-300"></i>
                        <span>Minimum 8 characters long</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mt-0.5 mr-3 text-blue-300"></i>
                        <span>Must include at least one number</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mt-0.5 mr-3 text-blue-300"></i>
                        <span>Must include mixed case (uppercase & lowercase)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-exclamation-triangle mt-0.5 mr-3 text-amber-300"></i>
                        <span>Avoid using common words or your email address</span>
                    </li>
                </ul>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                <h4 class="font-bold text-gray-900 dark:text-white mb-2">Last Changed</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Keep your account secure by changing your password periodically.</p>
                <div class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">
                    <i class="far fa-clock mr-2 text-gray-400 dark:text-gray-500"></i>
                    Updated: {{ Auth::user()->updated_at->diffForHumans() }}
                </div>
            </div>
        </div>

        <!-- Right Column: Form -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/50 flex items-center">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center mr-4">
                        <i class="fas fa-lock text-indigo-600 dark:text-indigo-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Update Credentials</h3>
                </div>

                <form @submit.prevent="submitForm" class="p-8 space-y-6" novalidate>
                    @csrf
                    
                    <!-- Current Password -->
                    <div class="space-y-2">
                        <label for="current_password" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Current Password <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-key text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
                            </div>
                            <input type="password" name="current_password" id="current_password" x-model="formData.current_password"
                                @input="clearError('current_password')"
                                :class="hasError('current_password') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200 dark:border-gray-600'"
                                class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 border rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none"
                                placeholder="Enter your current password">
                        </div>
                        <template x-if="hasError('current_password')">
                            <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('current_password')"></p>
                        </template>
                    </div>

                    <!-- New Password -->
                    <div class="space-y-2">
                        <label for="password" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">New Password <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-shield-alt text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
                            </div>
                            <input type="password" name="password" id="password" x-model="formData.password"
                                @input="clearError('password')"
                                :class="hasError('password') ? 'border-red-500 ring-2 ring-red-500/10' : 'border-gray-200 dark:border-gray-600'"
                                class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 border rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none"
                                placeholder="Min. 8 characters with numbers">
                        </div>
                        <template x-if="hasError('password')">
                            <p class="mt-1 text-xs text-red-600 font-medium ml-1" x-text="getError('password')"></p>
                        </template>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2">
                        <label for="password_confirmation" class="text-sm font-bold text-gray-700 dark:text-gray-300 ml-1">Confirm New Password <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-check-double text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation" x-model="formData.password_confirmation"
                                @input="clearError('password')"
                                class="w-full pl-11 pr-4 py-3 bg-white dark:bg-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none"
                                placeholder="Repeat your new password">
                        </div>
                    </div>

                    <div class="pt-4 flex items-center justify-end">
                        <button type="submit" 
                            :disabled="isSubmitting"
                            class="inline-flex items-center px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl text-sm font-bold transition-all duration-200 shadow-lg shadow-indigo-100 hover:shadow-indigo-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting">Update Password</span>
                            <span x-show="isSubmitting" class="flex items-center" style="display: none;">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function adminChangePassword() {
        return {
            formData: {
                current_password: '',
                password: '',
                password_confirmation: ''
            },
            errors: {},
            isSubmitting: false,

            submitForm() {
                this.isSubmitting = true;
                this.errors = {};

                fetch("{{ route('admin.update-password') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.formData)
                })
                .then(async response => {
                    const data = await response.json();
                    if (response.status === 422) {
                        this.errors = data.errors;
                    } else if (response.ok) {
                        window.showToast('success', data.message || 'Password changed successfully');
                        this.formData = {
                            current_password: '',
                            password: '',
                            password_confirmation: ''
                        };
                    } else {
                        window.showToast('error', data.message || 'Something went wrong');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.showToast('error', 'An unexpected error occurred');
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
            },

            hasError(field) {
                return !!this.errors[field];
            },

            getError(field) {
                return this.errors[field] ? this.errors[field][0] : '';
            },

            clearError(field) {
                if (this.errors[field]) {
                    delete this.errors[field];
                }
            }
        }
    }
</script>
@endpush
