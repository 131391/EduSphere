@extends('layouts.app')

@section('title', 'EduSphere | Multi-tenant School ERP SaaS')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
<style>
    :root {
        --brand-ink: #10231c;
        --brand-deep: #17392e;
        --brand-mint: #d7f7d7;
        --brand-lime: #c8ff6f;
        --brand-sand: #fff8e8;
        --brand-coral: #ff8f6b;
        --brand-sky: #d7eef7;
        --brand-line: rgba(16, 35, 28, 0.1);
    }

    .marketing-shell {
        font-family: 'DM Sans', sans-serif;
        color: var(--brand-ink);
        background:
            radial-gradient(circle at top left, rgba(200, 255, 111, 0.25), transparent 34%),
            radial-gradient(circle at top right, rgba(255, 143, 107, 0.18), transparent 30%),
            linear-gradient(180deg, #fffdf6 0%, #fff8e8 45%, #f9f6ef 100%);
    }

    .marketing-shell h1,
    .marketing-shell h2,
    .marketing-shell h3,
    .marketing-shell .display-font {
        font-family: 'Space Grotesk', sans-serif;
    }

    .orb {
        position: absolute;
        border-radius: 9999px;
        filter: blur(20px);
        opacity: 0.7;
        animation: drift 9s ease-in-out infinite;
    }

    .grid-noise {
        background-image:
            linear-gradient(rgba(16, 35, 28, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(16, 35, 28, 0.05) 1px, transparent 1px);
        background-size: 22px 22px;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.72);
        backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0 28px 80px rgba(16, 35, 28, 0.12);
    }

    .feature-card,
    .portal-card,
    .stack-card {
        transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
    }

    .feature-card:hover,
    .portal-card:hover,
    .stack-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 45px rgba(16, 35, 28, 0.1);
    }

    .section-divider {
        background: linear-gradient(90deg, transparent, rgba(16, 35, 28, 0.14), transparent);
        height: 1px;
    }

    @keyframes drift {
        0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
        50% { transform: translate3d(0, -18px, 0) scale(1.06); }
    }
</style>
@endpush

@section('content')
@php
    $productName = config('app.name', 'EduSphere');
    $tenantSchool = $school ?? null;
    $tenantName = $tenantSchool?->name ?: $productName;
    $tenantLogo = $tenantSchool?->logo ? asset('storage/' . $tenantSchool->logo) : null;
    $host = request()->getHost();
    $baseHost = preg_replace('/^www\./', '', $host);
    $subdomainExample = $tenantSchool ? $host : 'greenfield.' . $baseHost;
    $heroEyebrow = $tenantSchool
        ? 'Tenant workspace for ' . $tenantSchool->name
        : 'Built for multi-branch schools on isolated subdomains';
    $heroTitle = $tenantSchool
        ? $tenantSchool->name . ' now has a dedicated digital campus.'
        : 'One secure operating system for every school under your SaaS umbrella.';
    $heroBody = $tenantSchool
        ? $tenantSchool->name . ' can manage admissions, registrations, fee collection, attendance, examinations, reports, and family portals from its own branded subdomain workspace.'
        : $productName . ' connects admissions, registrations, fee collection, attendance, examinations, reports, and family portals while keeping every school on its own branded subdomain and tenant-safe workspace.';
@endphp

