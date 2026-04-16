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

<div class="space-y-8">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Schools -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm p-6 border-t-4 border-blue-500 transition-all duration-300 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Schools</p>
                    <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white mt-2">{{ $totalSchools ?? $schools->total() }}</h3>
                </div>
                <div class="w-14 h-14 bg-blue-50 dark:bg-blue-900/20 rounded-2xl flex items-center justify-center transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-university text-blue-600 dark:text-blue-400 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Active Schools -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm p-6 border-t-4 border-green-500 transition-all duration-300 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active Schools</p>
                    <h3 class="text-3xl font-extrabold text-green-600 mt-2">{{ $activeSchools ?? 0 }}</h3>
                </div>
                <div class="w-14 h-14 bg-green-50 dark:bg-green-900/20 rounded-2xl flex items-center justify-center transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-check-double text-green-600 dark:text-green-400 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Inactive Schools -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm p-6 border-t-4 border-red-500 transition-all duration-300 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Inactive Schools</p>
                    <h3 class="text-3xl font-extrabold text-red-600 mt-2">{{ $inactiveSchools ?? 0 }}</h3>
                </div>
                <div class="w-14 h-14 bg-red-50 dark:bg-red-900/20 rounded-2xl flex items-center justify-center transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-times-circle text-red-600 dark:text-red-400 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Suspended Schools -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm p-6 border-t-4 border-amber-500 transition-all duration-300 hover:shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Suspended</p>
                    <h3 class="text-3xl font-extrabold text-amber-600 mt-2">{{ $suspendedSchools ?? 0 }}</h3>
                </div>
                <div class="w-14 h-14 bg-amber-50 dark:bg-amber-900/20 rounded-2xl flex items-center justify-center transition-transform duration-300 hover:scale-110">
                    <i class="fas fa-exclamation-triangle text-amber-600 dark:text-amber-400 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Header Card -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm p-6 border border-blue-100/50 dark:border-gray-700">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white shadow-lg shadow-blue-200 dark:shadow-none">
                    <i class="fas fa-university"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Schools Management</h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">Manage all educational institutions and their configurations</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.schools.create') }}" 
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-bold rounded-2xl transition-all duration-200 shadow-lg shadow-blue-100 hover:shadow-blue-200 hover:-translate-y-0.5 active:translate-y-0">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Add New School
                </a>
                <button @click="window.location.href='{{ route('admin.schools.index', ['export' => 'csv']) }}'"
                    class="inline-flex items-center px-6 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-bold rounded-2xl transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-600 shadow-sm hover:shadow-md">
                    <i class="fas fa-file-export mr-2 text-blue-500"></i>
                    Export Results
                </button>
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
