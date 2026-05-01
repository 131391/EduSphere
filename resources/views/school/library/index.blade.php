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
                <button @click="openCategoryModal()"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                    <i class="fas fa-tags mr-2 text-xs text-amber-500"></i>
                    {{ $categories->isEmpty() ? 'Create First Category' : 'Add Category' }}
                </button>
                <a href="{{ route('school.library.export.catalog') }}"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                    <i class="fas fa-file-csv mr-2 text-xs text-emerald-500"></i>
                    Export CSV
                </a>
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

        <div x-show="!hasCategories" x-cloak class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900">
            <div>
                <div class="text-sm font-bold">Book categories are required before cataloging inventory.</div>
                <p class="text-xs font-medium text-amber-800/80 mt-1">Create your first category so library staff can add books without leaving this page.</p>
            </div>
            <button @click="openCategoryModal()" class="inline-flex items-center justify-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-xl transition-all shadow-sm">
                <i class="fas fa-plus mr-2 text-xs"></i>
                Create Category
            </button>
        </div>

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

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700" data-ssr x-show="!hydrated">
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
                                    <div class="flex justify-center items-center gap-2">
                                        <button class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-amber-600 transition-colors">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-rose-600 transition-colors">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700 transition-opacity duration-150" x-show="hydrated" :class="loading && rows.length &gt; 0 ? 'opacity-50' : 'opacity-100'">
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
                                    <div class="flex justify-center items-center gap-2">
                                        <button @click="openStockModal(row)" class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-emerald-600 transition-colors" title="Adjust Stock">
                                            <i class="fas fa-boxes-stacked text-xs"></i>
                                        </button>
                                        <button @click="openEditModal(row)" class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-amber-600 transition-colors" title="Edit Book">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <button @click="confirmDeleteBook(row.id)" class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-400 hover:text-rose-600 transition-colors" title="Remove from Catalog">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <x-table.empty-state :colspan="5" icon="fas fa-book-open" message="No books found in the repository." />
                    </tbody>
                </table>
            </div>

            <x-table.pagination :initial="$initialData['pagination']" />
        </div>

        <!-- Add/Edit Book Modal -->
        <x-modal name="book-modal" alpineTitle="isEdit ? 'Refine Catalog Entry' : 'Catalog New Acquisition'" maxWidth="2xl">
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
                            <select x-model="formData.category_id" @change="clearError('category_id')" class="modal-input-premium no-select2 appearance-none pr-10">
                                <option value="">Choose Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <template x-if="errors.category_id"><p class="modal-error-message" x-text="errors.category_id[0]"></p></template>
                        </div>
                        <div class="space-y-2">
                            <label class="modal-label-premium">ISBN</label>
                            <input type="text" x-model="formData.isbn" placeholder="978-..." class="modal-input-premium font-mono">
                            <template x-if="errors.isbn"><p class="modal-error-message" x-text="errors.isbn[0]"></p></template>
                        </div>
                    </div>

                    <!-- Row 3: Quantity & Price -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="modal-label-premium">Stock Quantity <span class="text-red-500">*</span></label>
                            <input type="number" x-model="formData.quantity" @input="clearError('quantity')" :disabled="isEdit"
                                class="modal-input-premium text-center font-bold" :class="isEdit ? 'bg-gray-50 dark:bg-gray-900/50 cursor-not-allowed' : ''">
                            <template x-if="isEdit">
                                <p class="text-[10px] text-gray-400 mt-1 italic italic">Total quantity cannot be reduced manually once cataloged.</p>
                            </template>
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
                    <button type="button" @click="$dispatch('close-modal', 'book-modal')" class="btn-premium-cancel px-10">Discard</button>
                    <button type="button" @click="submitForm()" :disabled="submitting" class="btn-premium-primary min-w-[200px] !from-amber-500 !to-orange-600 shadow-amber-200">
                        <template x-if="submitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                        <span x-text="submitting ? (isEdit ? 'Updating...' : 'Registering...') : (isEdit ? 'Save Changes' : 'Add to Catalog')"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <!-- Adjust Stock Modal -->
        <x-modal name="stock-modal" alpineTitle="'Adjust Stock — ' + (stockTarget?.title || '')" maxWidth="lg">
            <form @submit.prevent="submitStockAdjust()" method="POST" class="p-1">
                @csrf
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl">
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Current Stock</div>
                            <div class="text-2xl font-black text-gray-800 dark:text-gray-100" x-text="stockTarget?.total_quantity ?? '—'"></div>
                        </div>
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Available</div>
                            <div class="text-2xl font-black text-emerald-600" x-text="stockTarget?.available_quantity ?? '—'"></div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Adjustment <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <button type="button" @click="stockData.delta = -Math.abs(stockData.delta || 1)"
                                class="px-3 py-2 text-xs font-bold rounded-lg border" :class="stockData.delta < 0 ? 'bg-rose-50 border-rose-300 text-rose-700' : 'bg-white border-gray-200 text-gray-500'">−</button>
                            <input type="number" x-model.number="stockData.delta" @input="clearStockError('delta')"
                                placeholder="e.g. 5 or -2" class="modal-input-premium text-center font-bold flex-1"
                                :class="stockErrors.delta ? 'border-red-500' : ''">
                            <button type="button" @click="stockData.delta = Math.abs(stockData.delta || 1)"
                                class="px-3 py-2 text-xs font-bold rounded-lg border" :class="stockData.delta > 0 ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'bg-white border-gray-200 text-gray-500'">+</button>
                        </div>
                        <p class="text-[10px] text-gray-400 italic">Positive to add new copies, negative to remove. Cannot reduce below copies currently issued.</p>
                        <template x-if="stockErrors.delta"><p class="modal-error-message" x-text="stockErrors.delta[0]"></p></template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Reason <span class="text-red-500">*</span></label>
                        <select x-model="stockData.reason" @change="clearStockError('reason')" class="modal-input-premium no-select2 appearance-none pr-10">
                            <option value="purchase">Purchase / New Acquisition</option>
                            <option value="donation">Donation</option>
                            <option value="damage">Damage</option>
                            <option value="shrinkage">Shrinkage / Missing</option>
                            <option value="audit_correction">Audit Correction</option>
                            <option value="other">Other</option>
                        </select>
                        <template x-if="stockErrors.reason"><p class="modal-error-message" x-text="stockErrors.reason[0]"></p></template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Note</label>
                        <input type="text" x-model="stockData.note" maxlength="500" placeholder="Optional context for the audit log"
                            class="modal-input-premium">
                        <template x-if="stockErrors.note"><p class="modal-error-message" x-text="stockErrors.note[0]"></p></template>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'stock-modal')" class="btn-premium-cancel px-10">Cancel</button>
                    <button type="button" @click="submitStockAdjust()" :disabled="stockSubmitting" class="btn-premium-primary min-w-[200px] !from-emerald-600 !to-teal-700">
                        <template x-if="stockSubmitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                        <span x-text="stockSubmitting ? 'Applying...' : 'Apply Adjustment'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-modal name="book-category-modal" alpineTitle="'Create Book Category'" maxWidth="xl">
            <form @submit.prevent="submitCategoryForm()" method="POST" class="p-1">
                @csrf
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="modal-label-premium">Category Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="categoryFormData.name" @input="clearCategoryError('name')" placeholder="e.g., Fiction, Science, Reference"
                            class="modal-input-premium" :class="categoryErrors.name ? 'border-red-500' : ''">
                        <template x-if="categoryErrors.name"><p class="modal-error-message" x-text="categoryErrors.name[0]"></p></template>
                    </div>

                    <div class="space-y-2">
                        <label class="modal-label-premium">Description</label>
                        <textarea x-model="categoryFormData.description" @input="clearCategoryError('description')" rows="4"
                            placeholder="Optional notes for librarians and catalog staff."
                            class="modal-input-premium resize-none" :class="categoryErrors.description ? 'border-red-500' : ''"></textarea>
                        <template x-if="categoryErrors.description"><p class="modal-error-message" x-text="categoryErrors.description[0]"></p></template>
                    </div>
                </div>

                <x-slot name="footer">
                    <button type="button" @click="$dispatch('close-modal', 'book-category-modal')" class="btn-premium-cancel px-10">Discard</button>
                    <button type="button" @click="submitCategoryForm()" :disabled="categorySubmitting" class="btn-premium-primary min-w-[200px] !from-slate-700 !to-slate-900 shadow-slate-200">
                        <template x-if="categorySubmitting"><span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-3 inline-block"></span></template>
                        <span x-text="categorySubmitting ? 'Creating...' : 'Save Category'"></span>
                    </button>
                </x-slot>
            </form>
        </x-modal>

        <x-confirm-modal />
    </div>

    @push('scripts')
        <script>
            function libraryCatalogManager() {
                return {
                    submitting: false,
                    categorySubmitting: false,
                    hasCategories: @js($categories->isNotEmpty()),
                    errors: {},
                    categoryErrors: {},
                    isEdit: false,
                    editingId: null,
                    formData: {
                        title: '',
                        author: '',
                        category_id: '',
                        isbn: '',
                        quantity: 1,
                        price: ''
                    },
                    categoryFormData: {
                        name: '',
                        description: ''
                    },
                    stockSubmitting: false,
                    stockErrors: {},
                    stockTarget: null,
                    stockData: {
                        delta: 1,
                        reason: 'purchase',
                        note: ''
                    },

                    clearError(field) {
                        if (this.errors && this.errors[field]) {
                            const e = { ...this.errors };
                            delete e[field];
                            this.errors = e;
                        }
                    },

                    clearCategoryError(field) {
                        if (this.categoryErrors && this.categoryErrors[field]) {
                            const e = { ...this.categoryErrors };
                            delete e[field];
                            this.categoryErrors = e;
                        }
                    },

                    openCategoryModal() {
                        this.categoryErrors = {};
                        this.categoryFormData = { name: '', description: '' };
                        this.$dispatch('open-modal', 'book-category-modal');
                    },

                    openAddModal() {
                        if (!this.hasCategories) {
                            if (window.Toast) window.Toast.fire({ icon: 'info', title: 'Create a book category before adding catalog entries.' });
                            this.openCategoryModal();
                            return;
                        }

                        this.isEdit = false;
                        this.editingId = null;
                        this.errors = {};
                        this.formData = { title: '', author: '', category_id: '', isbn: '', quantity: 1, price: '' };
                        this.$dispatch('open-modal', 'book-modal');
                    },

                    clearStockError(field) {
                        if (this.stockErrors && this.stockErrors[field]) {
                            const e = { ...this.stockErrors };
                            delete e[field];
                            this.stockErrors = e;
                        }
                    },

                    openStockModal(book) {
                        this.stockErrors = {};
                        this.stockTarget = book;
                        this.stockData = { delta: 1, reason: 'purchase', note: '' };
                        this.$dispatch('open-modal', 'stock-modal');
                    },

                    async submitStockAdjust() {
                        if (this.stockSubmitting || !this.stockTarget) return;
                        this.stockSubmitting = true;
                        this.stockErrors = {};

                        try {
                            const url = `{{ route('school.library.books.adjust-stock', ['book' => '__BOOK__']) }}`.replace('__BOOK__', this.stockTarget.id);
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(this.stockData)
                            });

                            const result = await response.json();
                            if (response.ok && result.success) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.$dispatch('close-modal', 'stock-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else if (response.status === 422 && result.errors) {
                                this.stockErrors = result.errors;
                                window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(result, window.firstValidationMessage(this.stockErrors)) });
                            } else {
                                throw new Error(window.resolveApiMessage(result, ''));
                            }
                        } catch (e) {
                            window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(e.response?.data || { message: e.message }, e.message || 'Something went wrong') });
                        } finally {
                            this.stockSubmitting = false;
                        }
                    },

                    openEditModal(book) {
                        this.isEdit = true;
                        this.editingId = book.id;
                        this.errors = {};
                        this.formData = {
                            title: book.title,
                            author: book.author,
                            category_id: book.category_id,
                            isbn: book.isbn === 'N/A' ? '' : book.isbn,
                            quantity: book.total_quantity,
                            price: book.price.replace(/,/g, '')
                        };
                        this.$dispatch('open-modal', 'book-modal');
                    },

                    confirmDeleteBook(bookId) {
                        this.$dispatch('open-confirm-modal', {
                            title: 'Purge Book from Catalog?',
                            message: 'This will permanently remove the book record. If this book has circulation history, the operation may be restricted to preserve data integrity.',
                            callback: async () => {
                                try {
                                    const response = await fetch(`{{ route('school.library.books.destroy', ['book' => '__BOOK__']) }}`.replace('__BOOK__', bookId), {
                                        method: 'DELETE',
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        }
                                    });
                                    const result = await response.json();
                                    if (response.ok) {
                                        if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                        if (typeof this.refreshTable === 'function') this.refreshTable();
                                    } else {
                                        throw new Error(window.resolveApiMessage(result, ''));
                                    }
                                } catch (e) {
                                    window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(e.response?.data || { message: e.message }, e.message || 'Something went wrong') });
                                }
                            }
                        });
                    },

                    async submitForm() {
                        if (this.submitting) return;
                        this.submitting = true;
                        this.errors = {};

                        const url = this.isEdit 
                            ? `{{ route('school.library.books.update', ['book' => '__BOOK__']) }}`.replace('__BOOK__', this.editingId)
                            : '{{ route('school.library.books.store') }}';
                        
                        const method = this.isEdit ? 'PUT' : 'POST';

                        try {
                            const response = await fetch(url, {
                                method: method,
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
                                this.$dispatch('close-modal', 'book-modal');
                                if (typeof this.refreshTable === 'function') this.refreshTable();
                            } else if (response.status === 422) {
                                this.errors = result.errors || {};
                                window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(result, window.firstValidationMessage(this.errors)) });
                            } else {
                                throw new Error(window.resolveApiMessage(result, ''));
                            }
                        } catch (e) {
                            window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(e.response?.data || { message: e.message }, e.message || 'Something went wrong') });
                        } finally {
                            this.submitting = false;
                        }
                    },

                    async submitCategoryForm() {
                        if (this.categorySubmitting) return;
                        this.categorySubmitting = true;
                        this.categoryErrors = {};

                        try {
                            const response = await fetch('{{ route('school.library.categories.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(this.categoryFormData)
                            });

                            const result = await response.json();
                            if (response.ok) {
                                if (window.Toast) window.Toast.fire({ icon: 'success', title: result.message });
                                this.hasCategories = true;
                                this.$dispatch('close-modal', 'book-category-modal');
                                window.location.reload();
                            } else if (response.status === 422) {
                                this.categoryErrors = result.errors || {};
                                window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(result, window.firstValidationMessage(this.categoryErrors)) });
                            } else {
                                throw new Error(window.resolveApiMessage(result, ''));
                            }
                        } catch (e) {
                            window.Toast?.fire({ icon: 'error', title: window.resolveApiMessage(e.response?.data || { message: e.message }, e.message || 'Something went wrong') });
                        } finally {
                            this.categorySubmitting = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
