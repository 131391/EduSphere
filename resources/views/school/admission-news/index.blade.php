@extends('layouts.school')

@section('title', 'Admission Press Center')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.admission-news.fetch') }}',
        defaultSort: 'publish_date',
        defaultDirection: 'desc',
        defaultPerPage: 25,
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
    }), admissionNewsManagement())" class="space-y-6" @close-modal.window="if ($event.detail === 'admission-news-modal') { resetForm(); }">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Announcements" :value="$stats['total']" icon="fas fa-bullhorn" color="indigo" alpine-text="stats.total" />
            <x-stat-card label="Active News" :value="$stats['active']" icon="fas fa-check-circle" color="emerald" alpine-text="stats.active" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Admission Press Center" description="Broadcast official announcements and updates for potential applicants" icon="fas fa-bullhorn">
            <button @click="openAddModal()"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Compose News
            </button>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header with Search -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Broadcasting History</h2>
                        <x-table.search placeholder="Search announcements..." />
                    </div>
                    <div class="flex items-center gap-3">
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="publish_date" label="Release Date" sort-var="sort" direction-var="direction" />
                            <x-table.sort-header column="title" label="Announcement Summary" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Distribution</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" data-ssr x-show="!hydrated">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex flex-col items-center justify-center text-indigo-600 border border-indigo-100 shadow-sm leading-none p-1">
                                            <span class="text-[9px] font-black uppercase tracking-tighter">{{ \Carbon\Carbon::parse($row['publish_date'])->format('M') }}</span>
                                            <span class="text-sm font-black">{{ \Carbon\Carbon::parse($row['publish_date'])->format('d') }}</span>
                                        </div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ \Carbon\Carbon::parse($row['publish_date'])->format('Y') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col max-w-md">
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100 leading-tight">{{ $row['title'] }}</span>
                                        <span class="text-[10px] text-gray-400 font-medium truncate mt-1">{{ Str::limit($row['content'], 60) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($row['is_active'])
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-100 text-emerald-700 uppercase tracking-tight border border-emerald-200">Public</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-gray-100 text-gray-600 uppercase tracking-tight border border-gray-200">Hidden</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(@js($row))" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></button>
                                        <button @click="confirmDelete(@js($row))" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-show="hydrated" :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex flex-col items-center justify-center text-indigo-600 border border-indigo-100 shadow-sm leading-none p-1">
                                            <span class="text-[9px] font-black uppercase tracking-tighter" x-text="moment(row.publish_date).format('MMM')"></span>
                                            <span class="text-sm font-black" x-text="moment(row.publish_date).format('DD')"></span>
                                        </div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest" x-text="moment(row.publish_date).format('YYYY')"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col max-w-md">
                                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100 leading-tight" x-text="row.title"></span>
                                        <span class="text-[10px] text-gray-400 font-medium truncate mt-1" x-text="row.content.substring(0, 60) + (row.content.length > 60 ? '...' : '')"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <template x-if="row.is_active">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-emerald-100 text-emerald-700 uppercase tracking-tight border border-emerald-200">Public</span>
                                    </template>
                                    <template x-if="!row.is_active">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-gray-100 text-gray-600 uppercase tracking-tight border border-gray-200">Hidden</span>
                                    </template>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openEditModal(row)" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100 transition-colors" title="Edit"><i class="fas fa-edit text-xs"></i></button>
                                        <button @click="confirmDelete(row)" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition-colors" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="4" icon="fas fa-bullhorn" message="No official announcements found." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination :initial="$initialData['pagination']" />
        </div>

        <!-- Add/Edit Modal -->
        <x-modal name="admission-news-modal" alpineTitle="editMode ? 'Edit Announcement' : 'Compose Broadcasting News'" maxWidth="2xl">
            <form @submit.prevent="submitForm()" method="POST" novalidate class="p-1">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Headline <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="text" name="title" x-model="formData.title" @input="clearError('title')"
                                placeholder="e.g., Admissions Open for 2024-25 Session"
                                class="modal-input-premium pr-10"
                                :class="errors.title ? 'border-red-500' : 'border-slate-200'">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:text-indigo-500 transition-colors">
                                <i class="fas fa-heading text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.title">
                            <p class="modal-error-message" x-text="errors.title[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Distribution Date <span class="text-red-600 font-bold">*</span></label>
                        <div class="relative group">
                            <input type="date" name="publish_date" x-model="formData.publish_date" @input="clearError('publish_date')"
                                class="modal-input-premium pr-10 font-bold"
                                :class="errors.publish_date ? 'border-red-500' : 'border-slate-200'">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-focus-within:text-indigo-500 transition-colors">
                                <i class="fas fa-calendar-alt text-sm"></i>
                            </div>
                        </div>
                        <template x-if="errors.publish_date">
                            <p class="modal-error-message" x-text="errors.publish_date[0]"></p>
                        </template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Full Announcement Body <span class="text-red-600 font-bold">*</span></label>
                        <textarea name="content" x-model="formData.content" @input="clearError('content')"
                            rows="5" placeholder="Detailed message regarding the update..."
                            class="modal-input-premium px-4 py-3 resize-none h-40"
                            :class="errors.content ? 'border-red-500' : 'border-slate-200'"></textarea>
                        <template x-if="errors.content">
                            <p class="modal-error-message" x-text="errors.content[0]"></p>
                        </template>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'admission-news-modal')" class="btn-premium-cancel px-10">
                        Cancel
                    </button>
                    <button type="button" @click="submitForm()" :disabled="submitting"
                        class="btn-premium-primary min-w-[160px] !from-indigo-600 !to-blue-600 hover:!from-indigo-700 hover:!to-blue-700 shadow-indigo-200">
                        <template x-if="submitting">
                            <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span>
                        </template>
                        <span x-text="editMode ? 'Update Changes' : 'Broadcast Now'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
        <script>
            function admissionNewsManagement() {
                return {
                    submitting: false,
                    errors: {},
                    editMode: false,
                    newsId: null,
                    formData: {
                        title: '',
                        content: '',
                        publish_date: '{{ date('Y-m-d') }}'
                    },

                    clearError(field) {
                        if (this.errors && this.errors[field]) {
                            const e = { ...this.errors };
                            delete e[field];
                            this.errors = e;
                        }
                    },

                    resetForm() {
                        this.editMode = false;
                        this.newsId = null;
                        this.errors = {};
                        this.formData = { 
                            title: '',
                            content: '',
                            publish_date: '{{ date('Y-m-d') }}'
                        };
                    },

                    openAddModal() {
                        this.resetForm();
                        this.$dispatch('open-modal', 'admission-news-modal');
                    },

                    openEditModal(item) {
                        this.editMode = true;
                        this.newsId = item.id;
                        this.errors = {};
                        this.formData = { 
                            title: item.title || '',
                            content: item.content || '',
                            publish_date: item.publish_date || ''
                        };
                        this.$dispatch('open-modal', 'admission-news-modal');
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
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'admission-news-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                                window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(result, window.firstValidationMessage(this.errors)) });
                            } else {
                                throw new Error(window.resolveApiMessage(result, 'Something went wrong'));
                            }
                        } catch (error) {
                            window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(error.response?.data || { message: error.message }, error.message || 'Something went wrong') });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    confirmDelete(item) {
                        const self = this;
                        window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                            detail: {
                                title: 'Retract Announcement',
                                message: `Are you sure you want to retract the announcement "${item.title}"? This will remove it from public view immediately.`,
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

                                        const result = await response.json();

                                        if (response.ok) {
                                            if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message || 'Retracted successfully' });
                                            if (typeof self.refreshTable === 'function') self.refreshTable();
                                        } else {
                                            if (window.Toast) window.Toast.fire({ icon: 'error', title: window.resolveApiMessage(result, '') });
                                        }
                                    } catch (error) {
                                        if (window.Toast) window.Toast.fire({ icon: 'error', title: 'Retraction failed' });
                                    }
                                }
                            }
                        }));
                    },
                }
            }
        </script>
    @endpush
@endsection
