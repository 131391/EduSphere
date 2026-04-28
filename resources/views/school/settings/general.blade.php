@extends('layouts.school')

@section('title', 'General Settings - ' . $school->name)

@section('content')
<div class="w-full space-y-6 animate-in fade-in duration-500"
     x-data="generalSettings()">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-1">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm font-bold uppercase tracking-wider text-gray-400">
                    <li class="inline-flex items-center">
                        <a href="{{ route('school.dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center text-indigo-600">
                            <i class="fas fa-chevron-right mx-2 text-[11px] text-gray-300"></i>
                            <span>General Settings</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">General Settings</h1>
            <p class="text-base text-gray-500 mt-1 flex items-center font-medium">
                <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                Configure fees and receipt defaults for your school
            </p>
        </div>
    </div>

    <form @submit.prevent="submitForm()" novalidate class="space-y-6">
        @csrf

        {{-- Fee & Fine Configuration --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-money-bill-wave text-indigo-600 dark:text-indigo-400"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Fee & Fine Defaults</h2>
                    <p class="text-xs text-gray-400 mt-0.5">These values apply school-wide unless overridden per student.</p>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                {{-- Registration Fee --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Registration Fee
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 font-semibold text-sm pointer-events-none">₹</span>
                        <input type="number" step="0.01" min="0" name="registration_fee"
                               x-model="formData.registration_fee"
                               @input="clearError('registration_fee')"
                               placeholder="0.00"
                               class="w-full pl-8 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700 border rounded-xl text-sm font-medium text-gray-900 dark:text-gray-100 focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                               :class="errors.registration_fee ? 'border-red-500 bg-red-50' : 'border-gray-200'">
                    </div>
                    <template x-if="errors.registration_fee">
                        <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.registration_fee[0]"></span>
                        </p>
                    </template>
                </div>

                {{-- Admission Fee --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Admission Fee
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 font-semibold text-sm pointer-events-none">₹</span>
                        <input type="number" step="0.01" min="0" name="admission_fee"
                               x-model="formData.admission_fee"
                               @input="clearError('admission_fee')"
                               placeholder="0.00"
                               class="w-full pl-8 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700 border rounded-xl text-sm font-medium text-gray-900 dark:text-gray-100 focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                               :class="errors.admission_fee ? 'border-red-500 bg-red-50' : 'border-gray-200'">
                    </div>
                    <template x-if="errors.admission_fee">
                        <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.admission_fee[0]"></span>
                        </p>
                    </template>
                    {{-- Contextually placed toggle --}}
                    <label class="inline-flex items-center gap-2 mt-1 cursor-pointer select-none">
                        <input type="checkbox" name="admission_fee_applicable"
                               x-model="formData.admission_fee_applicable"
                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="text-xs font-medium text-gray-600">Charge on new admissions</span>
                    </label>
                </div>

                {{-- Library Fine --}}
                <div class="space-y-1.5">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Library Late Return Fine
                        <span class="text-gray-400 font-normal text-xs">(per day)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400 font-semibold text-sm pointer-events-none">₹</span>
                        <input type="number" step="0.01" min="0" name="late_return_library_book_fine"
                               x-model="formData.late_return_library_book_fine"
                               @input="clearError('late_return_library_book_fine')"
                               placeholder="0.00"
                               class="w-full pl-8 pr-4 py-2.5 bg-gray-50 dark:bg-gray-700 border rounded-xl text-sm font-medium text-gray-900 dark:text-gray-100 focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                               :class="errors.late_return_library_book_fine ? 'border-red-500 bg-red-50' : 'border-gray-200'">
                    </div>
                    <template x-if="errors.late_return_library_book_fine">
                        <p class="text-xs text-red-500 flex items-center gap-1 mt-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.late_return_library_book_fine[0]"></span>
                        </p>
                    </template>
                </div>

            </div>
        </div>

        {{-- Receipt Footer Note --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-file-alt text-amber-500 dark:text-amber-400"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Default Receipt Footer</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Printed at the bottom of all fee receipts.</p>
                </div>
            </div>

            <div class="p-6 space-y-2">
                <textarea name="receipt_note" rows="4" maxlength="1000"
                          x-model="formData.receipt_note"
                          @input="clearError('receipt_note')"
                          placeholder="e.g. This receipt is computer generated and does not require a signature."
                          class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border rounded-xl text-sm text-gray-800 dark:text-gray-100 leading-relaxed focus:bg-white dark:focus:bg-gray-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all resize-none"
                          :class="errors.receipt_note ? 'border-red-500 bg-red-50' : 'border-gray-200'"></textarea>
                <div class="flex items-center justify-between">
                    <template x-if="errors.receipt_note">
                        <p class="text-xs text-red-500 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle"></i>
                            <span x-text="errors.receipt_note[0]"></span>
                        </p>
                    </template>
                    <span x-show="!errors.receipt_note"></span>
                    <span class="text-xs text-gray-400 ml-auto"
                          :class="formData.receipt_note.length > 950 ? 'text-amber-500 font-semibold' : ''">
                        <span x-text="formData.receipt_note.length"></span>/1000
                    </span>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="flex items-center justify-end pt-1">
            <button type="submit" :disabled="submitting"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-60 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-indigo-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <template x-if="submitting">
                    <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                </template>
                <template x-if="!submitting">
                    <i class="fas fa-save text-xs"></i>
                </template>
                <span x-text="submitting ? 'Saving...' : 'Save Settings'"></span>
            </button>
        </div>

    </form>
</div>

@push('scripts')
<script>
function generalSettings() {
    return {
        submitting: false,
        errors: {},
        formData: {
            registration_fee: '{{ old('registration_fee', $settings['registration_fee'] ?? '') }}',
            admission_fee: '{{ old('admission_fee', $settings['admission_fee'] ?? '') }}',
            late_return_library_book_fine: '{{ old('late_return_library_book_fine', $settings['late_return_library_book_fine'] ?? '') }}',
            admission_fee_applicable: {{ !empty($settings['admission_fee_applicable']) ? 'true' : 'false' }},
            receipt_note: @js(old('receipt_note', $settings['receipt_note'] ?? '')),
        },

        clearError(field) {
            if (this.errors && this.errors[field]) {
                const e = { ...this.errors };
                delete e[field];
                this.errors = e;
            }
        },

        async submitForm() {
            if (this.submitting) return;
            this.submitting = true;
            this.errors = {};

            try {
                const response = await fetch('{{ route('school.settings.general.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ...this.formData,
                        _method: 'PUT'
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message: result.message || 'Settings saved successfully.', type: 'success' }
                    }));
                } else if (response.status === 422) {
                    this.errors = result.errors || {};
                } else {
                    throw new Error(result.message || 'Something went wrong.');
                }
            } catch (error) {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: error.message, type: 'error' }
                }));
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush
@endsection
