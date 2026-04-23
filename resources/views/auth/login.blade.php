@extends('layouts.auth')

@section('title', 'Login - ' . config('app.name'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --login-ink: #163126;
        --login-deep: #1e4a39;
        --login-soft: #f6f8f4;
        --login-card: rgba(255, 255, 255, 0.9);
        --login-line: rgba(22, 49, 38, 0.12);
        --login-accent: #c8ff6f;
        --login-muted: #617369;
        --login-error: #c94949;
    }

    .friendly-login {
        min-height: 100vh;
        font-family: 'DM Sans', sans-serif;
        background:
            radial-gradient(circle at top left, rgba(200, 255, 111, 0.22), transparent 28%),
            radial-gradient(circle at bottom right, rgba(182, 224, 255, 0.22), transparent 26%),
            linear-gradient(180deg, #fdfdf8 0%, #f4f6f1 100%);
        color: var(--login-ink);
    }

    .friendly-login-card {
        background: var(--login-card);
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 24px 60px rgba(22, 49, 38, 0.1);
        backdrop-filter: blur(16px);
    }

    .friendly-field:focus-within {
        border-color: rgba(30, 74, 57, 0.35);
        box-shadow: 0 0 0 5px rgba(200, 255, 111, 0.24);
    }
</style>
@endpush

@section('content')
@php
    $tenantSchool = $school ?? null;
    $tenantLogo = $tenantSchool?->logo ? asset('storage/' . $tenantSchool->logo) : null;
    $tenantName = $tenantSchool?->name ?: config('app.name', 'EduSphere');
    $tenantHost = request()->getHost();
@endphp

<div class="friendly-login flex items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 rounded-full border border-[var(--login-line)] bg-white/80 px-4 py-2 text-sm font-semibold text-[var(--login-ink)] transition hover:shadow-md">
                <i class="fas fa-arrow-left text-xs"></i>
                Back to website
            </a>
        </div>

        <div class="friendly-login-card overflow-hidden rounded-[1.75rem]"
            x-data="ajaxAuthForm({
                initialForm: {
                    email: @js(old('email', '')),
                    password: '',
                    remember: @js((bool) old('remember')),
                },
                validate() {
                    const errors = {};
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (!this.form.email) {
                        errors.email = 'Email address is required.';
                    } else if (!emailPattern.test(this.form.email)) {
                        errors.email = 'Enter a valid email address.';
                    }

                    if (!this.form.password) {
                        errors.password = 'Password is required.';
                    }

                    return errors;
                },
                transformPayload() {
                    return {
                        email: this.form.email,
                        password: this.form.password,
                        remember: this.form.remember ? 1 : 0,
                    };
                },
                async onSuccess(data) {
                    window.location.assign(data?.redirect || '{{ route('dashboard') }}');
                },
                errorMessage: 'Something went wrong while signing you in. Please try again.',
                validationMessage: 'Please correct the highlighted fields and try again.',
            })">
            <div class="px-6 pt-7 text-center sm:px-8">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center overflow-hidden rounded-3xl border border-[var(--login-line)] bg-white shadow-sm">
                    @if($tenantLogo)
                        <img src="{{ $tenantLogo }}" alt="{{ $tenantName }} logo" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-[var(--login-accent)] text-[var(--login-deep)]">
                            <i class="fas fa-school text-3xl"></i>
                        </div>
                    @endif
                </div>

                <p class="text-xs font-bold uppercase tracking-[0.28em] text-[var(--login-muted)]">
                    {{ $tenantSchool ? 'School Login' : 'Platform Login' }}
                </p>

                <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-[var(--login-ink)]">
                    {{ $tenantName }}
                </h1>

                <p class="mt-3 text-sm leading-6 text-[var(--login-muted)]">
                    {{ $tenantSchool
                        ? 'Sign in to continue to your school workspace.'
                        : 'Sign in with your official account to continue.' }}
                </p>

                @if($tenantSchool)
                    <div class="mt-4 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                        <i class="fas fa-globe text-[10px]"></i>
                        {{ $tenantHost }}
                    </div>
                @endif
            </div>

            <div class="px-6 py-6 sm:px-8">
                @if ($errors->any())
                    <div x-show="!hasClientErrors" class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-[var(--login-error)]">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-circle-exclamation mt-0.5"></i>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                    <div x-cloak x-show="message" x-transition class="mb-5 rounded-2xl border px-4 py-3 text-sm"
                        :class="messageType === 'error' ? 'border-red-200 bg-red-50 text-[var(--login-error)]' : 'border-emerald-200 bg-emerald-50 text-emerald-700'">
                    <div class="flex items-start gap-3">
                        <i class="fas mt-0.5" :class="messageType === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'"></i>
                        <p x-text="message"></p>
                    </div>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-4" novalidate @submit.prevent="submit">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-[var(--login-ink)]">Email address</label>
                        <div class="friendly-field flex min-h-[54px] items-center gap-3 rounded-2xl border bg-white px-4 transition"
                            :class="errors.email ? 'border-red-300' : 'border-[var(--login-line)]'">
                            <i class="fas fa-envelope text-slate-400"></i>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                x-model.trim="form.email"
                                @input="clearError('email')"
                                class="w-full border-0 bg-transparent py-3.5 text-base text-[var(--login-ink)] placeholder:text-slate-400 focus:outline-none focus:ring-0"
                                placeholder="Enter your email"
                            >
                        </div>
                        <p x-cloak x-show="errors.email" class="mt-2 text-xs font-medium text-[var(--login-error)]" x-text="errors.email"></p>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label for="password" class="block text-sm font-semibold text-[var(--login-ink)]">Password</label>
                            <span class="text-xs text-[var(--login-muted)]">Case-sensitive</span>
                        </div>

                        <div class="friendly-field flex min-h-[54px] items-center gap-3 rounded-2xl border bg-white px-4 transition"
                            :class="errors.password ? 'border-red-300' : 'border-[var(--login-line)]'">
                            <i class="fas fa-lock text-slate-400"></i>
                            <input
                                id="password"
                                name="password"
                                :type="isPasswordVisible('password') ? 'text' : 'password'"
                                autocomplete="current-password"
                                required
                                x-model="form.password"
                                @input="clearError('password')"
                                @keydown="syncCapsLock"
                                @keyup="syncCapsLock"
                                @blur="capsLockOn = false"
                                class="w-full border-0 bg-transparent py-3.5 text-base text-[var(--login-ink)] placeholder:text-slate-400 focus:outline-none focus:ring-0"
                                placeholder="Enter your password"
                            >
                            <button type="button"
                                @click="togglePasswordVisibility('password')"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                :aria-label="isPasswordVisible('password') ? 'Hide password' : 'Show password'">
                                <i class="fas" :class="isPasswordVisible('password') ? 'fa-eye-slash' : 'fa-eye'" aria-hidden="true"></i>
                            </button>
                        </div>
                        <p x-cloak x-show="capsLockOn" class="mt-2 text-xs font-medium text-amber-700">
                            Caps Lock is on.
                        </p>
                        <p x-cloak x-show="errors.password" class="mt-2 text-xs font-medium text-[var(--login-error)]" x-text="errors.password"></p>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <label class="inline-flex items-center gap-3 text-sm text-[var(--login-muted)]">
                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                value="1"
                                x-model="form.remember"
                                class="h-4 w-4 rounded border-gray-300 text-[var(--login-deep)] focus:ring-[var(--login-deep)]"
                            >
                            Remember me
                        </label>
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-[var(--login-deep)] hover:text-[var(--login-ink)]">
                            Forgot password?
                        </a>
                    </div>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="inline-flex min-h-[54px] w-full items-center justify-center rounded-2xl bg-[var(--login-deep)] px-5 py-3.5 text-base font-semibold text-white transition hover:bg-[var(--login-ink)] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[var(--login-deep)] focus:ring-offset-2"
                        :class="loading ? 'cursor-wait opacity-80' : ''"
                    >
                        <span x-show="!loading">Sign in</span>
                        <span x-cloak x-show="loading" class="inline-flex items-center gap-2">
                            <i class="fas fa-circle-notch animate-spin"></i>
                            Signing in...
                        </span>
                    </button>
                </form>

                <div class="mt-5 rounded-2xl bg-[var(--login-soft)] px-4 py-3 text-sm text-[var(--login-muted)]">
                    {{ $tenantSchool
                        ? 'Make sure you are signing in on the correct school subdomain.'
                        : 'If your school has a dedicated subdomain, use that URL for the correct school login.' }}
                </div>
            </div>

            <div class="border-t border-black/5 bg-white/70 px-6 py-4 text-center text-xs text-[var(--login-muted)] sm:px-8">
                {{ date('Y') }} © {{ $tenantName }}
            </div>
        </div>
    </div>
</div>
@endsection
