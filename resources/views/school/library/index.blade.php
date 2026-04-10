@extends('layouts.school')

@section('title', 'Library Repository - Knowledge Center')

@section('content')
<div x-data="libraryCatalogManager()">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-amber-100/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                        <i class="fas fa-book text-xs"></i>
                    </div>
                    Digital Knowledge Repository
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage the complete book collection and asset catalog</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('school.library.issues') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-all shadow-sm">
                    <i class="fas fa-exchange-alt mr-2"></i>
                    Circulation Desk
                </a>
                <button @click="openAddModal()" 
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md hover:shadow-lg active:scale-95">
                    <i class="fas fa-plus mr-2"></i>
                    In-ward New Book
                </button>
            </div>
        </div>
    </div>

    @php
        $tableColumns = [
            [
                'key' => 'book_details',
                'label' => 'BOOK IDENTITY',
                'render' => function($row) {
                    return '
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shadow-sm group-hover:bg-amber-100 transition-all">
                            <i class="fas fa-atlas text-sm"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-800">' . e($row->title) . '</div>
                            <div class="text-[10px] font-bold text-amber-500 uppercase tracking-tighter">' . e($row->author) . '</div>
                        </div>
                    </div>';
                }
            ],
            [
                'key' => 'category',
                'label' => 'CATEGORY',
                'render' => function($row) {
                    return '<span class="px-2.5 py-1 bg-gray-100 text-gray-600 text-[10px] font-black rounded-lg uppercase tracking-tight border border-gray-200">' . e($row->category->name ?? 'Uncategorized') . '</span>';
                }
            ],
            [
                'key' => 'stock',
                'label' => 'INVENTORY STATUS',
                'render' => function($row) {
                    $percent = $row->quantity > 0 ? ($row->available_quantity / $row->quantity) * 100 : 0;
                    $color = $percent > 20 ? 'emerald' : ($percent > 0 ? 'amber' : 'red');
                    return '
                    <div class="flex flex-col gap-1 w-32">
                        <div class="flex items-center justify-between text-[10px] font-black uppercase text-gray-400">
                            <span>' . $row->available_quantity . ' / ' . $row->quantity . '</span>
                            <span class="text-'.$color.'-600">' . round($percent) . '%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-'.$color.'-500 h-full transition-all duration-1000" style="width: ' . $percent . '%"></div>
                        </div>
                    </div>';
                }
            ],
            [
                'key' => 'price',
                'label' => 'VALUATION',
                'render' => function($row) {
                    return '<span class="text-xs font-bold text-gray-700">' . number_format($row->price, 2) . '</span>';
                }
            ],
        ];

        $tableActions = [
            [
                'type' => 'button',
                'icon' => 'fas fa-edit',
                'class' => 'text-amber-600 hover:text-amber-900 bg-amber-50 hover:bg-amber-100 p-2 rounded-lg transition-colors',
                'onclick' => function($row) {
                    return "console.log('Edit coming soon')";
                },
                'title' => 'Edit',
            ],
        ];
    @endphp

    <div>
        <x-data-table 
            :columns="$tableColumns"
            :data="$books"
            :actions="$tableActions"
            empty-message="Library shelves are empty"
            empty-icon="fas fa-book-open"
        >
            Catalog Registry
        </x-data-table>
    </div>

    <!-- Add Book Modal -->
    <x-modal name="add-book-modal" alpineTitle="'Catalog New Acquisition'" maxWidth="2xl">
        <form @submit.prevent="submitForm" method="POST" class="p-0" novalidate>
            @csrf
            <div class="px-8 py-8 space-y-6">
                <!-- Row 1: Title & Author -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Book Title <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            name="title" 
                            x-model="formData.title"
                            @input="if(errors.title) delete errors.title"
                            placeholder="e.g., The Great Gatsby"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:border-amber-500 focus:bg-white transition-all font-medium text-gray-700"
                            :class="{'border-red-500': errors.title}"
                        >
                        <template x-if="errors.title">
                            <p class="text-[10px] text-red-500 mt-1 ml-1" x-text="errors.title[0]"></p>
                        </template>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Primary Author <span class="text-red-500">*</span></label>
                        <input 
                            type="text" 
                            name="author" 
                            x-model="formData.author"
                            @input="if(errors.author) delete errors.author"
                            placeholder="e.g., F. Scott Fitzgerald"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:border-amber-500 focus:bg-white transition-all font-medium text-gray-700"
                            :class="{'border-red-500': errors.author}"
                        >
                        <template x-if="errors.author">
                            <p class="text-[10px] text-red-500 mt-1 ml-1" x-text="errors.author[0]"></p>
                        </template>
                    </div>
                </div>

                <!-- Row 2: Category & ISBN -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Category <span class="text-red-500">*</span></label>
                        <select name="category_id" x-model="formData.category_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:border-amber-500 transition-all font-medium text-gray-700 appearance-none">
                            <option value="">-- Choose Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">ISBN Identifier</label>
                        <input 
                            type="text" 
                            name="isbn" 
                            x-model="formData.isbn"
                            placeholder="978-..."
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:border-amber-500 transition-all font-medium text-gray-700"
                        >
                    </div>
                </div>

                <!-- Row 3: Quantity & Price -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Stock Quantity <span class="text-red-500">*</span></label>
                        <input 
                            type="number" 
                            name="quantity" 
                            x-model="formData.quantity"
                            @input="if(errors.quantity) delete errors.quantity"
                            placeholder="1"
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:border-amber-500 transition-all font-bold text-gray-700 text-center"
                            :class="{'border-red-500': errors.quantity}"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 mb-1.5 ml-1">Asset Valuation</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 text-sm font-bold">$</div>
                            <input 
                                type="number" 
                                name="price" 
                                step="0.01"
                                x-model="formData.price"
                                placeholder="0.00"
                                class="w-full pl-8 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:border-amber-500 transition-all font-bold text-gray-700"
                            >
                        </div>
                    </div>
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
                    class="px-8 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-sm font-bold rounded-xl hover:from-amber-600 hover:to-orange-700 transition-all duration-200 shadow-lg shadow-amber-200 flex items-center justify-center min-w-[180px] gap-2 active:scale-95 disabled:opacity-50"
                >
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <span x-text="submitting ? 'Registering...' : 'Confirm Acquisition'"></span>
                </button>
            </div>
        </form>
    </x-modal>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('libraryCatalogManager', () => ({
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

        async submitForm() {
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
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'success', title: result.message });
                    }
                    setTimeout(() => window.location.reload(), 1000);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Operation failed');
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
            this.errors = {};
            this.formData = { title: '', author: '', category_id: '', isbn: '', quantity: 1, price: '' };
            this.$dispatch('open-modal', 'add-book-modal');
        },

        closeModal() {
            this.$dispatch('close-modal', 'add-book-modal');
        }
    }));
});
</script>
@endpush
@endsection
