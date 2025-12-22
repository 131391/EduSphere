@extends('layouts.school')

@section('title', 'Admission Form')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Admission Form</h1>
        <a href="{{ route('school.admission.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Whoops!</strong>
            <span class="block sm:inline">There were some problems with your input.</span>
            <ul class="mt-2 list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('school.admission.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        
        <!-- Admission Info -->
        @include('school.admission.partials._admission_info')

        <!-- Personal Info -->
        @include('school.admission.partials._personal_info')

        <!-- Father Details -->
        @include('school.admission.partials._father_details')

        <!-- Mother Details -->
        @include('school.admission.partials._mother_details')

        <!-- Address Details -->
        @include('school.admission.partials._address_details')

        <!-- Correspondence Address -->
        @include('school.admission.partials._correspondence_address')

        <!-- Photo Details -->
        @include('school.admission.partials._photo_details')

        <!-- Signature Details -->
        @include('school.admission.partials._signature_details')

        <div class="flex justify-end gap-4">
            <button type="reset" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600">Reset</button>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Submit Admission</button>
        </div>
    </form>
</div>
@endsection
