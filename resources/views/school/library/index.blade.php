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
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Issue New Book</h3>
            <form action="{{ route('school.library.issue.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Select Book</label>
                    <select name="book_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 select2">
                        <option value="">-- Choose --</option>
                        @foreach($books as $book)
                            <option value="{{ $book->id }}">{{ $book->title }} ({{ $book->available_quantity }} left)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Issue To (Student)</label>
                    <select name="student_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 select2">
                        <option value="">-- Choose --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}">{{ $student->admission_no }} - {{ $student->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Due Date</label>
                    <input type="date" name="due_date" required min="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg font-bold hover:bg-blue-700 shadow-md">
                    Issue Book
                </button>
            </form>
        </div>
    </div>

    <!-- Active Issues List -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <h3 class="p-6 text-lg font-semibold text-gray-800 bg-gray-50 border-b">Active Issues</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issued To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($activeIssues as $issue)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-bold text-gray-900">{{ $issue->book->title }}</div>
                            <div class="text-xs text-gray-500">{{ $issue->book->author }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $issue->student->full_name }}</div>
                            <div class="text-xs text-gray-500">ID: {{ $issue->student->admission_no }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs">Issue: {{ $issue->issue_date->format('d M Y') }}</div>
                            <div class="text-xs font-bold {{ $issue->due_date->isPast() ? 'text-red-600' : 'text-green-600' }}">
                                Due: {{ $issue->due_date->format('d M Y') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form action="{{ route('school.library.return', $issue->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold hover:bg-green-200 transition">
                                    Return
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t">
                {{ $activeIssues->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
