@extends('layouts.school')

@section('title', 'Admission Press Center')

@section('content')
<div x-data="admissionNewsManager">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-indigo-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                        <i class="fas fa-bullhorn text-xs"></i>
                    </div>
                    Admission Press Center
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Broadcast official announcements and updates for potential applicants</p>
            </div>
            <button @click="openAddModal" 
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2"></i>
                Compose News
            </button>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'publish_date',
                'label' => 'RELEASE DATE',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex flex-col items-center justify-center text-indigo-600 border border-indigo-100 shadow-sm leading-none p-1">
                            <span class="text-[9px] font-black uppercase tracking-tighter">' . $row->publish_date->format('M') . '</span>
                            <span class="text-sm font-black">' . $row->publish_date->format('d') . '</span>
                        </div>
                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">' . $row->publish_date->format('Y') . '</div>
                    </div>';
                }
            ],
            [
                'key' => 'title',
                'label' => 'ANNOUNCEMENT SUMMARY',
                'sortable' => true,
                'render' => function($row) {
                    return '
                    <div class="flex flex-col max-w-md">
                        <span class="text-sm font-bold text-gray-800 leading-tight">' . e($row->title) . '</span>
                        <span class="text-[10px] text-gray-400 font-medium truncate mt-0.5 mt-1">' . e(Str::limit($row->content, 60)) . '</span>
                    </div>';
                }
            ],
            [
                'key' => 'status',
                'label' => 'DISTRIBUTION',
                'render' => function($row) {
                    $status = $row->is_active ? 'Public' : 'Hidden';
                    $color = $row->is_active ? 'emerald' : 'gray';
                    return '<span class="px-2.5 py-1 bg-'.$color.'-100 text-'.$color.'-700 text-[10px] font-black rounded-lg uppercase tracking-tight border border-'.$color.'-200">'.$status.'</span>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-indigo-600 hover:text-indigo-800 bg-indigo-50 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $encoded = json_encode([
                        'id' => $row->id,
                        'title' => $row->title,
                        'content' => $row->content,
                        'publish_date' => $row->publish_date->format('Y-m-d'),
                    ]);
                    return "window.dispatchEvent(new CustomEvent('open-edit-news', { detail: $encoded }))";
                },
                'title' => 'Edit Announcement',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    $name = addslashes($row->title);
                    return "window.dispatchEvent(new CustomEvent('open-delete-news', { detail: { id: " . $row->id . ", name: '{$name}' } }))";
                },
                'title' => 'Retract News',
            ],
        ];
    @endphp

    <div x-on:open-edit-news.window="openEditModal($event.detail)" 
         x-on:open-delete-news.window="confirmDelete($event.detail)">
        <x-data-table 
            :columns="$tableColumns"
            :data="$news"
            :actions="$tableActions"
            empty-message="No official announcements found"
            empty-icon="fas fa-newspaper"
        >
            Broadcasting History
        </x-data-table>
    </div>

    <!-- News Modal -->
    <x-modal name="admission-news-modal" alpineTitle="editMode ? 'Edit Announcement' : 'Compose Broadcasting News'" maxWidth="2xl">
        <form @submit.prevent="submitForm()" method="POST" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <!-- Title -->
            <div class="space-y-2 mb-6">
                <label class="modal-label-premium">Headline <span class="text-red-600 font-bold">*</span></label>
                <div class="relative group">
                    <input 
                        type="text" 
                        name="title" 
                        x-model="formData.title"
                        @input="clearError('title')"
                        placeholder="e.g., Admissions Open for 2024-25 Session"
                        class="modal-input-premium pl-4"
                        :class="{'border-red-500 ring-red-500/10': errors.title}"
                    >
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-indigo-500">
                        <i class="fas fa-heading text-sm"></i>
                    </div>
                </div>
                <template x-if="errors.title">
                    <p class="modal-error-message" x-text="errors.title[0]"></p>
                </template>
            </div>

            <!-- Date -->
            <div class="space-y-2 mb-6">
                <label class="modal-label-premium">Distribution Date <span class="text-red-600 font-bold">*</span></label>
                <div class="relative group">
                    <input 
                        type="date" 
                        name="publish_date" 
                        x-model="formData.publish_date"
                        @input="clearError('publish_date')"
                        class="modal-input-premium pl-4 font-bold"
                        :class="{'border-red-500 ring-red-500/10': errors.publish_date}"
                    >
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none transition-colors group-focus-within:text-indigo-500">
                        <i class="fas fa-calendar-alt text-sm"></i>
                    </div>
                </div>
                <template x-if="errors.publish_date">
                    <p class="modal-error-message" x-text="errors.publish_date[0]"></p>
                </template>
            </div>

            <!-- Content -->
            <div class="space-y-2 mb-8">
                <label class="modal-label-premium">Full Announcement Body <span class="text-red-600 font-bold">*</span></label>
                <textarea 
                    name="content" 
                    x-model="formData.content"
                    @input="clearError('content')"
                    rows="5"
                    placeholder="Detailed message regarding the update..."
                    class="modal-input-premium px-4 py-3 resize-none h-40"
                    :class="{'border-red-500 ring-red-500/10': errors.content}"
                ></textarea>
                <template x-if="errors.content">
                    <p class="modal-error-message" x-text="errors.content[0]"></p>
                </template>
            </div>

            <!-- Modal Footer -->
            <x-slot name="footer">
                <button type="button" @click="closeModal()" class="btn-premium-cancel px-10">
                    Cancel
                </button>
                <button type="button" @click="submitForm()" :disabled="submitting" class="btn-premium-primary min-w-[160px]">
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                    </template>
                    <span x-text="editMode ? 'Update Changes' : 'Broadcast Now'"></span>
                </button>
            </x-slot>
        </form>
    </x-modal>
