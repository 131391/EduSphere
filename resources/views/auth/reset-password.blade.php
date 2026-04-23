@extends('layouts.auth')

@section('title', 'Reset Password - ' . config('app.name'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --auth-ink: #163126;
        --auth-deep: #1e4a39;
        --auth-soft: #f6f8f4;
        --auth-card: rgba(255, 255, 255, 0.9);
        --auth-line: rgba(22, 49, 38, 0.12);
        --auth-accent: #c8ff6f;
        --auth-muted: #617369;
        --auth-error: #c94949;
    }
    .auth-shell {
        min-height: 100vh;
        font-family: 'DM Sans', sans-serif;
        background:
            radial-gradient(circle at top left, rgba(200,255,111,.22), transparent 28%),
            radial-gradient(circle at bottom right, rgba(182,224,255,.22), transparent 26%),
            linear-gradient(180deg, #fdfdf8 0%, #f4f6f1 100%);
        color: var(--auth-ink);
    }
    .auth-card {
        background: var(--auth-card);
        border: 1px solid rgba(255,255,255,.8);
        box-shadow: 0 24px 60px rgba(22,49,38,.1);
        backdrop-filter: blur(16px);
    }
    .auth-field:focus-within {
        border-color: rgba(30,74,57,.35);
        box-shadow: 0 0 0 5px rgba(200,255,111,.24);
    }
</style>
@endpush

@section('content')
@php
    $tenantSchool = $school ?? null;
    $tenantLogo = $tenantSchool?->logo ? asset('storage/' . $tenantSchool->logo) : null;
    $tenantName = $tenantSchool?->name ?: config('app.name', 'EduSphere');
@endphp
<div class="auth-shell flex items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <div class="auth-card overflow-hidden rounded-[1.75rem]"
            x-data="ajaxAuthForm({
                url: '{{ route('password.update') }}',
                initialForm: {
                    token: @js($token),
                    email: @js($email),
                    password: '',
                    password_confirmation: '',
                },
                validate() {
                    const errors = {};
                    if (!this.form.email) {
                        errors.email = 'Email address is required.';
                    }
                    if (!this.form.password) {
                        errors.password = 'New password is required.';
                    } else if (this.form.password.length < 8) {
                        errors.password = 'Password must be at least 8 characters.';
                    }
                    if (!this.form.password_confirmation) {
                        errors.password_confirmation = 'Please confirm your new password.';
                    } else if (this.form.password !== this.form.password_confirmation) {
                        errors.password_confirmation = 'Password confirmation does not match.';
                    }
                    return errors;
                },
                onSuccess(data) {
                    window.location.assign(data?.redirect || '{{ route('login') }}');
                },
                validationMessage: 'Please fix the highlighted fields.',
                errorMessage: 'We could not reset your password.',
            })">
            <div class="px-6 pt-7 text-center sm:px-8">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center overflow-hidden rounded-3xl border border-[var(--auth-line)] bg-white shadow-sm">
                    @if($tenantLogo)
                        <img src="{{ $tenantLogo }}" alt="{{ $tenantName }} logo" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-[var(--auth-accent)] text-[var(--auth-deep)]">
                            <i class="fas fa-lock text-3xl"></i>
                        </div>
                    @endif
                </div>
                <p class="text-xs font-bold uppercase tracking-[0.28em] text-[var(--auth-muted)]">Create a new password</p>
                <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-[var(--auth-ink)]">{{ $tenantName }}</h1>
                <p class="mt-3 text-sm leading-6 text-[var(--auth-muted)]">Set a new password for your account and return to login securely.</p>
            </div>
            <div class="px-6 py-6 sm:px-8">
                <div x-cloak x-show="message" x-transition class="mb-5 rounded-2xl border px-4 py-3 text-sm"
                    :class="messageType === 'error' ? 'border-red-200 bg-red-50 text-[var(--auth-error)]' : 'border-emerald-200 bg-emerald-50 text-emerald-700'">
                    <div class="flex items-start gap-3">
                        <i class="fas mt-0.5" :class="messageType === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'"></i>
                        <p x-text="message"></p>
                    </div>
                </div>
                <form method="POST" action="{{ route('password.update') }}" class="space-y-4" novalidate @submit.prevent="submit">
                    @csrf
                    <input type="hidden" name="token" x-model="form.token">
                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-[var(--auth-ink)]">Email address</label>
                        <div class="auth-field flex min-h-[54px] items-center gap-3 rounded-2xl border bg-white px-4 transition"
                            :class="errors.email ? 'border-red-300' : 'border-[var(--auth-line)]'">
                            <i class="fas fa-envelope text-slate-400"></i>
                            <input id="email" name="email" type="email" autocomplete="email" required x-model.trim="form.email" @input="clearError('email')"
                                class="w-full border-0 bg-transparent py-3.5 text-base text-[var(--auth-ink)] placeholder:text-slate-400 focus:outline-none focus:ring-0"
                                placeholder="Enter your email">
                        </div>
                        <p x-cloak x-show="errors.email" class="mt-2 text-xs font-medium text-[var(--auth-error)]" x-text="errors.email"></p>
                    </div>
                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-[var(--auth-ink)]">New password</label>
                        <div class="auth-field flex min-h-[54px] items-center gap-3 rounded-2xl border bg-white px-4 transition"
                            :class="errors.password ? 'border-red-300' : 'border-[var(--auth-line)]'">
                            <i class="fas fa-lock text-slate-400"></i>
                            <input :type="isPasswordVisible('password') ? 'text' : 'password'" id="password" name="password" autocomplete="new-password" required x-model="form.password"
                                @input="clearError('password')" @keydown="syncCapsLock" @keyup="syncCapsLock" @blur="capsLockOn = false"
                                class="w-full border-0 bg-transparent py-3.5 text-base text-[var(--auth-ink)] placeholder:text-slate-400 focus:outline-none focus:ring-0"
                                placeholder="Create a new password">
                            <button type="button" @click="togglePasswordVisibility('password')" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                                <i class="fas" :class="isPasswordVisible('password') ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        <p x-cloak x-show="capsLockOn" class="mt-2 text-xs font-medium text-amber-700">Caps Lock is on.</p>
                        <p x-cloak x-show="errors.password" class="mt-2 text-xs font-medium text-[var(--auth-error)]" x-text="errors.password"></p>
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-[var(--auth-ink)]">Confirm new password</label>
                        <div class="auth-field flex min-h-[54px] items-center gap-3 rounded-2xl border bg-white px-4 transition"
                            :class="errors.password_confirmation ? 'border-red-300' : 'border-[var(--auth-line)]'">
                            <i class="fas fa-check-double text-slate-400"></i>
                            <input :type="isPasswordVisible('password_confirmation') ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required x-model="form.password_confirmation"
                                @input="clearError('password_confirmation')" @keydown="syncCapsLock" @keyup="syncCapsLock" @blur="capsLockOn = false"
                                class="w-full border-0 bg-transparent py-3.5 text-base text-[var(--auth-ink)] placeholder:text-slate-400 focus:outline-none focus:ring-0"
                                placeholder="Repeat your new password">
                            <button type="button" @click="togglePasswordVisibility('password_confirmation')" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                                <i class="fas" :class="isPasswordVisible('password_confirmation') ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        <p x-cloak x-show="errors.password_confirmation" class="mt-2 text-xs font-medium text-[var(--auth-error)]" x-text="errors.password_confirmation"></p>
                    </div>
                    <button type="submit" :disabled="loading"
                        class="inline-flex min-h-[54px] w-full items-center justify-center rounded-2xl bg-[var(--auth-deep)] px-5 py-3.5 text-base font-semibold text-white transition hover:bg-[var(--auth-ink)] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[var(--auth-deep)] focus:ring-offset-2"
                        :class="loading ? 'cursor-wait opacity-80' : ''">
                        <span x-show="!loading">Reset password</span>
                        <span x-cloak x-show="loading" class="inline-flex items-center gap-2"><i class="fas fa-circle-notch animate-spin"></i>Updating...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
