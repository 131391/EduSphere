<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Enums\SchoolStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $query = School::withTrashed();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('city', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('state', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $statusMap = [
                'active' => SchoolStatus::Active->value,
                'inactive' => SchoolStatus::Inactive->value,
                'suspended' => SchoolStatus::Suspended->value,
            ];
            if (isset($statusMap[$request->status])) {
                $query->where('status', $statusMap[$request->status]);
            }
        }

        // Filter by subscription status
        if ($request->filled('subscription_status')) {
            if ($request->subscription_status === 'active') {
                $query->where(function($q) {
                    $q->whereNull('subscription_end_date')
                      ->orWhere('subscription_end_date', '>=', now());
                });
            } elseif ($request->subscription_status === 'expired') {
                $query->where('subscription_end_date', '<', now());
            }
        }

        // Sorting
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        
        // Validate sort column to prevent SQL injection
        $allowedSortColumns = ['id', 'name', 'code', 'email', 'status', 'created_at', 'subscription_end_date'];
        if (!in_array($sortColumn, $allowedSortColumns)) {
            $sortColumn = 'id';
        }
        
        $allowedDirections = ['asc', 'desc'];
        if (!in_array($sortDirection, $allowedDirections)) {
            $sortDirection = 'desc';
        }
        
        $query->orderBy($sortColumn, $sortDirection);

        // Per page
        $perPage = $request->get('per_page', 15);
        $allowedPerPage = [10, 15, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        // Get statistics (before pagination)
        $totalSchools = School::withTrashed()->count();
        $activeSchools = School::withTrashed()->where('status', SchoolStatus::Active->value)->count();
        $inactiveSchools = School::withTrashed()->where('status', SchoolStatus::Inactive->value)->count();
        $suspendedSchools = School::withTrashed()->where('status', SchoolStatus::Suspended->value)->count();

        // Paginate results
        $schools = $query->paginate($perPage);

        // Export functionality
        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportToCsv($query->get());
        }

        return view('admin.schools.index', compact('schools', 'totalSchools', 'activeSchools', 'inactiveSchools', 'suspendedSchools'));
    }

    private function exportToCsv($schools)
    {
        $filename = 'schools_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($schools) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, ['ID', 'Name', 'Code', 'Subdomain', 'Email', 'Phone', 'Status', 'City', 'State', 'Country', 'Created At']);
            
            // CSV Data
            foreach ($schools as $school) {
                fputcsv($file, [
                    $school->id,
                    $school->name,
                    $school->code,
                    $school->subdomain,
                    $school->email,
                    $school->phone,
                    $school->status,
                    $school->city->name ?? '',
                    $school->state->name ?? '',
                    $school->country->name ?? '',
                    $school->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        return view('admin.schools.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // School Details
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:schools,code',
            'subdomain' => 'required|string|max:255|unique:schools,subdomain',
            'domain' => 'nullable|string|max:255|unique:schools,domain',
            'email' => 'required|email|max:255|unique:schools,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city_id' => 'nullable|integer|exists:cities,id',
            'state_id' => 'nullable|integer|exists:states,id',
            'country_id' => 'nullable|integer|exists:countries,id',
            'pincode' => 'nullable|string|max:10',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => ['required', 'integer', Rule::enum(SchoolStatus::class)],
            'subscription_start_date' => 'nullable|date',
            'subscription_end_date' => 'nullable|date|after:subscription_start_date',

            // Admin Details
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            \DB::beginTransaction();

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $validated['logo'] = $request->file('logo')->store('schools/logos', 'public');
            }

            // Create School
            $schoolData = collect($validated)->except(['admin_name', 'admin_email', 'admin_password', 'admin_password_confirmation'])->toArray();
            
            // Status is already an integer from validation
            $schoolData['status'] = $validated['status'];

            $school = School::create($schoolData);

            // Seed Master Data for the new school
            (new \Database\Seeders\MasterDataSeeder())->run($school);

            // Create School Admin User
            $schoolAdminRole = \App\Models\Role::where('slug', \App\Models\Role::SCHOOL_ADMIN)->firstOrFail();

            \App\Models\User::create([
                'school_id' => $school->id,
                'role_id' => $schoolAdminRole->id,
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => \Hash::make($validated['admin_password']),
                'status' => \App\Models\User::STATUS_ACTIVE,
            ]);

            \DB::commit();

            return redirect()->route('admin.schools.index')
                ->with('success', 'School and Administrator account created successfully.');

        } catch (\Exception $e) {
            \DB::rollBack();
            // If logo was uploaded but transaction failed, delete the file
            if (isset($validated['logo'])) {
                Storage::disk('public')->delete($validated['logo']);
            }
            
            return back()->withInput()->with('error', 'Failed to create school: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $school = School::withTrashed()->findOrFail($id);
        return view('admin.schools.show', compact('school'));
    }

    public function edit($id)
    {
        $school = School::withTrashed()->findOrFail($id);
        return view('admin.schools.edit', compact('school'));
    }

    public function update(Request $request, $id)
    {
        $school = School::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:schools,code,' . $id,
            'subdomain' => 'required|string|max:255|unique:schools,subdomain,' . $id,
            'domain' => 'nullable|string|max:255|unique:schools,domain,' . $id,
            'email' => 'required|email|max:255|unique:schools,email,' . $id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'city_id' => 'nullable|integer|exists:cities,id',
            'state_id' => 'nullable|integer|exists:states,id',
            'country_id' => 'nullable|integer|exists:countries,id',
            'pincode' => 'nullable|string|max:10',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => ['required', 'integer', Rule::enum(SchoolStatus::class)],
            'subscription_start_date' => 'nullable|date',
            'subscription_end_date' => 'nullable|date|after:subscription_start_date',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($school->logo) {
                Storage::disk('public')->delete($school->logo);
            }
            $validated['logo'] = $request->file('logo')->store('schools/logos', 'public');
        }

        // Status is already an integer from validation
        // No mapping needed

        $school->update($validated);

        return redirect()->route('admin.schools.index')
            ->with('success', 'School updated successfully.');
    }

    public function destroy($id)
    {
        $school = School::findOrFail($id);
        $school->delete();

        return redirect()->route('admin.schools.index')
            ->with('success', 'School deleted successfully.');
    }
}
