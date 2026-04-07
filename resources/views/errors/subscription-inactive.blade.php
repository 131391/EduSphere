@extends('errors.layout')

@section('title', __('Subscription Inactive'))
@section('code', '403')
@section('message')
    <div class="space-y-4 text-left">
        <p class="text-lg font-semibold text-gray-700">School subscription is inactive.</p>
        <p class="text-sm text-gray-500 max-w-xl">
            The school account you are trying to access has an inactive or expired subscription. Access is currently blocked until the subscription is renewed.
        </p>

        @if(isset($school))
            <div class="rounded-2xl bg-white border border-gray-200 shadow-sm p-6 text-left">
                <p class="text-xs uppercase tracking-wide text-gray-400">School</p>
                <p class="mt-2 text-xl font-semibold text-gray-900">{{ $school->name }}</p>
                <p class="text-sm text-gray-600">Code: {{ $school->code }}</p>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase text-gray-400">Status</p>
                        <p class="mt-1 text-sm text-gray-700">{{ $school->status instanceof \App\Enums\SchoolStatus ? $school->status->name : $school->status }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-400">Subscription ends</p>
                        <p class="mt-1 text-sm text-gray-700">{{ $school->subscription_end_date ? $school->subscription_end_date->format('M d, Y') : 'Not set' }}</p>
                    </div>
                </div>

                @if(auth()->check() && auth()->user()->hasRole(\App\Models\Role::SUPER_ADMIN))
                    <div class="mt-6">
                        <a href="{{ route('admin.schools.edit', $school) }}" class="inline-flex items-center justify-center rounded-full bg-indigo-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-800">
                            Renew / Manage Subscription
                        </a>
                    </div>
                @endif
            </div>
        @endif

        <div class="flex flex-wrap gap-3 justify-center sm:justify-start">
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full bg-teal-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">
                Sign in to a different school
            </a>
            <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Back to Home
            </a>
        </div>
    </div>
@endsection
