@extends('layouts.school')

@section('title', 'Student Enquiries')

@section('content')
    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Total Enquiry -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-teal-500 transition-all duration-300 hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Total Enquiry</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-clipboard-list text-teal-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Enquiry -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-orange-500 transition-all duration-300 hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Pending</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['pending'] }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-clock text-orange-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Cancelled Enquiry -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-red-500 transition-all duration-300 hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Cancelled</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['cancelled'] }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-times-circle text-red-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Total Registration -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-blue-500 transition-all duration-300 hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Registrations</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['registration'] }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-user-check text-blue-600 text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Admission Done -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-green-500 transition-all duration-300 hover:shadow-md group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Admitted</p>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['admitted'] }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-graduation-cap text-green-600 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-teal-100/50">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                            <i class="fas fa-clipboard-list text-xs"></i>
                        </div>
                        Enquiry Management
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and track student admission enquiries and follow-ups.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('school.student-enquiries.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                        <i class="fas fa-plus mr-2"></i>
                        Add Enquiry
                    </a>
                    <a href="{{ route('school.student-enquiries.index', ['today' => 1]) }}"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                        <i class="fas fa-calendar-day mr-2 text-xs"></i>
                        Today Follow Up
                    </a>
                    <button
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                        <i class="fas fa-file-excel mr-2 text-xs"></i>
                        Excel Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Enquiries Table -->
        @php
            $tableColumns = [
                [
                    'key' => 'enquiry_no',
                    'label' => 'Enquiry No',
                    'sortable' => true,
                ],
                [
                    'key' => 'student_name',
                    'label' => 'Student\'s Name',
                    'sortable' => true,
                ],
                [
                    'key' => 'father_name',
                    'label' => 'Father\'s Name',
                    'sortable' => true,
                ],
                [
                    'key' => 'class_id',
                    'label' => 'Class',
                    'sortable' => false,
                    'render' => function ($row) {
                        return $row->class?->name ?? 'N/A';
                    }
                ],
                [
                    'key' => 'contact_no',
                    'label' => 'Primary Contact',
                    'sortable' => false,
                ],
                [
                    'key' => 'enquiry_date',
                    'label' => 'Enquiry Date',
                    'sortable' => true,
                    'render' => function ($row) {
                        return $row->enquiry_date->format('d M, Y');
                    }
                ],
                [
                    'key' => 'follow_up_date',
                    'label' => 'Follow Up Date',
                    'sortable' => true,
                    'render' => function ($row) {
                        return $row->follow_up_date ? $row->follow_up_date->format('d M, Y') : 'N/A';
                    }
                ],
                [
                    'key' => 'form_status',
                    'label' => 'Form Status',
                    'sortable' => true,
                    'render' => function ($row) {
                        // Convert enum to string for array key lookup
                        if ($row->form_status instanceof \App\Enums\EnquiryStatus) {
                            // Get the enum name (e.g., "PENDING") and convert to lowercase
                            $statusKey = strtolower($row->form_status->name);
                            $label = $row->form_status->label();
                        } else {
                            $statusKey = strtolower($row->form_status ?? 'pending');
                            $label = ucfirst($statusKey);
                        }

                        $colors = [
                            'pending' => 'bg-orange-100 text-orange-800',
                            'completed' => 'bg-blue-100 text-blue-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            'admitted' => 'bg-green-100 text-green-800',
                        ];
                        $color = $colors[$statusKey] ?? 'bg-gray-100 text-gray-800';

                        return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $color . '"> '
                            . $label . '</span>';
                    }
                ],
            ];

            $tableActions = [
                [
                    'type' => 'link',
                    'url' => function ($row) {
                        return route('school.student-enquiries.edit', $row->id);
                    },
                    'icon' => 'fas fa-edit',
                    'class' => 'text-blue-600 hover:text-blue-900',
                    'title' => 'Edit',
                ],
                [
                    'type' => 'button',
                    'onclick' => function ($row) {
                        return "window.dispatchEvent(new CustomEvent('open-delete-enquiry', { detail: { id: " . $row->id . ", name: '" . addslashes($row->student_name) . "' } }))";
                    },
                    'icon' => 'fas fa-trash',
                    'class' => 'text-red-600 hover:text-red-900',
                    'title' => 'Delete',
                ],
            ];
        @endphp

        <x-data-table :columns="$tableColumns" :data="$enquiries" :searchable="true" :actions="$tableActions"
            empty-message="No enquiries found" empty-icon="fas fa-clipboard-list">
            Enquiry List
        </x-data-table>

        {{-- Confirm Modal Component --}}
        <x-confirm-modal />



        @push('scripts')
            <script>
                window.addEventListener("open-delete-enquiry", (e) => {
                    const enquiry = e.detail;
                    window.dispatchEvent(new CustomEvent("open-confirm-modal", {
                        detail: {
                            title: "Delete Enquiry",
                            message: `Are you sure you want to delete the enquiry for "${enquiry.name}"? This action cannot be undone.`,
                            callback: async () => {
                                    try {
                                        const response = await fetch(`/school/student-enquiries/${enquiry.id}`, {
                                            method: "DELETE",
                                            headers: {
                                                "Accept": "application/json",
                                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                            }
                                        });

                                    const result = await response.json();

                                    if (response.ok) {
                                        if (window.Toast) {
                                            window.Toast.fire({ icon: "success", title: result.message });
                                        }
                                        setTimeout(() => window.location.reload(), 1000);
                                    } else {
                                        throw new Error(result.message || "Deletion failed");
                                    }
                                } catch (error) {
                                    if (window.Toast) {
                                        window.Toast.fire({ icon: "error", title: error.message });
                                    }
                                }
                            }
                        }
                    }));
                });
            </script>
        @endpush
@endsection