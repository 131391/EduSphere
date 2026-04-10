@extends('layouts.school')

@section('title', 'Admission Press Center')

@section('content')
<div x-data="admissionNewsManager()">
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
            <button @click="openAddModal()" 
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
                    $encoded = base64_encode(json_encode([
                        'id' => $row->id,
                        'title' => $row->title,
                        'content' => $row->content,
                        'publish_date' => $row->publish_date->format('Y-m-d'),
                    ]));
                    return "window.dispatchEvent(new CustomEvent('open-edit-news', { detail: JSON.parse(atob('$encoded')) }))";
                },
                'title' => 'Edit Announcement',
            ],
            [
                'type' => 'button',
                'icon' => 'fas fa-trash',
                'class' => 'text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "window.dispatchEvent(new CustomEvent('open-delete-news', { detail: { id: " . $row->id . ", name: '" . addslashes($row->title) . "' } }))";
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
        <form @submit.prevent="submitForm" method="POST" class="p-0" novalidate>
            @csrf
            <template x-if="editMode">
                <input type="hidden" name="_method" value="PUT">
            </template>

            <div class="px-8 py-8 space-y-6">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Headline <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-indigo-600 text-gray-400">
                            <i class="fas fa-heading text-sm"></i>
                        </div>
                        <input 
                            type="text" 
                            name="title" 
                            x-model="formData.title"
                            @input="if(errors.title) delete errors.title"
                            placeholder="e.g., Admissions Open for 2024-25 Session"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all font-medium text-gray-700"
                            :class="{'border-red-500 ring-red-500/10': errors.title}"
                        >
                    </div>
                </div>

                <!-- Date -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Distribution Date <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none transition-colors duration-200 group-focus-within:text-indigo-600 text-gray-400">
                            <i class="fas fa-calendar-alt text-sm"></i>
                        </div>
                        <input 
                            type="date" 
                            name="publish_date" 
                            x-model="formData.publish_date"
                            @input="if(errors.publish_date) delete errors.publish_date"
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all font-bold text-gray-700"
                            :class="{'border-red-500 ring-red-500/10': errors.publish_date}"
                        >
                    </div>
                </div>

                <!-- Content -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Full Announcement Body <span class="text-red-500">*</span></label>
                    <textarea 
                        name="content" 
                        x-model="formData.content"
                        @input="if(errors.content) delete errors.content"
                        rows="5"
                        placeholder="Detailed message regarding the update..."
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all font-medium text-gray-700 resize-none"
                        :class="{'border-red-500 ring-red-500/10': errors.content}"
                    ></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-8 py-6 bg-gray-50/50 flex items-center justify-end gap-3 rounded-b-lg border-t border-gray-100">
                <button 
                    type="button" 
                    @click="closeModal()"
                    class="px-5 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 hover:bg-gray-100/50 rounded-xl transition-all duration-200"
                >
                    Cancel
                </button>
                <button 
                    type="submit"
                    :disabled="submitting"
                    class="px-8 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-sm font-bold rounded-xl hover:from-indigo-700 hover:to-violet-700 transition-all duration-200 shadow-lg shadow-indigo-200 flex items-center justify-center min-w-[160px] gap-2 active:scale-95 disabled:opacity-50"
                >
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="editMode ? (submitting ? 'Updating...' : 'Save Draft') : (submitting ? 'Broadcasting...' : 'Broadcast Now')"></span>
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
                    setTimeout(() => window.location.reload(), 1000);
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
            if (window.confirm(`Are you sure you want to retract the announcement "${item.name}"?`)) {
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
                    
                    const result = await response.json();
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        alert(result.message || 'Retraction failed');
                    }
                } catch (error) {
                    alert('An error occurred during retraction');
                }
            }
        },

        closeModal() {
            this.$dispatch('close-modal', 'admission-news-modal');
        }
    }));
});
</script>
@endpush
@endsection
