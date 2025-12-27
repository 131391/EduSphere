@extends('layouts.school')

@section('title', 'Support')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Support</h1>
            <p class="text-gray-600 mt-1">Get help and support</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Contact Support -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-4">
                <i class="fas fa-headset text-blue-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Contact Support</h3>
            <p class="text-gray-600 mb-4">Need help? Our support team is available 24/7 to assist you.</p>
            <div class="space-y-2">
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-envelope w-6 text-center mr-2"></i>
                    <span>support@edusphere.com</span>
                </div>
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-phone w-6 text-center mr-2"></i>
                    <span>+1 (800) 123-4567</span>
                </div>
            </div>
        </div>

        <!-- Documentation -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-4">
                <i class="fas fa-book text-green-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Documentation</h3>
            <p class="text-gray-600 mb-4">Browse our comprehensive documentation to learn how to use EduSphere.</p>
            <a href="#" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <span>View Documentation</span>
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</div>
@endsection
