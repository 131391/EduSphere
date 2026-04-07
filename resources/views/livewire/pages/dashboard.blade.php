<div class="space-y-6 rounded-lg bg-white p-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-2 text-gray-600">Welcome to EduSphere - Enterprise School ERP with Livewire 3</p>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        @foreach(['Students' => 150, 'Teachers' => 25, 'Classes' => 10, 'Revenue' => '$45K'] as $label => $value)
            <div class="rounded-lg border border-gray-200 bg-gradient-to-br from-blue-50 to-blue-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">{{ $label }}</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $value }}</p>
                    </div>
                    <div class="rounded-lg bg-blue-200 p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-6">
        <h2 class="text-lg font-semibold text-gray-900">Quick Stats</h2>
        <div class="mt-4 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Attendance Rate</span>
                <div class="h-2 w-24 rounded-full bg-gray-200">
                    <div class="h-full w-3/4 rounded-full bg-green-500"></div>
                </div>
                <span class="text-sm font-medium text-gray-900">75%</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Fee Collection</span>
                <div class="h-2 w-24 rounded-full bg-gray-200">
                    <div class="h-full w-2/3 rounded-full bg-blue-500"></div>
                </div>
                <span class="text-sm font-medium text-gray-900">67%</span>
            </div>
        </div>
    </div>
</div>
