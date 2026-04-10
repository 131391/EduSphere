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
        $query = School::withTrashed()->with(['city', 'state', 'country']);

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
        $sortColumn = $request->input('sort', 'id');
        $sortDirection = $request->input('direction', 'desc');
        
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
        $perPage = $request->input('per_page', 15);
        $allowedPerPage = [10, 15, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        // Get statistics (before pagination)
        $totalSchools = School::withTrashed()->count();
        $activeSchools = School::withTrashed()->where('status', SchoolStatus::Active->value)->count();
        $inactiveSchools = School::withTrashed()->where('status', SchoolStatus::Inactive->value)->count();
        $suspendedSchools = School::withTrashed()->where('status', SchoolStatus::Suspended->value)->count();

        // Export functionality (BEFORE pagination so we export full result set)
        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportToCsv($query->get());
        }

        // Paginate results
        $schools = $query->paginate($perPage);

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

    public function store(\App\Http\Requests\Admin\StoreSchoolRequest $request)
    {
        $validated = $request->validated();

        try {
            \DB::beginTransaction();

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $validated['logo'] = $request->file('logo')->store('schools/logos', 'public');
            }

            // Create School
            $schoolData = collect($validated)->except(['admin_name', 'admin_email', 'admin_password', 'admin_password_confirmation'])->toArray();
            
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
        
        // Fetch the primary administrator
        $admin = \App\Models\User::where('school_id', $school->id)
            ->whereHas('role', function($q) {
                $q->where('slug', \App\Models\Role::SCHOOL_ADMIN);
            })->first();

        return view('admin.schools.show', compact('school', 'admin'));
    }

    public function edit($id)
    {
        $school = School::withTrashed()->findOrFail($id);
        
        // Fetch the primary administrator
        $admin = \App\Models\User::where('school_id', $school->id)
            ->whereHas('role', function($q) {
                $q->where('slug', \App\Models\Role::SCHOOL_ADMIN);
            })->first();

        return view('admin.schools.edit', compact('school', 'admin'));
    }

    public function update(\App\Http\Requests\Admin\UpdateSchoolRequest $request, $id)
    {
        $school = School::withTrashed()->findOrFail($id);
        $validated = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($school->logo) {
                Storage::disk('public')->delete($school->logo);
            }
            $validated['logo'] = $request->file('logo')->store('schools/logos', 'public');
        }

        \DB::beginTransaction();
        try {
            $school->update(collect($validated)->except(['admin_id', 'admin_name', 'admin_email', 'admin_password', 'admin_password_confirmation'])->toArray());

            // Update Admin User if admin_id is provided
            if ($request->filled('admin_id')) {
                $admin = \App\Models\User::findOrFail($request->admin_id);
                
                $adminData = [];
                if ($request->filled('admin_name')) $adminData['name'] = $request->admin_name;
                if ($request->filled('admin_email')) {
                    $adminData['email'] = $request->admin_email;
                }
                if ($request->filled('admin_password')) {
                    $adminData['password'] = \Hash::make($request->admin_password);
                }

                if (!empty($adminData)) {
                    $admin->update($adminData);
                }
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update school: ' . $e->getMessage());
        }

        return redirect()->route('admin.schools.index')
            ->with('success', 'School updated successfully.');
    }

    /**
     * Soft-delete a school
     */
    public function destroy($id)
    {
        $school = School::findOrFail($id);
        $school->delete();

        return redirect()->route('admin.schools.index')
            ->with('success', 'School deleted successfully.');
    }

    /**
     * Restore a soft-deleted school
     */
    public function restore($id)
    {
        $school = School::withTrashed()->findOrFail($id);

        if (!$school->trashed()) {
            return back()->with('error', 'School is not deleted.');
        }

        $school->restore();

        return redirect()->route('admin.schools.index')
            ->with('success', 'School restored successfully.');
    }

    /**
     * Permanently delete a school
     */
    public function forceDelete($id)
    {
        $school = School::withTrashed()->findOrFail($id);
        $school->forceDelete();

        return redirect()->route('admin.schools.index')
            ->with('success', 'School permanently deleted.');
    }

    /**
     * Show feature flags form for a school
     */
    public function features($id)
    {
        $school = School::findOrFail($id);
        $features = $school->features ?? [];

        $availableFeatures = [
            'student_management' => 'Student Management',
            'staff_management' => 'Staff & HR Management',
            'finance_accounting' => 'Finance & Accounting',
            'exam_management' => 'Examination Management',
            'attendance_tracking' => 'Attendance Tracking',
            'library_system' => 'Library Management',
            'transport_fleet' => 'Transport & Fleet',
            'hostel_management' => 'Hostel Management',
            'inventory_management' => 'Inventory Management',
            'communication_hub' => 'Communication Hub (SMS/Email)',
            'online_admissions' => 'Online Admissions',
            'parent_portal' => 'Parent Portal',
            'student_portal' => 'Student Portal',
        ];

        $premiumFeatures = [
            'biometric_integration' => 'Biometric Integration',
            'gps_tracking' => 'GPS Live Tracking',
            'mobile_app' => 'White-label Mobile App',
            'payment_gateway' => 'Online Payment Gateway',
            'virtual_classrooms' => 'Virtual Classrooms (Zoom/Meet)',
        ];

        return view('admin.schools.features', compact('school', 'features', 'availableFeatures', 'premiumFeatures'));
    }

    /**
     * Update feature flags for a school
     */
    public function updateFeatures(Request $request, $id)
    {
        $school = School::findOrFail($id);

        $school->update([
            'features' => $request->input('features', []),
        ]);

        return redirect()->route('admin.schools.index')
            ->with('success', 'Feature flags updated for ' . $school->name . '.');
    }
}

