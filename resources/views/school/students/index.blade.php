@extends('layouts.school')

@section('title', 'Students')

@section('content')
<div x-data="studentManagement">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Student Management</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total {{ number_format($stats['total']) }} students enrolled in your school</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm">
                    <i class="fas fa-file-export mr-2 opacity-50"></i>
                    Export
                </button>
                <a href="{{ route('school.admission.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-user-plus mr-2"></i>
                    New Admission
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-teal-50 text-teal-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Enrolled</p>
                <p class="text-2xl font-black text-gray-800">{{ number_format($stats['total']) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Students</p>
                <p class="text-2xl font-black text-emerald-600">{{ number_format($stats['active']) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-user-slash text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Inactive/Archive</p>
                <p class="text-2xl font-black text-rose-600">{{ number_format($stats['inactive']) }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 group hover:shadow-md transition-all duration-300">
            <div class="w-12 h-12 bg-violet-50 text-violet-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-user-clock text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Recent Admits</p>
                <p class="text-2xl font-black text-violet-600">{{ number_format($stats['admissions_this_month']) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6">
        <form action="{{ route('school.students.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Class</label>
                <select name="class_id" 
                        class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-teal-500 focus:border-teal-500 transition-all">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Section</label>
                <select name="section_id" 
                        class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-teal-500 focus:border-teal-500 transition-all">
                    <option value="">All Sections</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Status</label>
                <select name="status" 
                        class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-teal-500 focus:border-teal-500 transition-all">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="withdrawn" {{ request('status') == 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                </select>
            </div>
            <div class="lg:col-span-1">
                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Smart Search</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Roll, No..." 
                           class="w-full h-10 pl-9 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-200 focus:ring-teal-500 focus:border-teal-500 transition-all">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                </div>
            </div>
            <div class="flex items-end">
                <button type="submit" 
                        class="w-full h-10 flex items-center justify-center gap-2 bg-gray-800 dark:bg-gray-700 hover:bg-black dark:hover:bg-gray-600 text-white font-bold text-xs uppercase tracking-widest rounded-lg shadow-sm transition-all duration-300">
                    <i class="fas fa-filter text-[10px] opacity-50"></i>
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'student_details',
                'label' => 'Student Profile',
                'sortable' => false,
                'render' => function($row) {
                    $photoUrl = $row->photo ? Storage::url($row->photo) : null;
                    $initials = strtoupper(substr($row->first_name, 0, 1) . substr($row->last_name, 0, 1));
                    
                    $imgHtml = $photoUrl 
                        ? '<img class="w-10 h-10 rounded-xl object-cover shadow-sm ring-2 ring-white" src="'.$photoUrl.'">'
                        : '<div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-600 font-black text-xs">'.$initials.'</div>';
                        
                    return '
                        <div class="flex items-center gap-4">
                            '.$imgHtml.'
                            <div>
                                <div class="text-sm font-bold text-gray-800">'.e($row->full_name).'</div>
                                <div class="text-[11px] font-semibold text-gray-400 italic">'.($row->phone ?? 'No Contact').'</div>
                            </div>
                        </div>';
                }
            ],
            [
                'key' => 'admission_no',
                'label' => 'Admission Info',
                'sortable' => true,
                'render' => function($row) {
                    return '
                        <div>
                            <div class="text-[12px] font-mono font-black text-teal-600 tracking-tighter">'.e($row->admission_no).'</div>
                            <div class="text-[10px] font-bold text-gray-400">'.($row->admission_date ? $row->admission_date->format('M d, Y') : 'N/A').'</div>
                        </div>';
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
                            <div class="text-[11px] font-semibold text-emerald-600">'.e($row->section->name ?? 'N/A').'</div>
                        </div>';
                }
            ],
            [
                'key' => 'status',
                'label' => 'Status',
                'sortable' => true,
                'render' => function($row) {
                    $colors = [
                        'active' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'inactive' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                        'withdrawn' => 'bg-rose-100 text-rose-700 border-rose-200',
                    ];
                    $cls = $colors[$row->status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                    return '<span class="px-3 py-1 text-[10px] font-black uppercase rounded-full border tracking-wide '.$cls.'">'.e(ucfirst($row->status)).'</span>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-eye',
                'class' => 'text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.location.href='".route('school.students.show', $row->id)."'";
                },
                'title' => 'View Profile',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.location.href='".route('school.students.edit', $row->id)."'";
                },
                'title' => 'Edit',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $name = addslashes($row->full_name);
                    return "window.dispatchEvent(new CustomEvent('open-delete-student', { detail: { id: " . $row->id . ", name: '{$name}' } }))";
                },
                'title' => 'Archive Student',
            ],
        ];
    @endphp

    <div class="mt-6">
        <x-data-table 
            :columns="$tableColumns" 
            :data="$students" 
            :actions="$tableActions"
            empty-message="No student records found matching your criteria" 
            empty-icon="fas fa-user-slash"
        >
            Student Directory
        </x-data-table>
    </div>

    <!-- Confirmation Modal -->
    <x-confirm-modal />
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('studentManagement', () => ({
            init() {
                window.addEventListener('open-delete-student', (e) => this.confirmDelete(e.detail));
            },

            async confirmDelete(student) {
                window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                    detail: {
                        title: "Archive Student Record",
                        message: `Are you sure you want to archive the student record for "${student.name}"? This will hide them from active lists but keep their historical data.`,
                        callback: async () => {
                            try {
                                const response = await fetch(`/school/students/${student.id}`, {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json",
                                        "Accept": "application/json",
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                    },
                                    body: JSON.stringify({ _method: "DELETE" })
                                });

                                const result = await response.json();

                                if (response.ok) {
                                    if (window.Toast) {
                                        window.Toast.fire({
                                            icon: "success",
                                            title: result.message || "Student archived successfully"
                                        });
                                    }
                                    setTimeout(() => window.location.reload(), 800);
                                } else {
                                    throw new Error(result.message || "Failed to archive student");
                                }
                            } catch (error) {
                                if (window.Toast) {
                                    window.Toast.fire({
                                        icon: "error",
                                        title: error.message || "An error occurred"
                                    });
                                }
                            }
                        }
                    }
                }));
            }
        }));
    });
</script>
@endpush
@endsection

