@extends('layouts.school')

@section('title', 'Library Repository')

@section('content')
    <div x-data="Object.assign(ajaxDataTable({
        fetchUrl: '{{ route('school.library.fetch') }}',
        defaultSort: 'title',
        defaultDirection: 'asc',
        defaultPerPage: 25,
        defaultFilters: { category_id: '' },
        initialRows: @js($initialData['rows']),
        initialPagination: @js($initialData['pagination']),
        initialStats: @js($initialData['stats']),
        filterLabels: {
            category_id: { @foreach($categories as $c) '{{ $c->id }}': '{{ $c->name }}', @endforeach }
        }
    }), libraryCatalogManager())" class="space-y-6">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat-card label="Total Assets" :value="$stats['total_books']" icon="fas fa-boxes-stacked" color="amber" alpine-text="stats.total_books" />
            <x-stat-card label="Unique Titles" :value="$stats['available_titles']" icon="fas fa-atlas" color="indigo" alpine-text="stats.available_titles" />
            <x-stat-card label="Issued Volume" :value="$stats['issued_books']" icon="fas fa-exchange-alt" color="rose" alpine-text="stats.issued_books" />
            <x-stat-card label="Overdue Returns" :value="$stats['overdue_returns']" icon="fas fa-history" color="emerald" alpine-text="stats.overdue_returns" />
        </div>

        <!-- Header Section -->
        <x-page-header title="Digital Knowledge Repository" description="Manage institutional book catalog, inventory levels, and asset valuation across diverse subject categories." icon="fas fa-book">
            <div class="flex items-center gap-3">
                <a href="{{ route('school.library.issues') }}" 
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                    <i class="fas fa-exchange-alt mr-2 text-xs text-indigo-500"></i>
                    Circulation Desk
                </a>
                <button @click="openAddModal()"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2 text-xs"></i>
                    In-ward New Book
                </button>
            </div>
        </x-page-header>

        <!-- AJAX Data Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 flex flex-col md:flex-row md:items-center gap-4">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Catalog Registry</h2>
                        <x-table.search placeholder="Search by title, author, isbn..." />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-table.filter-select
                            model="filters.category_id"
                            action="applyFilter('category_id', $event.target.value)"
                            placeholder="All Categories"
                            :options="$categories->pluck('name', 'id')->toArray()"
                        />
                        <x-table.per-page model="perPage" action="changePerPage($event.target.value)" :default="25" />
                    </div>
                </div>

                <!-- Active Filter Tags -->
                <div class="mt-3 flex flex-wrap gap-2" x-show="hasActiveFilters()" x-cloak>
                    <template x-for="(value, key) in filters" :key="key">
                        <template x-if="value">
                            <div class="flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-100 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                <span x-text="key.replace('_', ' ') + ': ' + getFilterLabel(key, value)"></span>
                                <button @click="removeFilter(key)" class="ml-1 hover:text-amber-900 transition-colors">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                    </template>
                    <button @click="clearAllFilters()" class="text-[10px] font-bold text-red-600 hover:text-red-700 uppercase tracking-widest ml-2 transition-colors">
                        Clear All
                    </button>
                </div>
            </div>

            <!-- Table Body -->
            <div class="overflow-x-auto relative ajax-table-wrapper">
                <x-table.loading-overlay />

                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <x-table.sort-header column="title" label="Book Identity" sort-var="sort" direction-var="direction" />
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Inventory Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Valuation</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" :class="{ 'hidden': true }">
                        @foreach($initialData['rows'] as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 border border-amber-100 dark:border-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                            <i class="fas fa-atlas text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ $row['title'] }}</div>
                                            <div class="text-[10px] font-bold text-amber-500 uppercase tracking-tighter">{{ $row['author'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[10px] font-black rounded-lg uppercase tracking-tight border border-gray-200 dark:border-gray-600">{{ $row['category_name'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ $row['available_quantity'] }}</span>
                                        <span class="text-xs text-gray-400">/ {{ $row['total_quantity'] }}</span>
                                        <div class="w-20 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden ml-2">
                                            <div class="bg-{{ $row['status_color'] }}-500 h-full" style="width: {{ ($row['available_quantity'] / ($row['total_quantity'] ?: 1)) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-700 dark:text-gray-200">
                                    ₹{{ $row['price'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-indigo-600 transition-colors">
                                        <i class="fas fa-ellipsis-v text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-cloak :class="loading ? 'opacity-50' : 'opacity-100'">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 border border-amber-100 dark:border-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                            <i class="fas fa-atlas text-sm"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="row.title"></div>
                                            <div class="text-[10px] font-bold text-amber-500 uppercase tracking-tighter" x-text="row.author"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[10px] font-black rounded-lg uppercase tracking-tight border border-gray-200 dark:border-gray-600" x-text="row.category_name"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-200" x-text="row.available_quantity"></span>
                                        <span class="text-xs text-gray-400" x-text="'/ ' + row.total_quantity"></span>
                                        <div class="w-20 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden ml-2">
                                            <div class="h-full" :class="'bg-' + row.status_color + '-500'" :style="'width: ' + ((row.available_quantity / (row.total_quantity || 1)) * 100) + '%'"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-700 dark:text-gray-200" x-text="'₹' + row.price"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-indigo-600 transition-colors">
                                        <i class="fas fa-ellipsis-v text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="5" icon="fas fa-book-open" message="No books found in the repository." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination />
        </div>

        <!-- Add Book Modal -->
        <x-modal name="add-book-modal" alpineTitle="'Catalog New Acquisition'" maxWidth="2xl">
            <form @submit.prevent="submitForm()" method="POST" class="p-1">
                @csrf
                <div class="space-y-6">
                    <!-- Row 1: Title & Author -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="modal-label-premium">Book Title <span class="text-red-500">*</span></label>
                            <input type="text" x-model="formData.title" @input="clearError('title')" placeholder="e.g., The Great Gatsby"
                                class="modal-input-premium" :class="errors.title ? 'border-red-500' : ''">
                            <template x-if="errors.title"><p class="modal-error-message" x-text="errors.title[0]"></p></template>
                        </div>
                        <div class="space-y-2">
                            <label class="modal-label-premium">Author <span class="text-red-500">*</span></label>
                            <input type="text" x-model="formData.author" @input="clearError('author')" placeholder="e.g., Fitzgerald"
                                class="modal-input-premium" :class="errors.author ? 'border-red-500' : ''">
                            <template x-if="errors.author"><p class="modal-error-message" x-text="errors.author[0]"></p></template>
                        </div>
                    </div>

                    <!-- Row 2: Category & ISBN -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="modal-label-premium">Category <span class="text-red-500">*</span></label>
                            <select x-model="formData.category_id" @change="clearError('category_id')" class="modal-input-premium appearance-none pr-10">
                                <option value="">Choose Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="modal-label-premium">ISBN</label>
                            <input type="text" x-model="formData.isbn" placeholder="978-..." class="modal-input-premium font-mono">
                        </div>
                    </div>

                    <!-- Row 3: Quantity & Price -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="modal-label-premium">Stock Quantity <span class="text-red-500">*</span></label>
                            <input type="number" x-model="formData.quantity" @input="clearError('quantity')" class="modal-input-premium text-center font-bold">
                            <template x-if="errors.quantity"><p class="modal-error-message" x-text="errors.quantity[0]"></p></template>
                        </div>
                        <div class="space-y-2">
                            <label class="modal-label-premium">Asset Price</label>
                            <div class="relative group">
                                <input type="number" step="0.01" x-model="formData.price" placeholder="0.00" class="modal-input-premium pr-10 font-bold">
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 font-bold text-sm">₹</div>
                            </div>
                        </div>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'add-book-modal')" class="btn-premium-cancel px-10">Discard</button>
                    <button type="submit" :disabled="submitting" class="btn-premium-primary min-w-[200px] !from-amber-500 !to-orange-600 shadow-amber-200">
                        <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                        <span x-text="submitting ? 'Registering...' : 'Add to Catalog'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>
    </div>

    @push('scripts')
        <script>
            function libraryCatalogManager() {
                return {
                    submitting: false,
                    errors: {},
                    formData: {
                        title: '',
                        author: '',
                        category_id: '',
                        isbn: '',
                        quantity: 1,
                        price: ''
                    },

                    clearError(field) {
                        if (this.errors[field]) delete this.errors[field];
                    },

                    openAddModal() {
                        this.errors = {};
                        this.formData = { title: '', author: '', category_id: '', isbn: '', quantity: 1, price: '' };
                        this.$dispatch('open-modal', 'add-book-modal');
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        try {
                            const response = await fetch('{{ route('school.library.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(this.formData)
                            });

                            const result = await response.json();
                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'add-book-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                            } else {
                                throw new Error(result.message || 'Operation failed');
                            }
                        } catch (e) {
                            if (window.Toast) window.Toast.fire({ icon: 'error', title: e.message });
                        } finally {
                            this.submitting = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
