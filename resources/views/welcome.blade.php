@extends('layouts.app')

@section('title', 'Welcome - ' . config('app.name'))

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="max-w-4xl mx-auto text-center px-4">
        <h1 class="text-5xl font-bold text-gray-900 mb-4">Welcome to EduSphere</h1>
        <p class="text-xl text-gray-600 mb-8">Enterprise School ERP Management System</p>
        
        <div class="flex justify-center gap-4">
            <a href="{{ route('login') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                Login
            </a>
        </div>
    </div>
</div>
@endsection