<div class="marketing-shell min-h-screen">
    <div class="relative">
        <div class="orb -left-12 top-24 h-44 w-44 bg-lime-200"></div>
        <div class="orb right-0 top-0 h-56 w-56 bg-orange-200" style="animation-delay: 1.4s;"></div>
        <div class="orb left-1/2 top-[32rem] h-52 w-52 bg-cyan-100" style="animation-delay: 2.2s;"></div>

        <header class="sticky top-0 z-50 border-b border-black/5 bg-white/75 backdrop-blur-xl" x-data="{ open: false }">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    @if($tenantLogo)
                        <div class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-black/10 bg-white shadow-sm">
                            <img src="{{ $tenantLogo }}" alt="{{ $tenantName }} logo" class="h-full w-full object-cover">
                        </div>
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-black/10 bg-[var(--brand-lime)] text-[var(--brand-deep)] shadow-sm">
                            <i class="fas fa-school text-lg"></i>
                        </div>
                    @endif
                    <div>
                        <p class="display-font text-lg font-bold tracking-tight">{{ $tenantName }}</p>
                        <p class="text-xs uppercase tracking-[0.28em] text-slate-500">{{ $tenantSchool ? 'Tenant School Portal' : 'School ERP SaaS' }}</p>
                    </div>
                </a>

                <nav class="hidden items-center gap-8 text-sm font-medium text-slate-700 lg:flex">
                    <a href="#platform" class="transition hover:text-[var(--brand-deep)]">Platform</a>
                    <a href="#modules" class="transition hover:text-[var(--brand-deep)]">Modules</a>
                    <a href="#tenancy" class="transition hover:text-[var(--brand-deep)]">Subdomain SaaS</a>
                    <a href="#roles" class="transition hover:text-[var(--brand-deep)]">Portals</a>
                </nav>

                <div class="hidden items-center gap-3 lg:flex">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-full border border-[var(--brand-line)] bg-white px-5 py-2.5 text-sm font-semibold text-[var(--brand-ink)] transition hover:-translate-y-0.5 hover:shadow-md">
                            Open dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full border border-[var(--brand-line)] bg-white px-5 py-2.5 text-sm font-semibold text-[var(--brand-ink)] transition hover:-translate-y-0.5 hover:shadow-md">
                            Sign in
                        </a>
                        <a href="{{ route('login') }}" class="rounded-full bg-[var(--brand-deep)] px-5 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-[var(--brand-ink)] hover:shadow-lg">
                            Request demo
                        </a>
                    @endauth
                </div>

                <button @click="open = !open" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-black/10 bg-white text-slate-700 lg:hidden">
                    <i class="fas" :class="open ? 'fa-times' : 'fa-bars'"></i>
                </button>
            </div>

            <div x-show="open" x-transition class="border-t border-black/5 bg-white px-5 py-4 lg:hidden" style="display: none;">
                <div class="flex flex-col gap-3 text-sm font-medium text-slate-700">
                    <a href="#platform" @click="open = false">Platform</a>
                    <a href="#modules" @click="open = false">Modules</a>
                    <a href="#tenancy" @click="open = false">Subdomain SaaS</a>
                    <a href="#roles" @click="open = false">Portals</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="mt-2 rounded-full bg-[var(--brand-deep)] px-5 py-3 text-center font-semibold text-white">
                            Open dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="mt-2 rounded-full bg-[var(--brand-deep)] px-5 py-3 text-center font-semibold text-white">
                            Sign in to your school
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            <section class="relative px-5 pb-20 pt-10 lg:px-8 lg:pb-28 lg:pt-16">
                <div class="mx-auto grid max-w-7xl gap-14 lg:grid-cols-[1.08fr_0.92fr] lg:items-center">
                    <div class="relative">
                        <div class="mb-6 inline-flex items-center gap-3 rounded-full border border-black/10 bg-white/80 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-slate-600 shadow-sm">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            {{ $heroEyebrow }}
                        </div>

                        <h1 class="max-w-4xl text-5xl font-bold leading-[0.95] tracking-tight text-[var(--brand-ink)] md:text-6xl xl:text-7xl">
                            {{ $heroTitle }}
                        </h1>

                        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-700 md:text-xl">
                            {{ $heroBody }}
                        </p>

                        <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-full bg-[var(--brand-deep)] px-7 py-4 text-base font-semibold text-white transition hover:-translate-y-0.5 hover:bg-[var(--brand-ink)] hover:shadow-xl">
                                    Enter your workspace
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full bg-[var(--brand-deep)] px-7 py-4 text-base font-semibold text-white transition hover:-translate-y-0.5 hover:bg-[var(--brand-ink)] hover:shadow-xl">
                                    Launch a school demo
                                </a>
                            @endauth
                            <a href="#tenancy" class="inline-flex items-center justify-center rounded-full border border-black/10 bg-white px-7 py-4 text-base font-semibold text-[var(--brand-ink)] transition hover:-translate-y-0.5 hover:shadow-lg">
                                Explore the SaaS model
                            </a>
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-3xl border border-black/10 bg-white/85 p-5 shadow-sm">
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Tenant model</p>
                                <p class="mt-3 display-font text-2xl font-bold">{{ $tenantSchool ? $tenantSchool->subdomain : 'Subdomain-based' }}</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $tenantSchool ? 'This school is being served from its own branded tenant URL.' : 'Each school operates on its own dedicated URL and data boundary.' }}</p>
                            </div>
                            <div class="rounded-3xl border border-black/10 bg-white/85 p-5 shadow-sm">
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Finance flow</p>
                                <p class="mt-3 display-font text-2xl font-bold">Registration to receipt</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Move enquiries into admissions, then collect school-specific fees in one journey.</p>
                            </div>
                            <div class="rounded-3xl border border-black/10 bg-white/85 p-5 shadow-sm">
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Role access</p>
                                <p class="mt-3 display-font text-2xl font-bold">6 focused portals</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Super admin, school admin, receptionist, teacher, student, and parent experiences.</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="glass-card relative overflow-hidden rounded-[2rem] p-5 md:p-7">
                            <div class="grid-noise absolute inset-0 opacity-50"></div>

                            <div class="relative space-y-5">
                                <div class="flex items-center justify-between rounded-[1.75rem] border border-black/10 bg-white/80 px-5 py-4">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Live workspace</p>
                                        <p class="mt-1 display-font text-2xl font-bold">{{ $subdomainExample }}</p>
                                    </div>
                                    <div class="rounded-full bg-[var(--brand-lime)] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[var(--brand-deep)]">
                                        Tenant active
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-[1.2fr_0.8fr]">
                                    <div class="rounded-[1.75rem] border border-black/10 bg-[var(--brand-deep)] p-6 text-white shadow-xl">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.28em] text-emerald-100/80">School control room</p>
                                                <p class="mt-2 display-font text-3xl font-bold">Admissions, fees, reports</p>
                                            </div>
                                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10">
                                                <i class="fas fa-layer-group text-xl"></i>
                                            </div>
                                        </div>

                                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                            <div class="rounded-2xl bg-white/10 p-4">
                                                <p class="text-xs uppercase tracking-[0.2em] text-emerald-100/80">Today's queue</p>
                                                <p class="mt-2 text-3xl font-bold">42</p>
                                                <p class="mt-1 text-sm text-emerald-50/80">Registrations, fee reminders, approvals</p>
                                            </div>
                                            <div class="rounded-2xl bg-white/10 p-4">
                                                <p class="text-xs uppercase tracking-[0.2em] text-emerald-100/80">Receipts issued</p>
                                                <p class="mt-2 text-3xl font-bold">128</p>
                                                <p class="mt-1 text-sm text-emerald-50/80">School-aware payment methods and notes</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div class="rounded-[1.5rem] border border-black/10 bg-[var(--brand-sky)] p-5">
                                            <p class="text-xs uppercase tracking-[0.24em] text-slate-600">Role portal</p>
                                            <p class="mt-2 display-font text-xl font-bold">Receptionist desk</p>
                                            <p class="mt-2 text-sm leading-6 text-slate-700">Student enquiries, admissions, visitors, transport, and front-office execution.</p>
                                        </div>
                                        <div class="rounded-[1.5rem] border border-black/10 bg-[var(--brand-sand)] p-5">
                                            <p class="text-xs uppercase tracking-[0.24em] text-slate-600">Family portal</p>
                                            <p class="mt-2 display-font text-xl font-bold">Parent and student access</p>
                                            <p class="mt-2 text-sm leading-6 text-slate-700">Fees, attendance, results, and academic visibility without staff clutter.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[1.75rem] border border-black/10 bg-white/80 p-5">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="rounded-full bg-[var(--brand-mint)] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[var(--brand-deep)]">Admissions</span>
                                        <i class="fas fa-arrow-right text-slate-400"></i>
                                        <span class="rounded-full bg-[var(--brand-sand)] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[var(--brand-deep)]">Registration</span>
                                        <i class="fas fa-arrow-right text-slate-400"></i>
                                        <span class="rounded-full bg-[var(--brand-sky)] px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[var(--brand-deep)]">Fee collection</span>
                                        <i class="fas fa-arrow-right text-slate-400"></i>
                                        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-[var(--brand-deep)]">Receipts & reports</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="platform" class="px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-10 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Platform fit</p>
                            <h2 class="mt-3 text-3xl font-bold tracking-tight md:text-5xl">Designed around how schools actually operate.</h2>
                        </div>
                        <p class="max-w-2xl text-base leading-7 text-slate-600 md:text-lg">
                            The product is not just a dashboard. It supports the entire school lifecycle: enquiry, registration, admission, fee operations, academic tracking, staff workflows, and parent visibility.
                        </p>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-3">
                        <article class="stack-card rounded-[1.8rem] border border-black/10 bg-white p-7">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--brand-lime)] text-[var(--brand-deep)]">
                                <i class="fas fa-route text-xl"></i>
                            </div>
                            <h3 class="mt-5 text-2xl font-bold">Front-office to finance continuity</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">
                                Student enquiries can move through registration and admission with fee logic, payment methods, receipt notes, and school-specific settings carried through the process.
                            </p>
                        </article>

                        <article class="stack-card rounded-[1.8rem] border border-black/10 bg-white p-7">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--brand-sky)] text-[var(--brand-deep)]">
                                <i class="fas fa-user-shield text-xl"></i>
                            </div>
                            <h3 class="mt-5 text-2xl font-bold">Role-shaped experiences</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">
                                Super admins manage the SaaS, school admins configure the tenant, receptionists handle operations, and teachers, students, and parents each get focused access.
                            </p>
                        </article>

                        <article class="stack-card rounded-[1.8rem] border border-black/10 bg-white p-7">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--brand-coral)] text-white">
                                <i class="fas fa-shield-alt text-xl"></i>
                            </div>
                            <h3 class="mt-5 text-2xl font-bold">Tenant-safe by design</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">
                                School data stays scoped to the active tenant context, with school-aware settings, policies, APIs, and workflow protections baked into the product model.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="px-5 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="section-divider"></div>
                </div>
            </section>

            <section id="modules" class="px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-10 max-w-3xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Modules</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight md:text-5xl">A complete operating stack for modern schools.</h2>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        <article class="feature-card rounded-[1.8rem] border border-black/10 bg-white p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-2xl font-bold">Admissions engine</h3>
                                <i class="fas fa-user-plus text-xl text-emerald-600"></i>
                            </div>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Manage student enquiries, registrations, document collection, admissions, and school-specific fees from one pipeline.</p>
                        </article>

                        <article class="feature-card rounded-[1.8rem] border border-black/10 bg-white p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-2xl font-bold">Fee operations</h3>
                                <i class="fas fa-receipt text-xl text-amber-600"></i>
                            </div>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Configure fee types, payment methods, late fees, receipt notes, and daily collection workflows at the tenant level.</p>
                        </article>

                        <article class="feature-card rounded-[1.8rem] border border-black/10 bg-white p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-2xl font-bold">Academic control</h3>
                                <i class="fas fa-book-open text-xl text-sky-600"></i>
                            </div>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Handle classes, sections, subjects, academic years, examinations, marks entry, tabulation, and result publishing.</p>
                        </article>

                        <article class="feature-card rounded-[1.8rem] border border-black/10 bg-white p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-2xl font-bold">Attendance and reports</h3>
                                <i class="fas fa-chart-line text-xl text-rose-600"></i>
                            </div>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Track attendance, build daily and monthly reports, and give students and parents a clear operational view.</p>
                        </article>

                        <article class="feature-card rounded-[1.8rem] border border-black/10 bg-white p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-2xl font-bold">Campus operations</h3>
                                <i class="fas fa-bus text-xl text-indigo-600"></i>
                            </div>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Support transport, hostel, visitor, staff, library, and related front-office services without leaving the tenant workspace.</p>
                        </article>

                        <article class="feature-card rounded-[1.8rem] border border-black/10 bg-[var(--brand-deep)] p-6 text-white">
                            <div class="flex items-center justify-between">
                                <h3 class="text-2xl font-bold">SaaS control center</h3>
                                <i class="fas fa-globe text-xl text-[var(--brand-lime)]"></i>
                            </div>
                            <p class="mt-3 text-sm leading-7 text-emerald-50/85">Provision schools, manage features, assign subdomains, and keep every tenant independent while running one shared platform.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="tenancy" class="px-5 py-16 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Subdomain SaaS model</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight md:text-5xl">Every school gets its own address, identity, and operational context.</h2>
                        <p class="mt-5 text-base leading-8 text-slate-600 md:text-lg">
                            EduSphere is structured as a multi-tenant SaaS. A school logs in through its own subdomain, sees only its own users and academic setup, and manages its own configuration for admissions, fees, sessions, and day-to-day operations.
                        </p>

                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-[1.5rem] border border-black/10 bg-white p-5">
                                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">School domain</p>
                                <p class="mt-2 display-font text-xl font-bold">{{ $subdomainExample }}</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">A clean school-specific entry point for staff, families, and operational workflows.</p>
                            </div>
                            <div class="rounded-[1.5rem] border border-black/10 bg-white p-5">
                                <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Shared SaaS core</p>
                                <p class="mt-2 display-font text-xl font-bold">One platform, many schools</p>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Central provisioning, school-aware routing, and isolated tenant operations inside a single product.</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-black/10 bg-white p-6 shadow-[0_24px_70px_rgba(16,35,28,0.08)] md:p-8">
                        <div class="grid gap-4">
                            <div class="rounded-[1.5rem] border border-black/10 bg-[var(--brand-sand)] p-5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.28em] text-slate-500">1. Provision school</p>
                                        <p class="mt-1 text-xl font-bold">Create the tenant</p>
                                    </div>
                                    <i class="fas fa-school text-lg text-[var(--brand-deep)]"></i>
                                </div>
                                <p class="mt-3 text-sm leading-7 text-slate-600">Add a school, assign its subdomain, activate features, and configure subscription-level readiness.</p>
                            </div>

                            <div class="rounded-[1.5rem] border border-black/10 bg-[var(--brand-mint)] p-5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.28em] text-slate-500">2. Configure school</p>
                                        <p class="mt-1 text-xl font-bold">Set local policies and finance rules</p>
                                    </div>
                                    <i class="fas fa-sliders-h text-lg text-[var(--brand-deep)]"></i>
                                </div>
                                <p class="mt-3 text-sm leading-7 text-slate-600">School admins define sessions, fee types, payment methods, receipt notes, branding, and workflow settings.</p>
                            </div>

                            <div class="rounded-[1.5rem] border border-black/10 bg-[var(--brand-sky)] p-5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs uppercase tracking-[0.28em] text-slate-500">3. Run daily operations</p>
                                        <p class="mt-1 text-xl font-bold">Serve staff, students, and parents</p>
                                    </div>
                                    <i class="fas fa-users-cog text-lg text-[var(--brand-deep)]"></i>
                                </div>
                                <p class="mt-3 text-sm leading-7 text-slate-600">Each role lands in the right portal and works with school-specific data instead of a noisy shared admin panel.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="roles" class="px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-10 max-w-3xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-slate-500">Role portals</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight md:text-5xl">Each user sees the school through the right lens.</h2>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        <article class="portal-card rounded-[1.8rem] border border-black/10 bg-white p-6">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">SaaS owner</p>
                            <h3 class="mt-3 text-2xl font-bold">Super admin</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Manages schools, features, system-level settings, and overall platform operations.</p>
                        </article>

                        <article class="portal-card rounded-[1.8rem] border border-black/10 bg-white p-6">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Tenant operator</p>
                            <h3 class="mt-3 text-2xl font-bold">School admin</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Controls users, settings, fee logic, academic structure, and the school’s daily execution.</p>
                        </article>

                        <article class="portal-card rounded-[1.8rem] border border-black/10 bg-white p-6">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Front office</p>
                            <h3 class="mt-3 text-2xl font-bold">Receptionist</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Handles enquiries, admissions, registrations, visitors, transport, and operational intake.</p>
                        </article>

                        <article class="portal-card rounded-[1.8rem] border border-black/10 bg-white p-6">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Academic staff</p>
                            <h3 class="mt-3 text-2xl font-bold">Teacher</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Focuses on attendance, students, and academic responsibilities without admin overhead.</p>
                        </article>

                        <article class="portal-card rounded-[1.8rem] border border-black/10 bg-white p-6">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Learner view</p>
                            <h3 class="mt-3 text-2xl font-bold">Student</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Sees fees, attendance, timetable, and results in a simple self-service experience.</p>
                        </article>

                        <article class="portal-card rounded-[1.8rem] border border-black/10 bg-white p-6">
                            <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Family access</p>
                            <h3 class="mt-3 text-2xl font-bold">Parent</h3>
                            <p class="mt-3 text-sm leading-7 text-slate-600">Tracks child progress, fees, attendance, and academic outcomes from a family-first portal.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="px-5 pb-24 pt-8 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="rounded-[2.25rem] bg-[var(--brand-deep)] px-6 py-10 text-white shadow-[0_30px_80px_rgba(16,35,28,0.18)] md:px-10 md:py-12">
                        <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-center">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-emerald-100/80">Ready to launch</p>
                                <h2 class="mt-3 text-3xl font-bold tracking-tight md:text-5xl">Run many schools like a product company, not a spreadsheet company.</h2>
                                <p class="mt-4 max-w-2xl text-base leading-8 text-emerald-50/85 md:text-lg">
                                    {{ $tenantSchool ? 'This tenant can now operate with its own identity, school-specific settings, and role-based portals from a dedicated subdomain.' : 'Give each school its own subdomain, keep operations structured by role, and let one platform carry admissions, finance, academics, and reporting end to end.' }}
                                </p>
                            </div>

                            <div class="flex flex-col gap-4 lg:items-end">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-full bg-[var(--brand-lime)] px-7 py-4 text-base font-semibold text-[var(--brand-deep)] transition hover:-translate-y-0.5 hover:shadow-xl">
                                        Open your workspace
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full bg-[var(--brand-lime)] px-7 py-4 text-base font-semibold text-[var(--brand-deep)] transition hover:-translate-y-0.5 hover:shadow-xl">
                                        Sign in to your school
                                    </a>
                                @endauth
                                <p class="text-sm text-emerald-50/80">
                                    Example tenant:
                                    <span class="font-semibold text-white">{{ $subdomainExample }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-black/5 bg-white/70 px-5 py-8 backdrop-blur-sm lg:px-8">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 text-sm text-slate-600 md:flex-row md:items-center md:justify-between">
                <div>
                    <span class="display-font font-bold text-[var(--brand-ink)]">{{ $productName }}</span>
                    @if($tenantSchool)
                        <span class="mx-2 text-slate-300">/</span>
                        {{ $tenantSchool->name }}
                    @endif
                    <span class="mx-2 text-slate-300">/</span>
                    {{ $tenantSchool ? 'Tenant-branded school workspace' : 'Multi-tenant school ERP SaaS' }}
                </div>
                <div class="flex flex-wrap items-center gap-4">
                    <a href="#platform" class="transition hover:text-[var(--brand-deep)]">Platform</a>
                    <a href="#modules" class="transition hover:text-[var(--brand-deep)]">Modules</a>
                    <a href="#tenancy" class="transition hover:text-[var(--brand-deep)]">Subdomain SaaS</a>
                    <a href="{{ route('login') }}" class="font-semibold text-[var(--brand-deep)]">Login</a>
                </div>
            </div>
        </footer>
    </div>
</div>
@endsection
