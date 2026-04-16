@extends('layouts.admin')

@section('title', 'Schools Management')

@section('content')
@php
    // Define table columns
    $tableColumns = [
        [
            'key' => 'id',
            'label' => 'ID',
            'sortable' => true,
        ],
        [
            'key' => 'name',
            'label' => 'School Name',
            'sortable' => true,
            'render' => function($school) {
                $logo = $school->logo 
                    ? asset('storage/' . $school->logo) 
                    : null;
                $cityState = trim(($school->city->name ?? '') . ', ' . ($school->state->name ?? ''), ', ');
                
                return view('components.table.school-name', [
                    'school' => $school,
                    'logo' => $logo,
                    'cityState' => $cityState
                ])->render();
            }
        ],
        [
            'key' => 'code',
            'label' => 'Code',
            'sortable' => true,
        ],
        [
            'key' => 'subdomain',
            'label' => 'Subdomain',
            'sortable' => false,
            'render' => function($school) {
                $html = '<span class="text-sm text-gray-900">' . e($school->subdomain) . '</span>';
                if ($school->domain) {
                    $html .= '<div class="text-xs text-gray-500">' . e($school->domain) . '</div>';
                }
                return $html;
            }
        ],
        [
            'key' => 'email',
            'label' => 'Email',
            'sortable' => true,
        ],
        [
            'key' => 'status',
            'label' => 'Status',
            'sortable' => true,
            'render' => function($school) {
                $color = match($school->status) {
                    \App\Enums\SchoolStatus::Active => 'bg-green-100 text-green-800',
                    \App\Enums\SchoolStatus::Inactive => 'bg-gray-100 text-gray-800',
                    \App\Enums\SchoolStatus::Suspended => 'bg-yellow-100 text-yellow-800',
                };
                return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $color . '">' . 
                       $school->status->label() . '</span>';
            }
        ],
        [
            'key' => 'subscription',
            'label' => 'Subscription',
            'sortable' => false,
            'render' => function($school) {
                if ($school->subscription_end_date) {
                    $isActive = $school->isSubscriptionActive();
                    $statusColor = $isActive ? 'text-green-600' : 'text-red-600';
                    $statusText = $isActive ? 'Active' : 'Expired';
                    return '<div class="text-xs">
                                <div>Until: ' . $school->subscription_end_date->format('M d, Y') . '</div>
                                <span class="' . $statusColor . '">' . $statusText . '</span>
                            </div>';
                }
                return '<span class="text-xs text-gray-500">No limit</span>';
            }
        ],
    ];

    // Define filters
    $tableFilters = [
        [
            'name' => 'status',
            'label' => 'Status',
            'options' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'suspended' => 'Suspended',
            ]
        ],
        [
            'name' => 'subscription_status',
            'label' => 'Subscription',
            'options' => [
                'active' => 'Active Subscription',
                'expired' => 'Expired Subscription',
            ]
        ],
    ];

    // Define actions
    $tableActions = [
        [
            'type' => 'link',
            'url' => function($school) {
                return route('admin.schools.show', $school->id);
            },
            'icon' => 'fas fa-eye',
            'class' => 'text-blue-600 hover:text-blue-900',
            'title' => 'View',
        ],
        [
            'type' => 'link',
            'url' => function($school) {
                return route('admin.schools.edit', $school->id);
            },
            'icon' => 'fas fa-edit',
            'class' => 'text-yellow-600 hover:text-yellow-900',
            'title' => 'Edit',
        ],
        [
            'type' => 'form',
            'url' => function($school) {
                return route('admin.schools.destroy', $school->id);
            },
            'method' => 'DELETE',
            'icon' => 'fas fa-trash',
            'class' => 'text-red-600 hover:text-red-900',
            'title' => 'Delete',
            'dispatch' => [
                'event' => 'open-confirm-modal',
                'title' => 'Delete School',
                'message' => 'Are you sure you want to delete this school? This action cannot be undone.'
            ],
        ],
    ];
@endphp

<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-4">
        <!-- Total Schools -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-blue-500 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Total Schools</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalSchools ?? $schools->total() }}</h3>
                </div>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-university text-blue-600 dark:text-blue-400 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Active Schools -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-green-500 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Active Schools</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1 leading-none">
                        <span class="text-green-600">{{ $activeSchools ?? 0 }}</span>
                    </h3>
                </div>
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-check-double text-green-600 dark:text-green-400 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Inactive Schools -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-red-500 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Inactive Schools</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1 leading-none">
                        <span class="text-red-600">{{ $inactiveSchools ?? 0 }}</span>
                    </h3>
                </div>
                <div class="w-10 h-10 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-times-circle text-red-600 dark:text-red-400 text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Suspended Schools -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border-t-4 border-amber-500 transition-all duration-300 hover:shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[13px] font-medium text-gray-600 dark:text-gray-400">Suspended Schools</p>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1 leading-none">
                        <span class="text-amber-600">{{ $suspendedSchools ?? 0 }}</span>
                    </h3>
                </div>
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/20 rounded-full flex items-center justify-center transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-exclamation-triangle text-amber-600 dark:text-amber-400 text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-blue-100/50 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                        <i class="fas fa-university text-xs"></i>
                    </div>
                    Schools Management
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and track all schools and their configurations in the system.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.schools.create') }}" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2 text-xs"></i>
                    Add New School
                </a>
                <a href="{{ route('admin.schools.index', ['export' => 'csv']) }}"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-slate-700 to-slate-900 hover:from-black hover:to-slate-800 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-file-excel mr-2 text-xs"></i>
                    Excel Export
                </a>
            </div>
        </div>
    </div>

    <!-- Data Table Component -->
    <x-data-table 
        :columns="$tableColumns"
        :data="$schools"
        :searchable="true"
        :filterable="true"
        :filters="$tableFilters"
        :actions="$tableActions"
        empty-message="No schools found. Get started by creating your first school."
        empty-icon="fas fa-school"
    >
        All Schools
    </x-data-table>

    <!-- Confirmation Modal -->
    <x-confirm-modal />
</div>
@endsection
