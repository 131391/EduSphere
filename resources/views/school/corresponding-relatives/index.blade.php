@extends('layouts.school')

@section('title', 'Corresponding Relatives')

@section('content')
<div class="p-6 transition-all duration-300 ease-in-out" x-data="relativeManagement()">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight dark:text-white flex items-center gap-3">
                <span class="p-3 bg-teal-100 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 rounded-2xl shadow-sm">
                    <i class="fas fa-users-cog"></i>
                </span>
                Corresponding Relatives
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-2 flex items-center gap-2">
                <span class="w-2 h-2 bg-teal-500 rounded-full animate-pulse"></span>
                Configure and manage relationship types for student profiles
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openAddModal()" 
                class="group flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-teal-200 dark:shadow-none transition-all hover:scale-105 active:scale-95">
                <i class="fas fa-plus group-hover:rotate-90 transition-transform duration-300"></i>
                Add New Type
            </button>
        </div>
    </div>

    {{-- Stats Cards (Optional but adds premium feel) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 rounded-xl">
                    <i class="fas fa-list-ul fa-lg"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Types</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.total">{{ $relatives->total() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-xl">
                    <i class="fas fa-clock fa-lg"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Recently Added</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $relatives->where('created_at', '>=', now()->subDays(7))->count() }}
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-xl">
                    <i class="fas fa-shield-alt fa-lg"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">System Records</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">Active</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Section --}}
    <div class="relative group" id="table-container" @refresh-table.window="refreshTable()">
        @fragment('table')
        @php
            $tableColumns = [
                [
                    'key' => 'id',
                    'label' => 'SR NO',
                    'sortable' => true,
                    'class' => 'w-20'
                ],
                [
                    'key' => 'name',
                    'label' => 'RELATION TYPE',
                    'sortable' => true,
                    'render' => function($row) {
                        return '<div class="flex items-center gap-3">
                                    <div class="w-8 h-8 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg text-gray-500">
                                        <i class="fas fa-hashtag text-xs"></i>
                                    </div>
                                    <span class="font-bold text-gray-800 dark:text-gray-200">' . e($row->name) . '</span>
                                </div>';
                    }
                ],
                [
                    'key' => 'created_at',
                    'label' => 'CREATED ON',
                    'sortable' => true,
                    'render' => function($row) {
                        return '<div class="flex flex-col">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">' . $row->created_at->format('M d, Y') . '</span>
                                    <span class="text-xs text-gray-400">' . $row->created_at->format('h:i A') . '</span>
                                </div>';
                    }
                ],
            ];

            $tableActions = [
                [
                    'type' => 'button',
                    'icon' => 'fas fa-pen-nib',
                    'class' => 'p-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-lg transition-all duration-300 shadow-sm hover:shadow-indigo-200',
                    'title' => 'Edit Record',
                    'onclick' => function($row) {
                        return "openEditModal(" . json_encode($row) . ")";
                    }
                ],
                [
                    'type' => 'button',
                    'icon' => 'fas fa-trash-alt',
                    'class' => 'p-2 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg transition-all duration-300 shadow-sm hover:shadow-rose-200',
                    'title' => 'Delete Record',
                    'onclick' => function($row) {
                        return "confirmDelete(" . $row->id . ")";
                    }
                ],
            ];
        @endphp

        <x-data-table 
            :columns="$tableColumns"
            :data="$relatives"
            :actions="$tableActions"
            empty-message="No relationship types have been configured yet"
            empty-icon="fas fa-folder-open"
        >
            <div class="flex items-center gap-2">
                <i class="fas fa-stream text-teal-500"></i>
                Configured Types
            </div>
        </x-data-table>
        @endfragment
    </div>

    {{-- Add/Edit Modal (Premium Design) --}}
    <x-modal name="relative-modal" alpineTitle="editMode ? 'Edit Relationship Type' : 'Configure New Relationship'" maxWidth="md">
        <div class="relative overflow-hidden">
            {{-- Background Accent --}}
            <div class="absolute top-0 right-0 -m-4 w-24 h-24 bg-teal-500/5 rounded-full blur-2xl"></div>
            
            <form @submit.prevent="submitForm()" class="p-8 relative z-10" id="relativeForm">
                @csrf
                <template x-if="editMode">
                    @method('PUT')
                </template>
                
                <div class="space-y-6">
                    <div>
                        <label class="flex items-center gap-2 text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                            <i class="fas fa-tag text-teal-500 text-xs"></i>
                            Relation Name <span class="text-red-500 animate-pulse">*</span>
                        </label>
                        <div class="relative group">
                            <input 
                                type="text" 
                                name="name" 
                                x-model="formData.name"
                                placeholder="e.g. Guardian, Spouse, Sibling"
                                :class="errors.name ? 'border-rose-500 ring-rose-200' : 'border-gray-200 dark:border-gray-600 ring-teal-200'"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700/50 border rounded-xl focus:outline-none focus:ring-4 transition-all duration-300 placeholder-gray-400 dark:placeholder-gray-500"
                                required
                            >
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-300 group-focus-within:text-teal-500 transition-colors">
                                <i class="fas fa-keyboard"></i>
                            </div>
                        </div>
                        <template x-if="errors.name">
                            <p class="text-rose-500 text-xs mt-2 flex items-center gap-1 font-medium italic">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span x-text="errors.name[0]"></span>
                            </p>
                        </template>
                    </div>

                    {{-- Tips Section --}}
                    <div class="bg-teal-50 dark:bg-teal-900/20 p-4 rounded-xl border border-teal-100 dark:border-teal-800/30">
                        <p class="text-[10px] font-bold text-teal-600 dark:text-teal-400 uppercase tracking-widest flex items-center gap-2 mb-1">
                            <i class="fas fa-lightbulb"></i> Pro Tip
                        </p>
                        <p class="text-xs text-teal-800/70 dark:text-teal-400/70 leading-relaxed font-medium">
                            Use clear, standardized names for relationship types to ensure consistency across student files.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-10">
                    <button 
                        type="button" 
                        @click="closeModal()"
                        class="px-6 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-all"
                    >
                        Discard
                    </button>
                    <button 
                        type="submit"
                        :disabled="loading"
                        class="group relative px-8 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold shadow-lg shadow-teal-100 dark:shadow-none transition-all active:scale-95 disabled:opacity-50 flex items-center gap-2"
                    >
                        <span x-show="!loading" class="flex items-center gap-2">
                            <span x-text="editMode ? 'Update Changes' : 'Save Record'"></span>
                            <i class="fas fa-chevron-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        </span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <i class="fas fa-circle-notch animate-spin"></i>
                            Processing...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</div>

