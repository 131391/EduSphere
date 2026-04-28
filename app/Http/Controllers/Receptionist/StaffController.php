<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\Staff;
use App\Models\User;
use App\Models\Role;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Qualification;
use App\Traits\HasAjaxDataTable;
use App\Enums\Gender;
use App\Enums\StaffPost;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StaffController extends TenantController
{
    use HasAjaxDataTable;

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($staff) {
            $post = $staff->post;
            
            return [
                'id'                           => $staff->id,
                'name'                         => $staff->name,
                'initials'                     => collect(explode(' ', $staff->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join(''),
                'post_label'                   => $post?->label() ?? 'N/A',
                'post_value'                   => $post?->value,
                'post_color'                   => $post?->color() ?? 'slate',
                'class_name'                   => $staff->class?->name ?? 'N/A',
                'section_name'                 => $staff->section?->name ?? 'N/A',
                'mobile'                       => $staff->mobile,
                'email'                        => $staff->email ?? '',
                'gender_label'                 => $staff->gender?->label() ?? 'N/A',
                'joining_date'                 => $staff->joining_date?->format('d M, Y') ?? 'N/A',
                'current_salary'               => $staff->current_salary,
                'aadhaar_no'                    => $staff->aadhaar_no ?? '',
                'staff_image'                  => $staff->staff_image ? asset('storage/' . $staff->staff_image) : null,
                'created_at_label'             => $staff->created_at->format('d M, Y'),
                
                // Payload for edit form
                'post'                         => $post?->value,
                'class_id'                     => $staff->class_id,
                'section_id'                   => $staff->section_id,
                'gender'                       => $staff->gender?->value,
                'total_experience'             => $staff->total_experience,
                'previous_school_salary'       => $staff->previous_school_salary,
                'country_id'                   => $staff->country_id,
                'state_id'                     => $staff->state_id,
                'city_id'                      => $staff->city_id,
                'zip_code'                     => $staff->zip_code ?? '',
                'address'                      => $staff->address ?? '',
                'aadhaar_card'                  => $staff->aadhaar_card ?? '',
                'aadhaar_card_preview'          => $staff->aadhaar_card ? asset('storage/' . $staff->aadhaar_card) : '',
                'staff_image_preview'          => $staff->staff_image ? asset('storage/' . $staff->staff_image) : '',
                'joining_date_raw'             => $staff->joining_date?->format('Y-m-d') ?? '',
                'higher_qualification_id'      => $staff->higher_qualification_id,
                'previous_school_company_name' => $staff->previous_school_company_name ?? '',
            ];
        };

        $query = Staff::where('school_id', $schoolId)
            ->with(['class', 'section', 'higherQualification']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('aadhaar_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('post')) {
            $query->where('post', $request->post);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $sort      = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $allowed   = ['name', 'mobile', 'joining_date', 'current_salary', 'created_at'];
        if (in_array($sort, $allowed)) {
            $query->orderBy($sort, $direction);
        }

        $stats = [
            'total'       => Staff::where('school_id', $schoolId)->count(),
            'teaching'    => Staff::where('school_id', $schoolId)->where('post', StaffPost::Teacher->value)->count(),
            'non_teaching'=> Staff::where('school_id', $schoolId)->where('post', '!=', StaffPost::Teacher->value)->count(),
            'recent'      => Staff::where('school_id', $schoolId)->where('joining_date', '>=', now()->subDays(30))->count(),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        if ($request->has('export')) {
            return $this->exportToCsv($query);
        }

        $initialData = $this->getHydrationData($query, $transformer, ['stats' => $stats]);

        $classes        = ClassModel::where('school_id', $schoolId)->orderBy('order')->get();
        $qualifications = Qualification::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $countries      = \Nnjeim\World\Models\Country::orderBy('name')->get(['id', 'name']);
        $staffPosts     = StaffPost::cases();

        return view('receptionist.staff.index', compact(
            'initialData', 'stats', 'classes', 'qualifications', 'countries', 'staffPosts'
        ));
    }

    private function exportToCsv($query)
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="staff_export_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Post', 'Mobile', 'Email', 'Class', 'Section', 'Joining Date', 'Salary']);
            $query->orderBy('created_at', 'desc')->cursor()->each(function ($s) use ($file) {
                fputcsv($file, [
                    $s->name, $s->post?->label(), $s->mobile, $s->email,
                    $s->class?->name, $s->section?->name,
                    $s->joining_date?->format('Y-m-d'), $s->current_salary,
                ]);
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $request)
    {
        $validated = $this->validateStaff($request);

        if ($request->hasFile('aadhaar_card')) {
            $validated['aadhaar_card'] = $request->file('aadhaar_card')->store('staff/aadhaar', 'public');
        }
        if ($request->hasFile('staff_image')) {
            $validated['staff_image'] = $request->file('staff_image')->store('staff/images', 'public');
        }

        try {
            DB::transaction(function () use ($validated) {
                $role = $this->resolveRoleForPost(StaffPost::from($validated['post']));

                $user = User::create([
                    'school_id'           => $this->getSchoolId(),
                    'name'                => $validated['name'],
                    'email'               => $validated['email'],
                    'password'            => Hash::make($validated['password']),
                    'role_id'             => $role->id,
                    'phone'               => $validated['mobile'] ?? null,
                    'status'              => UserStatus::Active,
                    'must_change_password' => true,
                ]);

                unset($validated['password']);
                $validated['school_id'] = $this->getSchoolId();
                $validated['user_id']   = $user->id;

                Staff::create($validated);
            });

            return response()->json(['success' => true, 'message' => 'Staff registered successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create staff: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Staff $staff)
    {
        $this->authorizeTenant($staff);
        $validated = $this->validateStaff($request, $staff);

        if ($request->hasFile('aadhaar_card')) {
            if ($staff->aadhaar_card) Storage::disk('public')->delete($staff->aadhaar_card);
            $validated['aadhaar_card'] = $request->file('aadhaar_card')->store('staff/aadhaar', 'public');
        }
        if ($request->hasFile('staff_image')) {
            if ($staff->staff_image) Storage::disk('public')->delete($staff->staff_image);
            $validated['staff_image'] = $request->file('staff_image')->store('staff/images', 'public');
        }

        try {
            DB::transaction(function () use ($staff, $validated, $request) {
                // Sync name/email/role/phone on the linked User
                if ($staff->user_id) {
                    $role = $this->resolveRoleForPost(StaffPost::from($validated['post']));
                    $userUpdate = [
                        'name'    => $validated['name'],
                        'email'   => $validated['email'],
                        'phone'   => $validated['mobile'] ?? null,
                        'role_id' => $role->id,
                    ];
                    if ($request->filled('password')) {
                        $userUpdate['password'] = Hash::make($request->password);
                    }
                    $staff->user->update($userUpdate);
                }

                unset($validated['password']);
                $staff->update($validated);
            });

            return response()->json(['success' => true, 'message' => 'Staff record updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update staff: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, Staff $staff)
    {
        $this->authorizeTenant($staff);

        if ($staff->aadhaar_card) Storage::disk('public')->delete($staff->aadhaar_card);
        if ($staff->staff_image) Storage::disk('public')->delete($staff->staff_image);

        DB::transaction(function () use ($staff) {
            $staff->delete();
            if ($staff->user_id) {
                $staff->user?->delete();
            }
        });

        return response()->json(['success' => true, 'message' => 'Staff record deleted successfully.']);
    }

    public function getSections(Request $request, $classId)
    {
        $sections = Section::where('school_id', $this->getSchoolId())
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['sections' => $sections]);
    }

    private function resolveRoleForPost(StaffPost $post): Role
    {
        $slug = match ($post) {
            StaffPost::Teacher    => Role::TEACHER,
            StaffPost::Principal,
            StaffPost::Assistant,
            StaffPost::Counselor  => Role::SCHOOL_ADMIN,
            default               => Role::RECEPTIONIST,
        };

        return Role::where('slug', $slug)->firstOrFail();
    }

    private function validateStaff(Request $request, ?Staff $staff = null): array
    {
        return $request->validate([
            'post'                         => ['required', 'integer', Rule::enum(StaffPost::class)],
            'class_id'                     => ['nullable', Rule::exists('classes', 'id')->where('school_id', $this->getSchoolId())],
            'section_id'                   => ['nullable', Rule::exists('sections', 'id')->where('school_id', $this->getSchoolId())],
            'name'                         => 'required|string|max:255',
            'mobile'                       => 'required|string|max:20',
            'email'                        => ['required', 'email', 'max:255', Rule::unique('staff', 'email')->where('school_id', $this->getSchoolId())->ignore($staff?->id)],
            'password'                     => $staff ? 'nullable|string|min:8' : 'required|string|min:8',
            'gender'                       => ['required', 'integer', Rule::enum(Gender::class)],
            'total_experience'             => 'nullable|integer|min:0',
            'previous_school_salary'       => 'nullable|numeric|min:0',
            'current_salary'               => 'required|numeric|min:0',
            'country_id'                   => 'nullable|exists:countries,id',
            'state_id'                     => 'nullable|exists:states,id',
            'city_id'                      => 'nullable|exists:cities,id',
            'zip_code'                     => 'nullable|string|max:20',
            'address'                      => 'nullable|string',
            'aadhaar_no'                    => 'nullable|string|max:20',
            'aadhaar_card'                  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'staff_image'                  => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'joining_date'                 => 'required|date',
            'higher_qualification_id'      => ['nullable', Rule::exists('qualifications', 'id')->where('school_id', $this->getSchoolId())],
            'previous_school_company_name' => 'nullable|string|max:255',
        ]);
    }
}
