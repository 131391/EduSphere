@extends('layouts.school')

@section('title', 'My Profile')

@section('content')
<div x-data="schoolProfilePage()" class="space-y-6 max-w-5xl">

    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden">
        <div class="absolute right-0 top-0 w-56 h-56 bg-blue-50 dark:bg-blue-900/10 rounded-full -mr-16 -mt-16 blur-3xl pointer-events-none"></div>
        <div class="relative flex items-center gap-3">
            <div class="w-9 h-9 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-circle text-sm"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">My Profile</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">Manage your account information and security</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Left: Identity Card --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Avatar & Meta --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg mb-4">
                    {{ strtoupper(substr($user->name ?? 'A', 0, 2)) }}
                </div>
                <h2 class="text-base font-bold text-gray-800 dark:text-white" x-text="profile.name">{{ $user->name }}</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5" x-text="profile.email">{{ $user->email }}</p>
                <span class="mt-3 px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-800">
                    <i class="fas fa-user-shield text-[9px] mr-1"></i>School Admin
                </span>

                @if($user->must_change_password)
                <span class="mt-2 px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-100 dark:border-amber-800">
                    <i class="fas fa-exclamation-triangle text-[9px] mr-1"></i>Password Reset Required
                </span>
                @endif

                <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-700 w-full space-y-3 text-left">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-0.5">Status</span>
                        <span class="flex items-center gap-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> Active
                        </span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-0.5">Member Since</span>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $user->created_at?->format('M d, Y') ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-0.5">Last Login</span>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $user->last_login_at?->diffForHumans() ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-0.5">Last Updated</span>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $user->updated_at?->diffForHumans() ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            {{-- Security Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                        <i class="fas fa-shield-alt text-sm"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-800 dark:text-white">Security</h4>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Keep your account secure</p>
                    </div>
                </div>
                <button @click="activeTab = 'password'"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 hover:border-amber-300 dark:hover:border-amber-700 hover:text-amber-700 dark:hover:text-amber-400 transition-all">
                    <i class="fas fa-key text-[10px]"></i>
                    Change Password
                </button>
            </div>
        </div>

        {{-- Right: Tabbed Forms --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">

                {{-- Tabs --}}
                <div class="flex border-b border-gray-100 dark:border-gray-700">
                    <button @click="activeTab = 'profile'"
                            class="flex items-center gap-2 px-5 py-3.5 text-xs font-bold uppercase tracking-wider transition-all border-b-2 -mb-px"
                            :class="activeTab === 'profile'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400 bg-blue-50/50 dark:bg-blue-900/10'
                                : 'border-transparent text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'">
                        <i class="fas fa-user-edit text-[10px]"></i>
                        Edit Profile
                    </button>
                    <button @click="activeTab = 'password'"
                            class="flex items-center gap-2 px-5 py-3.5 text-xs font-bold uppercase tracking-wider transition-all border-b-2 -mb-px"
                            :class="activeTab === 'password'
                                ? 'border-amber-500 text-amber-600 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-900/10'
                                : 'border-transparent text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300'">
                        <i class="fas fa-key text-[10px]"></i>
                        Change Password
                    </button>
                </div>

                {{-- Edit Profile Tab --}}
                <div x-show="activeTab === 'profile'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <form @submit.prevent="submitProfile()" novalidate class="p-6 space-y-5">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- Full Name --}}
                            <div class="md:col-span-2 space-y-1.5">
                                <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                                    Full Name <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" x-model="profile.name" @input="clearError('profile', 'name')"
                                       placeholder="Enter your full name"
                                       class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700/50 border rounded-lg text-sm text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                       :class="profileErrors.name ? 'border-red-400 bg-red-50 dark:bg-red-900/10' : 'border-gray-200 dark:border-gray-600'">
                                <template x-if="profileErrors.name">
                                    <p class="text-xs text-rose-500 flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle text-[10px]"></i>
                                        <span x-text="profileErrors.name"></span>
                                    </p>
                                </template>
                            </div>

                            {{-- Email --}}
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                                    Email <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                                        <i class="fas fa-envelope text-[10px]"></i>
                                    </span>
                                    <input type="email" x-model="profile.email" @input="clearError('profile', 'email')"
                                           placeholder="your@email.com"
                                           class="w-full h-10 pl-8 pr-3 bg-gray-50 dark:bg-gray-700/50 border rounded-lg text-sm text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                           :class="profileErrors.email ? 'border-red-400 bg-red-50 dark:bg-red-900/10' : 'border-gray-200 dark:border-gray-600'">
                                </div>
                                <template x-if="profileErrors.email">
                                    <p class="text-xs text-rose-500 flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle text-[10px]"></i>
                                        <span x-text="profileErrors.email"></span>
                                    </p>
                                </template>
                            </div>

                            {{-- Phone --}}
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Phone</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                                        <i class="fas fa-phone text-[10px]"></i>
                                    </span>
                                    <input type="tel" x-model="profile.phone" @input="clearError('profile', 'phone')"
                                           placeholder="+1 (555) 000-0000"
                                           class="w-full h-10 pl-8 pr-3 bg-gray-50 dark:bg-gray-700/50 border rounded-lg text-sm text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                           :class="profileErrors.phone ? 'border-red-400 bg-red-50 dark:bg-red-900/10' : 'border-gray-200 dark:border-gray-600'">
                                </div>
                                <template x-if="profileErrors.phone">
                                    <p class="text-xs text-rose-500 flex items-center gap-1">
                                        <i class="fas fa-exclamation-circle text-[10px]"></i>
                                        <span x-text="profileErrors.phone"></span>
                                    </p>
                                </template>
                            </div>
                        </div>

                        <div class="pt-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-700">
                            <p class="text-[10px] text-gray-400 dark:text-gray-500">
                                Last updated {{ $user->updated_at?->diffForHumans() ?? 'never' }}
                            </p>
                            <button type="submit" :disabled="profileSubmitting"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 disabled:opacity-60 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-all active:scale-95">
                                <span x-show="profileSubmitting" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin" x-cloak></span>
                                <i x-show="!profileSubmitting" class="fas fa-save text-[10px]"></i>
                                <span x-text="profileSubmitting ? 'Saving...' : 'Save Changes'"></span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Change Password Tab --}}
                <div x-show="activeTab === 'password'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
                    <form @submit.prevent="submitPassword()" novalidate class="p-6 space-y-5">

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Choose a strong password with at least 8 characters, mixed case, and numbers.
                        </p>

                        {{-- Current Password --}}
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                                Current Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                                    <i class="fas fa-lock text-[10px]"></i>
                                </span>
                                <input :type="showCurrent ? 'text' : 'password'"
                                       x-model="passwords.current" @input="clearError('password', 'current_password')"
                                       autocomplete="current-password"
                                       class="w-full h-10 pl-8 pr-10 bg-gray-50 dark:bg-gray-700/50 border rounded-lg text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all"
                                       :class="passwordErrors.current_password ? 'border-red-400 bg-red-50 dark:bg-red-900/10' : 'border-gray-200 dark:border-gray-600'">
                                <button type="button" @click="showCurrent = !showCurrent"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                    <i :class="showCurrent ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-[11px]"></i>
                                </button>
                            </div>
                            <template x-if="passwordErrors.current_password">
                                <p class="text-xs text-rose-500 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle text-[10px]"></i>
                                    <span x-text="passwordErrors.current_password"></span>
                                </p>
                            </template>
                        </div>

                        {{-- New Password --}}
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                                New Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                                    <i class="fas fa-key text-[10px]"></i>
                                </span>
                                <input :type="showNew ? 'text' : 'password'"
                                       x-model="passwords.new" @input="clearError('password', 'password')"
                                       autocomplete="new-password"
                                       class="w-full h-10 pl-8 pr-10 bg-gray-50 dark:bg-gray-700/50 border rounded-lg text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all"
                                       :class="passwordErrors.password ? 'border-red-400 bg-red-50 dark:bg-red-900/10' : 'border-gray-200 dark:border-gray-600'">
                                <button type="button" @click="showNew = !showNew"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                    <i :class="showNew ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-[11px]"></i>
                                </button>
                            </div>
                            {{-- Strength bar --}}
                            <div x-show="passwords.new.length > 0" class="flex gap-1 mt-1">
                                <template x-for="i in 4">
                                    <div class="h-1 flex-1 rounded-full transition-all duration-300"
                                         :class="i <= passwordStrength
                                            ? (passwordStrength <= 1 ? 'bg-red-400' : passwordStrength <= 2 ? 'bg-amber-400' : passwordStrength <= 3 ? 'bg-blue-400' : 'bg-emerald-500')
                                            : 'bg-gray-200 dark:bg-gray-600'"></div>
                                </template>
                                <span class="text-[10px] font-bold ml-1"
                                      :class="passwordStrength <= 1 ? 'text-red-400' : passwordStrength <= 2 ? 'text-amber-400' : passwordStrength <= 3 ? 'text-blue-400' : 'text-emerald-500'"
                                      x-text="['', 'Weak', 'Fair', 'Good', 'Strong'][passwordStrength]"></span>
                            </div>
                            <template x-if="passwordErrors.password">
                                <p class="text-xs text-rose-500 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle text-[10px]"></i>
                                    <span x-text="passwordErrors.password"></span>
                                </p>
                            </template>
                        </div>

                        {{-- Confirm Password --}}
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                                Confirm New Password <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 pointer-events-none">
                                    <i class="fas fa-key text-[10px]"></i>
                                </span>
                                <input :type="showConfirm ? 'text' : 'password'"
                                       x-model="passwords.confirm"
                                       autocomplete="new-password"
                                       class="w-full h-10 pl-8 pr-10 bg-gray-50 dark:bg-gray-700/50 border rounded-lg text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all"
                                       :class="passwords.confirm && passwords.confirm !== passwords.new ? 'border-red-400 bg-red-50 dark:bg-red-900/10' : 'border-gray-200 dark:border-gray-600'">
                                <button type="button" @click="showConfirm = !showConfirm"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                                    <i :class="showConfirm ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-[11px]"></i>
                                </button>
                            </div>
                            <template x-if="passwords.confirm && passwords.confirm !== passwords.new">
                                <p class="text-xs text-rose-500 flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle text-[10px]"></i>
                                    Passwords do not match
                                </p>
                            </template>
                        </div>

                        <div class="pt-4 flex justify-end border-t border-gray-100 dark:border-gray-700">
                            <button type="submit" :disabled="passwordSubmitting || (passwords.confirm && passwords.confirm !== passwords.new)"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 disabled:opacity-60 text-white text-xs font-bold uppercase tracking-wider rounded-lg transition-all active:scale-95">
                                <span x-show="passwordSubmitting" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin" x-cloak></span>
                                <i x-show="!passwordSubmitting" class="fas fa-shield-alt text-[10px]"></i>
                                <span x-text="passwordSubmitting ? 'Updating...' : 'Update Password'"></span>
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
function schoolProfilePage() {
    return {
        activeTab: '{{ $user->must_change_password ? "password" : "profile" }}',

        // Profile form
        profileSubmitting: false,
        profileErrors: {},
        profile: {
            name:  @js(old('name', $user->name)),
            email: @js(old('email', $user->email)),
            phone: @js(old('phone', $user->phone ?? '')),
        },

        // Password form
        passwordSubmitting: false,
        passwordErrors: {},
        passwords: { current: '', new: '', confirm: '' },
        showCurrent: false,
        showNew: false,
        showConfirm: false,

        get passwordStrength() {
            const p = this.passwords.new;
            if (!p) return 0;
            let score = 0;
            if (p.length >= 8)  score++;
            if (/[A-Z]/.test(p) && /[a-z]/.test(p)) score++;
            if (/\d/.test(p))   score++;
            if (/[^A-Za-z0-9]/.test(p)) score++;
            return score;
        },

        clearError(form, field) {
            if (form === 'profile' && this.profileErrors[field]) {
                const e = { ...this.profileErrors }; delete e[field]; this.profileErrors = e;
            }
            if (form === 'password' && this.passwordErrors[field]) {
                const e = { ...this.passwordErrors }; delete e[field]; this.passwordErrors = e;
            }
        },

        async submitProfile() {
            if (this.profileSubmitting) return;
            this.profileSubmitting = true;
            this.profileErrors = {};
            try {
                const res = await fetch('{{ route('school.profile.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(this.profile),
                });
                const data = await res.json();
                if (res.ok) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { type: 'success', message: data.message || 'Profile updated successfully.' }
                    }));
                } else if (res.status === 422) {
                    Object.entries(data.errors || {}).forEach(([k, v]) => {
                        this.profileErrors[k] = Array.isArray(v) ? v[0] : v;
                    });
                } else {
                    throw new Error(data.message || 'Something went wrong.');
                }
            } catch (err) {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { type: 'error', message: err.message }
                }));
            } finally {
                this.profileSubmitting = false;
            }
        },

        async submitPassword() {
            if (this.passwordSubmitting) return;
            if (this.passwords.new !== this.passwords.confirm) return;
            this.passwordSubmitting = true;
            this.passwordErrors = {};
            try {
                const res = await fetch('{{ route('school.profile.password.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        current_password: this.passwords.current,
                        password: this.passwords.new,
                        password_confirmation: this.passwords.confirm,
                    }),
                });
                const data = await res.json();
                if (res.ok) {
                    this.passwords = { current: '', new: '', confirm: '' };
                    this.activeTab = 'profile';
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { type: 'success', message: data.message || 'Password changed successfully.' }
                    }));
                } else if (res.status === 422) {
                    Object.entries(data.errors || {}).forEach(([k, v]) => {
                        this.passwordErrors[k] = Array.isArray(v) ? v[0] : v;
                    });
                } else {
                    throw new Error(data.message || 'Something went wrong.');
                }
            } catch (err) {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { type: 'error', message: err.message }
                }));
            } finally {
                this.passwordSubmitting = false;
            }
        },
    }
}
</script>
@endpush
@endsection
