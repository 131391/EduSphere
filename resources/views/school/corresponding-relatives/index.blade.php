@extends('layouts.school')

@section('title', 'Corresponding Relatives')

@section('content')
<div class="space-y-6" x-data="relativeManagement()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-teal-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                        <i class="fas fa-users-cog text-xs"></i>
                    </div>
                    Corresponding Relatives
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure and manage relationship types for student profiles</p>
            </div>
            <button @click="openAddModal()" 
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
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
                    'icon' => 'fas fa-edit',
                    'class' => 'text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-2 rounded-lg transition-colors',
                    'onclick' => function ($row) {
                        $data = json_encode([
                            'id' => $row->id,
                            'name' => $row->name,
                        ]);
                        return "window.dispatchEvent(new CustomEvent('open-edit-relative', { detail: $data }))";
                    },
                    'title' => 'Edit',
                ],
                [
                    'type' => 'button',
                    'icon' => 'fas fa-trash',
                    'class' => 'text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors',
                    'onclick' => function ($row) {
                        $name = addslashes($row->name);
                        return "window.dispatchEvent(new CustomEvent('open-delete-relative', { detail: { id: {$row->id}, name: '{$name}' } }))";
                    },
                    'title' => 'Delete',
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
        <form id="relative-form" @submit.prevent="submitForm()" method="POST">
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>
            
            <div class="space-y-6">
                <div>
                    <label class="modal-label-premium">Relation Name <span class="text-red-600 font-bold">*</span></label>
                    <div class="relative group">
                        <input 
                            type="text" 
                            name="name" 
                            x-model="formData.name"
                            @input="clearError('name')"
                            placeholder="e.g. Guardian, Spouse, Sibling"
                            class="modal-input-premium"
                            :class="{'border-red-500 ring-red-500/10': errors.name}"
                        >
                    </div>
                    <template x-if="errors.name">
                        <p class="modal-error-message" x-text="errors.name[0]"></p>
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

            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">
                    Discard
                </button>
                <button type="submit" :disabled="loading" form="relative-form" class="btn-premium-primary min-w-[160px] bg-teal-600 hover:bg-teal-700 shadow-teal-200">
                    <template x-if="loading">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="editMode ? 'Update Changes' : 'Save Record'"></span>
                </button>
            </x-slot>
        </form>
    </x-modal>
</div>

{{-- Confirmation Modal --}}
<x-confirm-modal />

@push('scripts')
    <script>
        document.addEventListener("alpine:init", () => {
            Alpine.data("relativeManagement", () => ({
                editMode: false,
                loading: false,
                relativeId: null,
                stats: {
                    total: {{ $relatives->total() }}
                },
                formData: {
                    name: ""
                },
                errors: {},

                init() {
                    window.addEventListener("open-edit-relative", (e) => this.openEditModal(e.detail));
                    window.addEventListener("open-delete-relative", (e) => this.confirmDelete(e.detail));
                },

                openAddModal() {
                    this.editMode = false;
                    this.relativeId = null;
                    this.formData = { name: "" };
                    this.errors = {};
                    this.$dispatch("open-modal", "relative-modal");
                },
                
                openEditModal(relative) {
                    this.editMode = true;
                    this.relativeId = relative.id;
                    this.formData = { name: relative.name };
                    this.errors = {};
                    this.$dispatch("open-modal", "relative-modal");
                },

                closeModal() {
                    this.$dispatch("close-modal", "relative-modal");
                },

                clearError(field) {
                    if (this.errors[field]) {
                        delete this.errors[field];
                    }
                },

                async submitForm() {
                    if (this.loading) return;
                    this.loading = true;
                    this.errors = {};
                    
                    const url = this.editMode 
                        ? `/school/corresponding-relatives/${this.relativeId}` 
                        : "{{ route('school.corresponding-relatives.store') }}";
                    
                    try {
                        const response = await fetch(url, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                ...this.formData,
                                _method: this.editMode ? "PUT" : "POST"
                            })
                        });

                        const result = await response.json();

                        if (response.ok) {
                            if (window.Toast) {
                                window.Toast.fire({
                                    icon: "success",
                                    title: result.message
                                });
                            }
                            this.closeModal();
                            setTimeout(() => window.location.reload(), 800);
                        } else if (response.status === 422) {
                            this.errors = result.errors || {};
                        } else {
                            throw new Error(result.message || "Operation failed");
                        }
                    } catch (error) {
                        if (window.Toast) {
                            window.Toast.fire({
                                icon: "error",
                                title: error.message
                            });
                        }
                    } finally {
                        this.loading = false;
                    }
                },

                async confirmDelete(relative) {
                    window.dispatchEvent(new CustomEvent("open-confirm-modal", {
                        detail: {
                            title: "Delete Relationship Type",
                            message: `Are you sure you want to delete the relationship type "${relative.name}"? This action cannot be undone and may affect associated student profiles.`,
                            callback: async () => {
                                try {
                                    const response = await fetch(`/school/corresponding-relatives/${relative.id}`, {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/json",
                                            "Accept": "application/json",
                                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                        },
                                        body: JSON.stringify({ _method: "DELETE" })
                                    });
                                    
                                    if (response.ok) {
                                        window.location.reload();
                                    } else {
                                        const result = await response.json();
                                        if (window.Toast) {
                                            window.Toast.fire({
                                                icon: "error",
                                                title: result.message || "Delete failed"
                                            });
                                        }
                                    }
                                } catch (error) {
                                    console.error("Delete Error:", error);
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
