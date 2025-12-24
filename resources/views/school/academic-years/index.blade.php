@extends('layouts.school')

@section('title', 'Academic Years')

@section('content')
<div class="space-y-6" x-data="academicYearManagement">
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Academic Years</h1>
            <p class="text-gray-600 mt-1">Manage academic years for your school</p>
        </div>
        <button 
            @click="openAddModal()" 
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
        >
            <i class="fas fa-plus mr-2"></i>
            ADD
        </button>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'id',
                'label' => 'SR NO',
                'sortable' => true,
                'render' => function($row) use ($academicYears) {
                    static $index = 0;
                    return $academicYears->firstItem() + $index++;
                }
            ],
            [
                'key' => 'name',
                'label' => 'ACADEMIC YEAR',
                'sortable' => true,
                'render' => function($row) {
                    $html = '<span class="font-medium text-gray-900">' . e($row->name) . '</span>';
                    if ($row->is_current) {
                        $html .= '<span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Current</span>';
                    }
                    return $html;
                }
            ],
            [
                'key' => 'start_date',
                'label' => 'DATE',
                'sortable' => true,
                'render' => function($row) {
                    return $row->start_date->format('M d, Y') . ' - ' . $row->end_date->format('M d, Y');
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-blue-600 hover:text-blue-900',
                'title' => 'Edit',
                'onclick' => function($row) {
                    return "openEditModal(JSON.parse(atob(this.getAttribute('data-year'))))";
                },
                'data-year' => function($row) {
                    return base64_encode(json_encode([
                        'id' => $row->id,
                        'name' => $row->name,
                        'start_date' => $row->start_date->format('Y-m-d'),
                        'end_date' => $row->end_date->format('Y-m-d'),
                        'is_current' => $row->is_current,
                    ]));
                }
            ],
            [
                'type' => 'form',
                'url' => fn($row) => route('school.academic-years.destroy', $row->id),
                'method' => 'DELETE',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-600 hover:text-red-900',
                'title' => 'Delete',
                'dispatch' => [
                    'event' => 'open-confirm-modal',
                    'title' => 'Delete Academic Year',
                    'message' => 'Are you sure you want to delete this academic year?'
                ]
            ],
        ];
    @endphp

    <x-data-table 
        :columns="$tableColumns"
        :data="$academicYears"
        :actions="$tableActions"
        empty-message="No academic years found"
        empty-icon="fas fa-calendar-alt"
    >
        Academic Years List
    </x-data-table>

    <!-- Add/Edit Academic Year Modal -->
    <x-modal name="academic-year-modal" alpineTitle="editMode ? 'Edit Academic Year' : 'Add Academic Year'" maxWidth="md">
        <form :action="editMode ? `/school/academic-years/${yearId}` : '{{ route('school.academic-years.store') }}'" 
              method="POST" class="p-6" novalidate>
            @csrf
            <template x-if="editMode">
                @method('PUT')
            </template>
            <input type="hidden" name="year_id" x-model="yearId">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Academic Year Name <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="formData.name"
                        placeholder="e.g., 2025-2026"
                        class="w-full px-4 py-2 border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    >
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Start Date <span class="text-red-500">*</span></label>
                    <input 
                        type="date" 
                        name="start_date" 
                        x-model="formData.start_date"
                        class="w-full px-4 py-2 border @error('start_date') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    >
                    @error('start_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">End Date <span class="text-red-500">*</span></label>
                    <input 
                        type="date" 
                        name="end_date" 
                        x-model="formData.end_date"
                        class="w-full px-4 py-2 border @error('end_date') border-red-500 @else border-gray-300 @enderror rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                    >
                    @error('end_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="is_current" 
                        id="is_current"
                        x-model="formData.is_current"
                        :checked="formData.is_current"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <label for="is_current" class="ml-2 text-sm text-gray-700">Set as current academic year</label>
                </div>
            </div>

            <div class="flex items-center justify-center gap-4 mt-8">
                <button 
                    type="button" 
                    @click="closeModal()"
                    class="px-8 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-semibold"
                >
                    Close
                </button>
                <button 
                    type="submit"
                    class="px-8 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold shadow-md"
                >
                    Submit
                </button>
            </div>
        </form>
    </x-modal>
</div>

<!-- Confirmation Modal -->
<x-confirm-modal />

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('academicYearManagement', () => ({
        editMode: false,
        yearId: null,
        formData: {
            name: '',
            start_date: '',
            end_date: '',
            is_current: false
        },

        init() {
            @if($errors->any())
                this.editMode = {{ old('_method') === 'PUT' ? 'true' : 'false' }};
                this.yearId = '{{ old('year_id') }}';
                this.formData = {
                    name: '{{ old('name') }}',
                    start_date: '{{ old('start_date') }}',
                    end_date: '{{ old('end_date') }}',
                    is_current: {{ old('is_current') ? 'true' : 'false' }}
                };
                this.$nextTick(() => {
                    this.$dispatch('open-modal', 'academic-year-modal');
                });
            @endif
        },

        openAddModal() {
            this.editMode = false;
            this.yearId = null;
            this.formData = { name: '', start_date: '', end_date: '', is_current: false };
            this.$dispatch('open-modal', 'academic-year-modal');
        },
        
        openEditModal(year) {
            this.editMode = true;
            this.yearId = year.id;
            this.formData = {
                name: year.name,
                start_date: year.start_date,
                end_date: year.end_date,
                is_current: !!year.is_current
            };
            this.$dispatch('open-modal', 'academic-year-modal');
        },

        closeModal() {
            this.$dispatch('close-modal', 'academic-year-modal');
        }
    }));
});

// Global function for table actions
function openEditModal(year) {
    const component = Alpine.$data(document.querySelector('[x-data*="academicYearManagement"]'));
    if (component) {
        component.openEditModal(year);
    }
}
</script>
@endpush
@endsection
