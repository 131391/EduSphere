@extends('layouts.school')

@section('title', 'Generate Class Fees')

@section('content')
<div x-data="feeGenerator()">

    {{-- Page Header --}}
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('school.fees.index') }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:border-emerald-300 transition-all shadow-sm">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Generate Class Fees</h1>
            <p class="text-xs text-gray-400 mt-0.5">Create fee records for all active students in a class at once.</p>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="mb-6 flex items-start gap-3 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 text-sm text-blue-700">
        <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
        <span>This will create individual fee records for every <strong>active student</strong> in the selected class. Duplicate records for the same period are automatically skipped.</span>
    </div>

    <form @submit.prevent="generateFees" method="POST" class="space-y-6">
        @csrf

        {{-- Section 1: Fee Configuration --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">1</span>
                <h2 class="text-sm font-semibold text-gray-700">Fee Configuration</h2>
            </div>

            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                {{-- Class --}}
                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Class <span class="text-red-500">*</span>
                    </label>
                    <select id="class_id" name="class_id"
                            x-model="formData.class_id"
                            required
                            @change="if(errors.class_id) delete errors.class_id"
                            class="no-select2 w-full px-3 py-2.5 bg-white border rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                            :class="errors.class_id ? 'border-red-400' : 'border-gray-300'">
                        <option value="">Select a class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <template x-if="errors.class_id">
                        <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.class_id[0]"></span>
                        </p>
                    </template>
                </div>

                {{-- Academic Year --}}
                <div>
                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Academic Year <span class="text-red-500">*</span>
                    </label>
                    <select id="academic_year_id" name="academic_year_id"
                            x-model="formData.academic_year_id"
                            required
                            class="no-select2 w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <option value="">Select academic year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @if($year->is_current) selected @endif>
                                {{ $year->name }}@if($year->is_current) (Current)@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Fee Type --}}
                <div>
                    <label for="fee_type_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Fee Type <span class="text-red-500">*</span>
                    </label>
                    <select id="fee_type_id" name="fee_type_id"
                            x-model="formData.fee_type_id"
                            required
                            @change="if(errors.fee_type_id) delete errors.fee_type_id"
                            class="no-select2 w-full px-3 py-2.5 bg-white border rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                            :class="errors.fee_type_id ? 'border-red-400' : 'border-gray-300'">
                        <option value="">Select fee type</option>
                        @foreach($feeTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <template x-if="errors.fee_type_id">
                        <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.fee_type_id[0]"></span>
                        </p>
                    </template>
                </div>

                {{-- Fee Period --}}
                <div>
                    <label for="fee_period" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Fee Period <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="fee_period"
                           name="fee_period"
                           x-model="formData.fee_period"
                           placeholder="e.g. {{ date('F Y') }}"
                           required
                           class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <p class="mt-1 text-xs text-gray-400">Enter the month and year, e.g. "April 2025"</p>
                </div>

                {{-- Due Date --}}
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Due Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                           id="due_date"
                           name="due_date"
                           x-model="formData.due_date"
                           min="{{ date('Y-m-d') }}"
                           required
                           class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                    <p class="mt-1 text-xs text-gray-400">Must be today or a future date.</p>
                </div>

            </div>
        </div>

        {{-- Section 2: Fee Components --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">2</span>
                    <h2 class="text-sm font-semibold text-gray-700">Fee Components</h2>
                    <span class="text-xs text-gray-400">— select all that apply to this class</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500" x-show="formData.fee_name_ids.length > 0">
                        <span class="font-semibold text-indigo-600" x-text="formData.fee_name_ids.length"></span> selected
                    </span>
                    <label class="flex items-center gap-1.5 cursor-pointer text-xs font-medium text-gray-600 hover:text-indigo-600 transition select-none">
                        <input type="checkbox"
                               @change="toggleAll"
                               x-model="allSelected"
                               class="rounded w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        Select all
                    </label>
                </div>
            </div>

            <div class="p-6">
                @if($feeNames->isEmpty())
                    <div class="text-center py-10 text-gray-400">
                        <i class="fas fa-receipt text-3xl mb-3 block"></i>
                        <p class="text-sm font-medium">No fee components found.</p>
                        <p class="text-xs mt-1">Please add fee names first before generating fees.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                        @foreach($feeNames as $name)
                        <label class="cursor-pointer">
                            <input type="checkbox"
                                   name="fee_name_ids[]"
                                   value="{{ $name->id }}"
                                   x-model="formData.fee_name_ids"
                                   @change="syncSelectAll()"
                                   class="sr-only">
                            <div class="flex items-center gap-3 p-3.5 rounded-lg border transition-all duration-150 hover:shadow-sm"
                                 :class="formData.fee_name_ids.includes('{{ $name->id }}')
                                    ? 'bg-indigo-50 border-indigo-300'
                                    : 'bg-gray-50 border-gray-200 hover:bg-white hover:border-indigo-200'">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-all"
                                     :class="formData.fee_name_ids.includes('{{ $name->id }}')
                                        ? 'bg-indigo-600 text-white border border-indigo-600'
                                        : 'bg-white text-gray-400 border border-gray-200'">
                                    <i class="fas text-xs"
                                       :class="formData.fee_name_ids.includes('{{ $name->id }}') ? 'fa-check' : 'fa-receipt'"></i>
                                </div>
                                <span class="text-sm font-medium leading-tight"
                                      :class="formData.fee_name_ids.includes('{{ $name->id }}') ? 'text-indigo-800' : 'text-gray-700'"
                                >{{ $name->name }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    {{-- Validation error for fee_name_ids --}}
                    <template x-if="errors.fee_name_ids">
                        <p class="mt-3 text-xs text-red-500 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.fee_name_ids[0]"></span>
                        </p>
                    </template>

                    <p class="mt-3 text-xs text-gray-400" x-show="formData.fee_name_ids.length === 0">
                        <i class="fas fa-hand-pointer mr-1"></i> Select at least one fee component to continue.
                    </p>
                @endif
            </div>
        </div>

        {{-- Action Bar --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-xl border border-gray-200 shadow-sm px-6 py-4">
            <p class="text-xs text-gray-500 flex items-center gap-1.5">
                <i class="fas fa-shield-alt text-amber-500"></i>
                Fee records already generated for the same period will be skipped automatically.
            </p>
            <div class="flex items-center gap-3">
                <a href="{{ route('school.fees.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button
                    type="submit"
                    :disabled="submitting || formData.fee_name_ids.length === 0 || !formData.class_id || !formData.fee_type_id"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed disabled:active:scale-100"
                >
                    <template x-if="submitting">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    </template>
                    <i x-show="!submitting" class="fas fa-bolt text-xs"></i>
                    <span x-text="submitting ? 'Generating...' : 'Generate Fees'"></span>
                </button>
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('feeGenerator', () => ({
        submitting: false,
        allSelected: false,
        errors: {},
        formData: {
            class_id: '{{ old('class_id') }}',
            academic_year_id: '{{ $academicYears->where('is_current', true)->first()->id ?? ($academicYears->first()->id ?? '') }}',
            fee_type_id: '',
            fee_period: '{{ date('F Y') }}',
            due_date: '{{ date('Y-m-d', strtotime('+10 days')) }}',
            fee_name_ids: []
        },

        async generateFees() {
            if (!this.formData.class_id || this.formData.fee_name_ids.length === 0) return;

            this.submitting = true;
            this.errors = {};
            try {
                const response = await fetch('{{ route('school.fees.store') }}', {
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
                    setTimeout(() => window.location.href = '{{ route('school.fees.index') }}', 1000);
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                    if (window.Toast) {
                        window.Toast.fire({ icon: 'error', title: 'Please correct the highlighted errors.' });
                    }
                } else {
                    throw new Error(result.message || 'Generation failed');
                }
            } catch (err) {
                if (window.Toast) {
                    window.Toast.fire({ icon: 'error', title: err.message });
                }
            } finally {
                this.submitting = false;
            }
        },

        toggleAll() {
            if (this.allSelected) {
                this.formData.fee_name_ids = [@foreach($feeNames as $n)'{{ $n->id }}',@endforeach];
            } else {
                this.formData.fee_name_ids = [];
            }
        },

        syncSelectAll() {
            const total = {{ $feeNames->count() }};
            this.allSelected = this.formData.fee_name_ids.length === total;
        }
    }));
});
</script>
@endpush
@endsection
