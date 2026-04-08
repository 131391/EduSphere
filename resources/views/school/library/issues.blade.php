@extends('layouts.school')

@section('title', 'Book Issuance & Returns')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Library Circulation</h1>
        <p class="text-gray-600">Track book issues and facilitate returns</p>
    </div>
    <a href="{{ route('school.library.index') }}" class="text-gray-600 hover:text-gray-900 flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Catalog
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Issue Book Form -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6 border-t-4 border-blue-600">
            <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-plus-circle text-blue-600 mr-2"></i> Issue New Book
            </h3>
            <form action="{{ route('school.library.issue.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Select Book</label>
                    <select name="book_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 select2">
                        <option value="">-- Choose Book --</option>
                        @foreach($books as $book)
                            <option value="{{ $book->id }}">{{ $book->title }} ({{ $book->available_quantity }} available)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Issue To (Student)</label>
                    <select name="student_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 select2">
                        <option value="">-- Choose Student --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->admission_no }} - {{ $student->full_name }} ({{ $student->class->name }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Due Date</label>
                    <input type="date" name="due_date" required min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+14 days')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 shadow-md transform active:scale-95 transition">
                    Issue Book
                </button>
            </form>
        </div>
    </div>

    <!-- Active Issues List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <h3 class="p-6 text-lg font-semibold text-gray-800 bg-gray-50 border-b flex items-center justify-between">
                <span>Active Issues</span>
                <span class="bg-blue-100 text-blue-800 text-xs px-2.5 py-0.5 rounded-full">{{ $activeIssues->total() }} total</span>
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Book</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Issued To</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dates</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($activeIssues as $issue)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-primary">{{ $issue->book->title }}</div>
                                <div class="text-xs text-gray-500">{{ $issue->book->author }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 font-medium">{{ $issue->student->full_name }}</div>
                                <div class="text-xs text-secondary">Class: {{ $issue->student->class->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-gray-500 mb-1">Issue: {{ $issue->issue_date->format('d M Y') }}</div>
                                <div class="text-xs font-bold {{ $issue->due_date->isPast() ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }} px-2 py-0.5 rounded inline-block">
                                    Due: {{ $issue->due_date->format('d M Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('school.library.return', $issue->id) }}" method="POST" onsubmit="return confirm('Confirm book return?')">
                                    @csrf
                                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-indigo-700 shadow-sm">
                                        Return Book
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">No active book issues found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($activeIssues->hasPages())
            <div class="px-6 py-4 border-t bg-gray-50">
                {{ $activeIssues->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
