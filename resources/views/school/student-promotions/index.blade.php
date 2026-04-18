@extends('layouts.school')
@section('title', 'Student Promotions')

@section('content')
    <div x-data="studentPromotion">

        {{-- Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Student Promotions</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Promote students to the next academic year</p>
                </div>
                <a href="{{ route('school.student-promotions.history') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-all">
                    <i class="fas fa-history mr-2"></i> Promotion History
                </a>
            </div>
        </div>

        {{-- Step 1: Year Selection --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-base font-bold text-gray-700 dark:text-white mb-4">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold mr-2">1</span>
                Select Academic Years
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">From Year <span class="text-red-500">*</span></label>
                    <select x-model="fromYearId" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">— Select —</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ $currentYear?->id === $year->id ? 'selected' : '' }}>
                                {{ $year->name }}{{ $year->is_current === \App\Enums\YesNo::Yes ? ' (Current)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-1">To Year <span class="text-red-500">*</span></label>
                    <select x-model="toYearId" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">— Select —</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button @click="loadPreview" :disabled="!fromYearId || !toYearId || loading"
                    class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                    <template x-if="loading">
                        <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block"></span>
                    </template>
                    <i x-show="!loading" class="fas fa-search mr-2"></i>
                    Preview Promotions
                </button>
            </div>
        </div>

        {{-- Step 2: Preview & Edit --}}
        <template x-if="preview">
            <div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-base font-bold text-gray-700 dark:text-white mb-1">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold mr-2">2</span>
                        Review & Adjust
                    </h3>
                    <p class="text-sm text-gray-500 mb-4 ml-8">
                        <span x-text="preview.total_students"></span> students found from
                        <strong x-text="preview.from_year.name"></strong> →
                        <strong x-text="preview.to_year.name"></strong>
                    </p>

                    <template x-for="cls in promotionData" :key="cls.class_id">
                        <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                            <div class="bg-indigo-50 dark:bg-indigo-900/30 px-4 py-3 flex items-center justify-between">
                                <span class="font-bold text-indigo-700 dark:text-indigo-300 text-sm" x-text="cls.class_name + ' → ' + cls.next_class_name"></span>
                                <span class="text-xs text-gray-500" x-text="cls.students.length + ' students'"></span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-700 text-xs text-gray-500 uppercase">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Student</th>
                                            <th class="px-4 py-2 text-left">Admission No</th>
                                            <th class="px-4 py-2 text-left">Current Section</th>
                                            <th class="px-4 py-2 text-left">Result</th>
                                            <th class="px-4 py-2 text-left">Target Section</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(student, idx) in cls.students" :key="student.student_id">
                                            <tr class="border-t border-gray-100 dark:border-gray-700">
                                                <td class="px-4 py-2 font-medium text-gray-800 dark:text-white" x-text="student.name"></td>
                                                <td class="px-4 py-2 text-gray-500" x-text="student.admission_no"></td>
                                                <td class="px-4 py-2 text-gray-500" x-text="student.section ?? '—'"></td>
                                                <td class="px-4 py-2">
                                                    <select x-model.number="student.result"
                                                        @change="onResultChange(cls, student)"
                                                        class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                        <option value="1">Promoted</option>
                                                        <option value="2">Graduated</option>
                                                        <option value="3">Detained</option>
                                                        <option value="4">Transferred</option>
                                                    </select>
                                                </td>
                                                <td class="px-4 py-2">
                                                    <template x-if="student.result === 1 || student.result === 4">
                                                        <select x-model.number="student.to_section_id"
                                                            class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                            <option value="">Auto</option>
                                                            <template x-for="sec in getSections(cls.next_class_id)" :key="sec.id">
                                                                <option :value="sec.id" x-text="sec.name"></option>
                                                            </template>
                                                        </select>
                                                    </template>
                                                    <template x-if="student.result === 2">
                                                        <span class="text-xs text-blue-500 font-semibold">Graduate</span>
                                                    </template>
                                                    <template x-if="student.result === 3">
                                                        <span class="text-xs text-orange-500 font-semibold">Same Class</span>
                                                    </template>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Step 3: Execute --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-base font-bold text-gray-700 dark:text-white mb-4">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-bold mr-2">3</span>
                        Execute Promotion
                    </h3>
                    <div class="flex items-center gap-4">
                        <button @click="executePromotion" :disabled="submitting"
                            class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="submitting">
                                <span class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2 inline-block"></span>
                            </template>
                            <i x-show="!submitting" class="fas fa-check-circle mr-2"></i>
                            Confirm & Promote
                        </button>
                        <button @click="preview = null; promotionData = []" class="text-sm text-gray-500 hover:text-gray-700">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </template>

    </div>

    @push('scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('studentPromotion', () => ({
            fromYearId: '{{ $currentYear?->id ?? '' }}',
            toYearId: '',
            loading: false,
            submitting: false,
            preview: null,
            promotionData: [],
            sections: {},

            async loadPreview() {
                if (!this.fromYearId || !this.toYearId || this.fromYearId === this.toYearId) return;
                this.loading = true;
                this.preview = null;
                this.promotionData = [];
                try {
                    const res = await fetch('{{ route('school.student-promotions.preview') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ from_year_id: this.fromYearId, to_year_id: this.toYearId })
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Preview failed');

                    this.preview = data.data;
                    this.sections = data.data.sections || {};

                    // Build mutable promotion data
                    this.promotionData = data.data.classes.map(cls => ({
                        class_id: cls.class_id,
                        class_name: cls.class_name,
                        next_class_id: cls.next_class_id,
                        next_class_name: cls.next_class_name,
                        students: cls.students.map(s => ({
                            student_id: s.id,
                            name: s.name,
                            admission_no: s.admission_no,
                            section: s.section,
                            result: s.result,
                            to_class_id: cls.next_class_id,
                            to_section_id: null,
                        }))
                    }));
                } catch (e) {
                    window.Toast?.fire({ icon: 'error', title: e.message });
                } finally {
                    this.loading = false;
                }
            },

            getSections(classId) {
                return this.sections[classId] ?? [];
            },

            onResultChange(cls, student) {
                if (student.result === 1 || student.result === 4) {
                    student.to_class_id = cls.next_class_id;
                } else {
                    student.to_class_id = null;
                    student.to_section_id = null;
                }
            },

            async executePromotion() {
                if (this.submitting) return;
                this.submitting = true;
                try {
                    const payload = {
                        from_year_id: this.fromYearId,
                        to_year_id: this.toYearId,
                        promotion_data: this.promotionData.map(cls => ({
                            class_id: cls.class_id,
                            students: cls.students.map(s => ({
                                student_id: s.student_id,
                                result: s.result,
                                to_class_id: s.to_class_id ?? null,
                                to_section_id: s.to_section_id ?? null,
                            }))
                        }))
                    };

                    const res = await fetch('{{ route('school.student-promotions.promote') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        window.Toast?.fire({ icon: 'success', title: data.message });
                        setTimeout(() => window.location.href = '{{ route('school.student-promotions.history') }}', 1200);
                    } else {
                        throw new Error(data.message || 'Promotion failed');
                    }
                } catch (e) {
                    window.Toast?.fire({ icon: 'error', title: e.message });
                } finally {
                    this.submitting = false;
                }
            }
        }));
    });
    </script>
    @endpush
@endsection
