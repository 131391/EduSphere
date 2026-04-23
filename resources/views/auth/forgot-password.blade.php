@extends('layouts.auth')

@section('title', 'Forgot Password - ' . config('app.name'))

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
        <div class="mb-6 text-center">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-3 rounded-full border border-[var(--auth-line)] bg-white/80 px-4 py-2 text-sm font-semibold text-[var(--auth-ink)] transition hover:shadow-md">
                <i class="fas fa-arrow-left text-xs"></i>
                Back to login
            </a>
        </div>
        <div class="auth-card overflow-hidden rounded-[1.75rem]"
            x-data="ajaxAuthForm({
                url: '{{ route('password.email') }}',
                initialForm: { email: @js(old('email', '')) },
                validate() {
                    const errors = {};
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!this.form.email) {
                        errors.email = 'Email address is required.';
                    } else if (!emailPattern.test(this.form.email)) {
                        errors.email = 'Enter a valid email address.';
                    }
                    return errors;
                },
                onSuccess(data) {
                    this.setMessage(data?.message || 'Reset link sent. Check your email.', 'success');
                },
                validationMessage: 'Please enter a valid email address.',
                errorMessage: 'We could not send the reset link right now.',
            })">
            <div class="px-6 pt-7 text-center sm:px-8">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center overflow-hidden rounded-3xl border border-[var(--auth-line)] bg-white shadow-sm">
                    @if($tenantLogo)
                        <img src="{{ $tenantLogo }}" alt="{{ $tenantName }} logo" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-[var(--auth-accent)] text-[var(--auth-deep)]">
                            <i class="fas fa-envelope-open-text text-3xl"></i>
                        </div>
                    @endif
                </div>
                <p class="text-xs font-bold uppercase tracking-[0.28em] text-[var(--auth-muted)]">Password recovery</p>
                <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-[var(--auth-ink)]">{{ $tenantName }}</h1>
                <p class="mt-3 text-sm leading-6 text-[var(--auth-muted)]">Enter your email address and we’ll send you a password reset link.</p>
            </div>
            <div class="px-6 py-6 sm:px-8">
                <div x-cloak x-show="message" x-transition class="mb-5 rounded-2xl border px-4 py-3 text-sm"
                    :class="messageType === 'error' ? 'border-red-200 bg-red-50 text-[var(--auth-error)]' : 'border-emerald-200 bg-emerald-50 text-emerald-700'">
                    <div class="flex items-start gap-3">
                        <i class="fas mt-0.5" :class="messageType === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'"></i>
                        <p x-text="message"></p>
                    </div>
                </div>
                <form method="POST" action="{{ route('password.email') }}" class="space-y-4" novalidate @submit.prevent="submit">
                    @csrf
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
                    <button type="submit" :disabled="loading"
                        class="inline-flex min-h-[54px] w-full items-center justify-center rounded-2xl bg-[var(--auth-deep)] px-5 py-3.5 text-base font-semibold text-white transition hover:bg-[var(--auth-ink)] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[var(--auth-deep)] focus:ring-offset-2"
                        :class="loading ? 'cursor-wait opacity-80' : ''">
                        <span x-show="!loading">Send reset link</span>
                        <span x-cloak x-show="loading" class="inline-flex items-center gap-2"><i class="fas fa-circle-notch animate-spin"></i>Sending...</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
