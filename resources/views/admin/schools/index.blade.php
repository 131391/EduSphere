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
                $cityState = trim(($school->city ?? '') . ', ' . ($school->state ?? ''), ', ');
                
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
    <!-- Success Message -->
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Schools Management</h1>
            <p class="text-gray-600 mt-1">Manage all schools in the system</p>
        </div>
        <a href="{{ route('admin.schools.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Add New School
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Schools</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalSchools ?? $schools->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-school text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Active Schools</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $activeSchools ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Inactive Schools</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $inactiveSchools ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Suspended</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $suspendedSchools ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                </div>
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
