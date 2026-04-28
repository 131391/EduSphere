@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')
<div x-data="profilePage()" class="space-y-6">

    <x-page-header title="My Profile" description="View and manage your account information" icon="fas fa-user-circle" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left: Profile Card -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Avatar & Identity -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 flex flex-col items-center text-center">
                <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg mb-4">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <h2 class="text-lg font-bold text-gray-800 dark:text-white" x-text="form.name">{{ Auth::user()->name }}</h2>
                <p class="text-sm text-gray-400 mt-0.5" x-text="form.email">{{ Auth::user()->email }}</p>
                <span class="mt-3 px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                    {{ Auth::user()->role->name ?? 'Super Admin' }}
                </span>

                <div class="mt-6 pt-5 border-t border-gray-100 dark:border-gray-700 w-full space-y-4 text-left">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Status</span>
                        <span class="flex items-center gap-2 text-sm font-semibold text-emerald-600">
                            <span class="w-2 h-2 bg-emerald-500 rounded-full"></span> Active
                        </span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Member Since</span>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {{ Auth::user()->created_at?->format('M d, Y') ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Last Login</span>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                            {{ Auth::user()->last_login_at?->diffForHumans() ?? 'N/A' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Account Type</span>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Platform Administrator</span>
                    </div>
                </div>
            </div>

            <!-- Security Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
                        <i class="fas fa-shield-alt text-sm"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 dark:text-white">Password & Security</h4>
                        <p class="text-[11px] text-gray-400">Keep your account secure</p>
                    </div>
                </div>
                <a href="{{ route('admin.change-password') }}"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition-all">
                    <i class="fas fa-key text-xs text-gray-400"></i>
                    Change Password
                </a>
            </div>
        </div>

        <!-- Right: Edit Form -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <!-- Card Header -->
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                        <i class="fas fa-user-edit text-xs"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white">Account Settings</h3>
                        <p class="text-[11px] text-gray-400">Update your personal information</p>
                    </div>
                </div>

                <div class="p-6">
                    <form @submit.prevent="submitProfile()" novalidate class="space-y-5">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Full Name -->
                            <div class="space-y-1.5">
                                <label class="modal-label-premium">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" x-model="form.name" @input="clearError('name')"
                                    placeholder="Enter your full name"
                                    class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none"
                                    :class="errors.name ? 'border-red-400 bg-red-50' : 'border-gray-200 dark:border-gray-600'">
                                <template x-if="errors.name"><p class="modal-error-message" x-text="errors.name"></p></template>
                            </div>

                            <!-- Email -->
                            <div class="space-y-1.5">
                                <label class="modal-label-premium">Email Address <span class="text-red-500">*</span></label>
                                <input type="email" x-model="form.email" @input="clearError('email')"
                                    placeholder="Enter your email"
                                    class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none"
                                    :class="errors.email ? 'border-red-400 bg-red-50' : 'border-gray-200 dark:border-gray-600'">
                                <template x-if="errors.email"><p class="modal-error-message" x-text="errors.email"></p></template>
                            </div>

                            <!-- Phone -->
                            <div class="space-y-1.5">
                                <label class="modal-label-premium">Phone Number</label>
                                <input type="tel" x-model="form.phone" @input="clearError('phone')"
                                    placeholder="Enter your phone number"
                                    class="w-full h-11 px-4 bg-gray-50 dark:bg-gray-700 border rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none"
                                    :class="errors.phone ? 'border-red-400 bg-red-50' : 'border-gray-200 dark:border-gray-600'">
                                <template x-if="errors.phone"><p class="modal-error-message" x-text="errors.phone"></p></template>
                            </div>
                        </div>

                        <!-- Success Banner -->
                        <div x-show="success" x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                            x-cloak
                            class="flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-sm text-emerald-700 font-semibold">
                            <i class="fas fa-check-circle text-emerald-500"></i>
                            Profile updated successfully.
                        </div>

                        <!-- Footer -->
                        <div class="pt-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-700">
                            <p class="text-[11px] text-gray-400">
                                Last updated {{ Auth::user()->updated_at?->diffForHumans() ?? 'never' }}
                            </p>
                            <button type="submit" :disabled="submitting"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm hover:shadow-md active:scale-95 disabled:opacity-60">
                                <span x-show="submitting" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" x-cloak></span>
                                <i x-show="!submitting" class="fas fa-save text-xs"></i>
                                <span x-text="submitting ? 'Saving...' : 'Save Changes'">Save Changes</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function profilePage() {
    return {
        submitting: false,
        success: false,
        errors: {},
        form: {
            name:  '{{ addslashes(Auth::user()->name) }}',
            email: '{{ addslashes(Auth::user()->email) }}',
            phone: '{{ addslashes(Auth::user()->phone ?? '') }}',
        },

        clearError(field) {
            if (this.errors && this.errors[field]) { const e = { ...this.errors }; delete e[field]; this.errors = e; }
            this.success = false;
        },

        async submitProfile() {
            this.submitting = true;
            this.errors = {};
            this.success = false;

            try {
                const response = await fetch('{{ route('admin.update-profile') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await response.json();

                if (response.ok) {
                    this.success = true;
                    setTimeout(() => this.success = false, 4000);
                } else if (response.status === 422) {
                    // Flatten first error message per field
                    Object.entries(data.errors || {}).forEach(([key, msgs]) => {
                        this.errors[key] = Array.isArray(msgs) ? msgs[0] : msgs;
                    });
                } else {
                    throw new Error(data.message || 'Something went wrong.');
                }
            } catch (err) {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { type: 'error', message: err.message }
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