</div>

<!-- Confirmation Modal -->
<x-confirm-modal />

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('admissionNewsManager', () => ({
        editMode: false,
        newsId: null,
        submitting: false,
        errors: {},
        formData: {
            title: '',
            content: '',
            publish_date: '{{ date('Y-m-d') }}'
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};
            
            const url = this.editMode 
                ? `/school/admission-news/${this.newsId}` 
                : '{{ route('school.admission-news.store') }}';
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ...this.formData,
                        _method: this.editMode ? 'PUT' : 'POST'
                    })
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: result.message });
                    }
                    setTimeout(() => window.location.reload(), 800);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Transmission failed');
                }
            } catch (error) {
                if (window.Toast) {
                    window.Toast.fire({ icon: 'error', title: error.message });
                }
            } finally {
                this.submitting = false;
            }
        },

        clearError(field) {
            if (this.errors[field]) {
                delete this.errors[field];
            }
        },

        openAddModal() {
            this.editMode = false;
            this.newsId = null;
            this.errors = {};
            this.formData = { title: '', content: '', publish_date: '{{ date('Y-m-d') }}' };
            this.$dispatch('open-modal', 'admission-news-modal');
        },
        
        openEditModal(item) {
            this.editMode = true;
            this.newsId = item.id;
            this.errors = {};
            this.formData = {
                title: item.title,
                content: item.content,
                publish_date: item.publish_date
            };
            this.$dispatch('open-modal', 'admission-news-modal');
        },

        async confirmDelete(item) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                detail: {
                    title: 'Retract Announcement',
                    message: `Are you sure you want to retract the announcement "${item.name}"? This will remove it from public view immediately.`,
                    callback: async () => {
                        try {
                            const response = await fetch(`/school/admission-news/${item.id}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ _method: 'DELETE' })
                            });
                            
                            if (response.ok) {
                                window.location.reload();
                            } else {
                                const result = await response.json();
                                if (window.Toast) {
                                    window.Toast.fire({
                                        icon: 'error',
                                        title: result.message || 'Retraction failed'
                                    });
                                }
                            }
                        } catch (error) {
                            console.error('Retraction Error:', error);
                        }
                    }
                }
            }));
        },

        closeModal() {
            this.$dispatch('close-modal', 'admission-news-modal');
        }
    }));
});
</script>
@endpush
@endsection
