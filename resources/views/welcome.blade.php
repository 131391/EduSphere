@extends('layouts.app')

@section('title', 'Welcome - ' . config('app.name'))

@section('content')
<div class="min-h-screen flex flex-col bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <i class="fas fa-graduation-cap text-blue-600 text-3xl mr-2"></i>
                    <span class="text-2xl font-bold text-gray-800">EduSphere</span>
                </div>
                <div>
                    @auth
                        <a href="{{ route('home') }}" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-sm">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-sm">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                    <div class="sm:text-center lg:text-left">
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                            <span class="block xl:inline">Transform Your School</span>
                            <span class="block text-blue-600 xl:inline">Management Today</span>
                        </h1>
                        <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                            The all-in-one ERP solution designed to streamline administration, enhance learning, and simplify communication for schools of all sizes.
                        </p>
                        <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                            <div class="rounded-md shadow">
                                @auth
                                    <a href="{{ route('home') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg">
                                        Go to Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg">
                                        Get Started
                                    </a>
                                @endauth
                            </div>
                            <div class="mt-3 sm:mt-0 sm:ml-3">
                                <a href="#" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 md:py-4 md:text-lg">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 bg-blue-50 flex items-center justify-center">
            <div class="grid grid-cols-2 gap-8 p-8 opacity-80">
                <div class="bg-white p-6 rounded-2xl shadow-lg transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <i class="fas fa-user-graduate text-5xl text-blue-500 mb-4"></i>
                    <h3 class="font-bold text-gray-800">Students</h3>
                    <p class="text-sm text-gray-500">Comprehensive profiles & records</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg transform -rotate-3 hover:rotate-0 transition-transform duration-300 mt-12">
                    <i class="fas fa-chalkboard-teacher text-5xl text-green-500 mb-4"></i>
                    <h3 class="font-bold text-gray-800">Teachers</h3>
                    <p class="text-sm text-gray-500">Efficient staff management</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg transform -rotate-3 hover:rotate-0 transition-transform duration-300">
                    <i class="fas fa-file-invoice-dollar text-5xl text-yellow-500 mb-4"></i>
                    <h3 class="font-bold text-gray-800">Fees</h3>
                    <p class="text-sm text-gray-500">Automated billing & payments</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-lg transform rotate-3 hover:rotate-0 transition-transform duration-300 mt-12">
                    <i class="fas fa-chart-line text-5xl text-purple-500 mb-4"></i>
                    <h3 class="font-bold text-gray-800">Reports</h3>
                    <p class="text-sm text-gray-500">Data-driven insights</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:text-center">
                <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Features</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Everything you need to run your school
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                    EduSphere provides a complete suite of modules to manage every aspect of your educational institution.
                </p>
            </div>

            <div class="mt-10">
                <dl class="space-y-10 md:space-y-0 md:grid md:grid-cols-2 md:gap-x-8 md:gap-y-10">
                    <div class="relative">
                        <dt>
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                                <i class="fas fa-users"></i>
                            </div>
                            <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Student Management</p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-gray-500">
                            Manage admissions, student profiles, attendance, and disciplinary records with ease.
                        </dd>
                    </div>

                    <div class="relative">
                        <dt>
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-green-500 text-white">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Fee Management</p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-gray-500">
                            Streamline fee collection, generate invoices, track dues, and manage online payments.
                        </dd>
                    </div>

                    <div class="relative">
                        <dt>
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-purple-500 text-white">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Academic Excellence</p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-gray-500">
                            Handle examinations, grading, report cards, and academic calendars efficiently.
                        </dd>
                    </div>

                    <div class="relative">
                        <dt>
                            <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-yellow-500 text-white">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Staff & HR</p>
                        </dt>
                        <dd class="mt-2 ml-16 text-base text-gray-500">
                            Manage teacher profiles, payroll, leave requests, and timetables in one place.
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-auto">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-graduation-cap text-blue-400 text-2xl mr-2"></i>
                        <span class="text-xl font-bold">EduSphere</span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Empowering education through technology.
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Features</a></li>
                        <li><a href="#" class="text-base text-gray-300 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase mb-4">Connect</h3>
                    <div class="flex space-x-6">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <span class="sr-only">Facebook</span>
                            <i class="fab fa-facebook fa-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <span class="sr-only">Twitter</span>
                            <i class="fab fa-twitter fa-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <span class="sr-only">LinkedIn</span>
                            <i class="fab fa-linkedin fa-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="mt-8 border-t border-gray-700 pt-8 text-center">
                <p class="text-base text-gray-400">
                    &copy; {{ date('Y') }} EduSphere. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</div>
@endsection
