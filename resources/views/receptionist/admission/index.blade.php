@extends('layouts.receptionist')

@section('title', 'Admission Confirmation')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Total Registration</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $totalRegistration }}</h3>
                </div>
                <div class="text-blue-500">
                    <i class="fas fa-file-alt text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Admission Done</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $admissionDone }}</h3>
                </div>
                <div class="text-green-500">
                    <i class="fas fa-user-check text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Pending Registration</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $pendingRegistration }}</h3>
                </div>
                <div class="text-yellow-500">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Cancelled Registration</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $cancelledRegistration }}</h3>
                </div>
                <div class="text-red-500">
                    <i class="fas fa-times-circle text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold">Total Enquiry</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $totalEnquiry }}</h3>
                </div>
                <div class="text-purple-500">
                    <i class="fas fa-question-circle text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap items-center justify-end gap-4">
        <a href="{{ route('receptionist.admission.create') }}" class="bg-teal-500 text-white px-4 py-2 rounded hover:bg-teal-600 flex items-center gap-2">
            <i class="fas fa-plus"></i> New Admission
        </a>
        <button class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 flex items-center gap-2">
            <i class="fas fa-sms"></i> Send SMS
        </button>
        <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 flex items-center gap-2">
            <i class="fas fa-envelope"></i> Send Email
        </button>
    </div>

    <!-- Students Table -->
    @php
        $tableColumns = [
            [
                'key' => 'admission_no',
                'label' => 'Admission No.',
                'sortable' => true,
            ],
            [
                'key' => 'full_name',
                'label' => 'Student\'s Name',
                'sortable' => true,
                'render' => function($row) {
                    $photoUrl = $row->photo ? asset('storage/' . $row->photo) : 'https://ui-avatars.com/api/?name='.urlencode($row->full_name);
                    return '<div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <img class="h-8 w-8 rounded-full object-cover" src="'.$photoUrl.'" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">'.$row->full_name.'</div>
                                </div>
                            </div>';
                }
            ],
            [
                'key' => 'class_section',
                'label' => 'Class-Section',
                'sortable' => false,
                'render' => function($row) {
                    return ($row->class->name ?? 'N/A') . ' - ' . ($row->section->name ?? 'A');
                }
            ],
            [
                'key' => 'father_name',
                'label' => 'Father\'s Name',
                'sortable' => true,
            ],
            [
                'key' => 'registration_no',
                'label' => 'Registration No.',
                'sortable' => true,
                'render' => function($row) {
                    return $row->registration_no ?? '-';
                }
            ],
            [
                'key' => 'admission_date',
                'label' => 'Admission Date',
                'sortable' => true,
                'render' => function($row) {
                    return $row->admission_date ? $row->admission_date->format('M. d, Y, h:i a') : '-';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'link',
                'url' => fn($row) => route('receptionist.admission.show', $row->id),
                'icon' => 'fas fa-eye',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'View',
            ],
            [
                'type' => 'link',
                'url' => fn($row) => route('receptionist.admission.edit', $row->id),
                'icon' => 'fas fa-edit',
                'class' => 'text-indigo-600 hover:text-indigo-900',
                'title' => 'Edit',
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('receptionist.admission.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'confirm' => 'Are you sure you want to delete this student?',
            ],
        ];

        $tableFilters = [
            [
                'name' => 'class_id',
                'label' => 'Class',
                'options' => $classes->pluck('name', 'id')->toArray(),
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$students"
        :searchable="true"
        :filterable="true"
        :filters="$tableFilters"
        :actions="$tableActions"
        empty-message="No students found"
        empty-icon="fas fa-user-graduate"
    >
        Admission List
    </x-data-table>
</div>

@endsection
