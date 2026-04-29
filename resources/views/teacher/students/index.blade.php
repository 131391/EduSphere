@extends('layouts.teacher')

@section('title', 'My Students')

@section('content')
<div x-data="{ searchOpen: true }">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-indigo-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-user-graduate text-xs"></i>
                    </div>
                    Student Directory
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Managing {{ number_format($stats['total']) }} students across {{ $stats['classes'] }} assigned classes.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="searchOpen = !searchOpen" 
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm">
                    <i class="fas fa-filter mr-2 opacity-50"></i>
                    Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total My Students</p>
                <p class="text-2xl font-black text-gray-800">{{ number_format($stats['total']) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-mars text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Male Students</p>
                <p class="text-2xl font-black text-blue-600">{{ number_format($stats['male']) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-venus text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Female Students</p>
                <p class="text-2xl font-black text-rose-600">{{ number_format($stats['female']) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-indigo-50 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-chalkboard text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Classes Taught</p>
                <p class="text-2xl font-black text-amber-600">{{ $stats['classes'] }}</p>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div x-show="searchOpen" x-collapse
         class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <form action="{{ route('teacher.students.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Direct Search</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, admission number, roll..." 
                           class="w-full h-10 pl-9 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Class Filter</label>
                <select name="class_id" 
                        class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All My Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" 
                        class="flex-1 h-10 flex items-center justify-center gap-2 bg-gray-800 dark:bg-gray-700 hover:bg-black dark:hover:bg-gray-600 text-white font-bold text-xs uppercase tracking-widest rounded-lg transition-all duration-300">
                    <i class="fas fa-filter text-[10px] opacity-50"></i>
                    Apply
                </button>
                @if(request()->hasAny(['search', 'class_id']))
                <a href="{{ route('teacher.students.index') }}" 
                   class="h-10 px-4 flex items-center justify-center bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-lg hover:bg-gray-200 transition-colors">
                    <i class="fas fa-times text-xs"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'student',
                'label' => 'Student Details',
                'sortable' => false,
                'render' => function($row) {
                    $photoUrl = $row->student_photo ? asset('storage/'.$row->student_photo) : null;
                    $initials = strtoupper(substr($row->first_name, 0, 1));
                    
                    $imgHtml = $photoUrl 
                        ? '<img class="w-10 h-10 rounded-xl object-cover shadow-sm ring-2 ring-white" src="'.$photoUrl.'">'
                        : '<div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-black text-xs">'.$initials.'</div>';
                        
                    return '
                        <div class="flex items-center gap-4">
                            '.$imgHtml.'
                            <div>
                                <div class="text-sm font-bold text-gray-800">'.e($row->full_name).'</div>
                                <div class="text-[10px] font-bold text-gray-400 tracking-wider">ROLL: '.($row->roll_no ?? 'N/A').'</div>
                            </div>
                        </div>';
                }
            ],
            [
                'key' => 'admission_no',
                'label' => 'Admission ID',
                'sortable' => true,
                'render' => function($row) {
                    return '<span class="text-[12px] font-mono font-black text-indigo-600 tracking-tighter">'.e($row->admission_no).'</span>';
                }
            ],
            [
                'key' => 'class_section',
                'label' => 'Class / Section',
                'sortable' => false,
                'render' => function($row) {
                    return '
                        <div>
                            <div class="text-sm font-bold text-gray-700">'.e($row->class->name ?? 'N/A').'</div>
                            <div class="text-[11px] font-semibold text-indigo-400">Section: '.e($row->section->name ?? 'N/A').'</div>
                        </div>';
                }
            ],
            [
                'key' => 'contact',
                'label' => 'Contact Info',
                'sortable' => false,
                'render' => function($row) {
                    return '<div class="text-xs font-semibold text-gray-500">'.e($row->mobile_no ?? '—').'</div>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-arrow-right',
                'class' => 'text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-2.5 rounded-xl transition-all flex items-center justify-center group',
                'onclick' => function($row) {
                    return "window.location.href='".route('teacher.students.show', $row->id)."'";
                },
                'title' => 'Access Student Profile',
                'label' => '<span class="text-[10px] font-black uppercase tracking-widest ml-1 hidden group-hover:inline">Access Profile</span>'
            ],
        ];
    @endphp

    <div class="mt-6">
        <x-data-table 
            :columns="$tableColumns" 
            :data="$students" 
            :actions="$tableActions"
            empty-message="No student profiles are currently associated with your classes." 
            empty-icon="fas fa-user-slash"
        >
            My Class Students
        </x-data-table>
    </div>
</div>
@endsection