{{-- Confirmation Modal --}}
<x-confirm-modal />

@push('scripts')
<script>
function relativeManagement() {
    return {
        editMode: false,
        loading: false,
        relativeId: null,
        stats: {
            total: {{ $relatives->total() }}
        },
        formData: {
            name: ''
        },
        errors: {},

        init() {
            // Initializations if needed
        },

        openAddModal() {
            this.editMode = false;
            this.relativeId = null;
            this.formData = { name: '' };
            this.errors = {};
            this.$dispatch('open-modal', 'relative-modal');
        },
        
        openEditModal(relative) {
            this.editMode = true;
            this.relativeId = relative.id;
            this.formData = { name: relative.name };
            this.errors = {};
            this.$dispatch('open-modal', 'relative-modal');
        },

        closeModal() {
            this.$dispatch('close-modal', 'relative-modal');
        },

        async submitForm() {
            this.loading = true;
            this.errors = {};
            
            const url = this.editMode 
                ? `/school/corresponding-relatives/${this.relativeId}` 
                : '{{ route('school.corresponding-relatives.store') }}';
            
            const method = this.editMode ? 'PUT' : 'POST';
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ...this.formData,
                        _method: method
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: 'success',
                            title: result.message
                        });
                    }
                    this.closeModal();
                    this.refreshTable();
                } else if (response.status === 422) {
                    this.errors = result.errors;
                } else {
                    throw new Error(result.message || 'Something went wrong');
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({
                        icon: 'error',
                        title: error.message
                    });
                }
            } finally {
                this.loading = false;
            }
        },

        async deleteRecord(id) {
            try {
                const response = await fetch(`/school/corresponding-relatives/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                });

                const result = await response.json();

                if (response.ok) {
                    if (window.Toast) {
                        window.Toast.fire({
                            icon: 'success',
                            title: result.message
                        });
                    }
                    this.refreshTable();
                } else {
                    throw new Error(result.message || 'Failed to delete record');
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({
                        icon: 'error',
                        title: error.message
                    });
                }
            }
        },

        refreshTable() {
            const currentUrl = new URL(window.location.href);
            fetch(currentUrl.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTable = doc.querySelector('#table-container');
                if (newTable) {
                    document.querySelector('#table-container').innerHTML = newTable.innerHTML;
                    // Trigger Alpine init for the new content if needed
                }
                // Update stats
                this.updateStats();
            });
        },

        updateStats() {
            // Optional: Fetch updated stats
            // For now, let's just use the table refresh logic
        }
    };
}

// Global scope helpers
function openEditModal(relative) {
    const el = document.querySelector('[x-data*="relativeManagement"]');
    if (el) {
        Alpine.$data(el).openEditModal(relative);
    }
}

function confirmDelete(id) {
    window.dispatchEvent(new CustomEvent('open-confirm-modal', {
        detail: {
            title: 'Delete Record',
            message: 'Are you sure you want to delete this relationship type? This action cannot be undone.',
            callback: () => {
                const el = document.querySelector('[x-data*="relativeManagement"]');
                if (el) {
                    Alpine.$data(el).deleteRecord(id);
                }
            }
        }
    }));
}
</script>
@endpush
@endsection
