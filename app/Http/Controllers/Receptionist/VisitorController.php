<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\Visitor;
use App\Enums\VisitorPriority;
use App\Enums\VisitorMode;
use App\Enums\VisitorStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Enums\VisitPurpose;
use App\Enums\VisitorType;
use App\Enums\MeetingWith;

use App\Traits\HasAjaxDataTable;

class VisitorController extends TenantController
{
    use HasAjaxDataTable;

    public function index(Request $request)
    {
        $this->authorize('receptionist:operate');

        $schoolId = $this->getSchoolId();

        // 1. Row Transformer (Crucial for Gold Standard UI consistency)
        $transformer = function ($visitor) {
            $status = $visitor->status;
            $priority = $visitor->priority;

            // Map Tailwind-style config for badges
            $statusConfig = [
                'bg' => 'bg-' . $status?->color() . '-50',
                'text' => 'text-' . $status?->color() . '-700',
                'border' => 'border-' . $status?->color() . '-100',
                'icon' => match ($status) {
                    VisitorStatus::Scheduled => 'fa-calendar',
                    VisitorStatus::CheckedIn => 'fa-sign-in-alt',
                    VisitorStatus::Completed => 'fa-check-double',
                    VisitorStatus::Cancelled => 'fa-times-circle',
                    default => 'fa-user-clock'
                }
            ];

            return [
                'id' => $visitor->id,
                'visitor_no' => $visitor->visitor_no,
                'name' => $visitor->name,
                'initials' => collect(explode(' ', $visitor->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join(''),
                'mobile' => $visitor->mobile,
                'email' => $visitor->email,
                'meeting_with' => $visitor->meeting_with?->label() ?? 'N/A',
                'visit_purpose' => $visitor->visit_purpose?->label() ?? 'N/A',
                'source' => $visitor->source ?? 'N/A',
                'meeting_type' => $visitor->meeting_type?->label() ?? 'N/A',
                'priority_label' => $priority?->label() ?? 'Medium',
                'priority_color' => $priority?->color() ?? 'blue',
                'status_label' => $status?->label() ?? 'N/A',
                'status_config' => $statusConfig,
                'check_in' => $visitor->check_in ? $visitor->check_in->format('d M, h:i A') : '--',
                'check_out' => $visitor->check_out ? $visitor->check_out->format('d M, h:i A') : '--',
                'scheduled_at' => $visitor->meeting_scheduled ? $visitor->meeting_scheduled->format('d M, h:i A') : 'N/A',
                'can_check_in' => $status === VisitorStatus::Scheduled,
                'can_check_out' => $status === VisitorStatus::CheckedIn,
            ];
        };

        // 2. Build Query
        $query = Visitor::where('school_id', $schoolId);

        // Apply filters
        if ($request->filled('today')) {
            $query->whereDate('created_at', Carbon::today());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('visitor_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('meeting_type')) {
            $query->where('meeting_type', $request->meeting_type);
        }

        // 3. Handle AJAX or CSV Export vs Blade Hydration
        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer);
        }

        if ($request->has('export')) {
            return $this->exportToCsv($query);
        }

        // 4. Blade Hydration
        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => [
                'total' => Visitor::where('school_id', $schoolId)->count(),
                'online' => Visitor::where('school_id', $schoolId)->where('meeting_type', VisitorMode::Online)->count(),
                'offline' => Visitor::where('school_id', $schoolId)->where('meeting_type', VisitorMode::Offline)->count(),
                'office' => Visitor::where('school_id', $schoolId)->where('meeting_type', VisitorMode::Office)->count(),
                'cancelled' => Visitor::where('school_id', $schoolId)->where('status', VisitorStatus::Cancelled)->count(),
            ]
        ]);

        $priorities = VisitorPriority::cases();
        $meetingTypes = VisitorMode::cases();
        $visitPurposes = VisitPurpose::cases();
        $visitorTypes = VisitorType::cases();
        $meetingWithCases = MeetingWith::cases();

        return view('receptionist.visitors.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
            'priorities' => $priorities,
            'meetingTypes' => $meetingTypes,
            'visitPurposes' => $visitPurposes,
            'visitorTypes' => $visitorTypes,
            'meetingWithCases' => $meetingWithCases,
        ]);
    }

    private function exportToCsv($query)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="visitors_export_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Visitor No', 'Name', 'Mobile', 'Email', 'Purpose', 'Meeting With', 'Status', 'Date']);

            $query->orderBy('created_at', 'desc')->cursor()->each(function ($visitor) use ($file) {
                fputcsv($file, [
                    $visitor->visitor_no,
                    $visitor->name,
                    $visitor->mobile,
                    $visitor->email,
                    $visitor->visit_purpose,
                    $visitor->meeting_with,
                    $visitor->status?->label() ?? 'N/A',
                    $visitor->created_at->format('Y-m-d H:i')
                ]);
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show(Visitor $visitor)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeAccess($visitor);

        return view('receptionist.visitors.show', compact('visitor'));
    }

    public function store(Request $request)
    {
        $this->authorize('receptionist:operate');

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'email' => 'nullable|email',
                'address' => 'nullable|string',
                'visitor_type' => ['required', 'string', Rule::enum(VisitorType::class)],
                'visit_purpose' => ['required', 'string', Rule::enum(VisitPurpose::class)],
                'meeting_purpose' => 'nullable|string',
                'meeting_with' => ['required', 'string', Rule::enum(MeetingWith::class)],
                'priority' => ['required', 'integer', Rule::enum(VisitorPriority::class)],
                'no_of_guests' => 'nullable|integer|min:1',
                'meeting_type' => ['required', 'integer', Rule::enum(VisitorMode::class)],
                'source' => 'nullable|string',
                'meeting_scheduled' => 'nullable|date',
                'visitor_photo' => 'nullable|image|max:2048',
                'id_proof' => 'nullable|file|max:2048',
            ]);

            $schoolId = $this->getSchoolId();
            $validated['school_id'] = $schoolId;
            $validated['visitor_no'] = Visitor::generateVisitorNo($schoolId);

            // Handle file uploads
            if ($request->hasFile('visitor_photo')) {
                $validated['visitor_photo'] = $request->file('visitor_photo')->store('visitors/photos', 'public');
            }

            if ($request->hasFile('id_proof')) {
                $validated['id_proof'] = $request->file('id_proof')->store('visitors/proofs', 'public');
            }

            // Convert priority to integer (form sends as string, enum needs int)
            $validated['priority'] = (int) $validated['priority'];

            // Convert meeting_type to integer (form sends as string, enum needs int)
            $validated['meeting_type'] = (int) $validated['meeting_type'];

            $visitor = Visitor::create($validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Visitor recorded successfully!',
                    'data' => $visitor
                ]);
            }

            return redirect()->route('receptionist.visitors.index')->with('success', 'Visitor added successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to record visitor: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to record visitor: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Visitor $visitor)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeAccess($visitor);

        try {
            // Store visitor ID for redirect back on validation error
            $request->merge(['visitor_id' => $visitor->id]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'mobile' => 'required|string|max:20',
                'email' => 'nullable|email',
                'address' => 'nullable|string',
                'visitor_type' => ['required', 'string', Rule::enum(VisitorType::class)],
                'visit_purpose' => ['required', 'string', Rule::enum(VisitPurpose::class)],
                'meeting_purpose' => 'nullable|string',
                'meeting_with' => ['required', 'string', Rule::enum(MeetingWith::class)],
                'priority' => ['required', 'integer', Rule::enum(VisitorPriority::class)],
                'no_of_guests' => 'nullable|integer|min:1',
                'meeting_type' => ['required', 'integer', Rule::enum(VisitorMode::class)],
                'source' => 'nullable|string',
                'meeting_scheduled' => 'nullable|date',
                'visitor_photo' => 'nullable|image|max:2048',
                'id_proof' => 'nullable|file|max:2048',
            ]);

            // Handle file uploads
            if ($request->hasFile('visitor_photo')) {
                if ($visitor->visitor_photo) {
                    Storage::disk('public')->delete($visitor->visitor_photo);
                }
                $validated['visitor_photo'] = $request->file('visitor_photo')->store('visitors/photos', 'public');
            }

            if ($request->hasFile('id_proof')) {
                if ($visitor->id_proof) {
                    Storage::disk('public')->delete($visitor->id_proof);
                }
                $validated['id_proof'] = $request->file('id_proof')->store('visitors/proofs', 'public');
            }

            // Convert priority to integer (form sends as string, enum needs int)
            $validated['priority'] = (int) $validated['priority'];

            // Convert meeting_type to integer (form sends as string, enum needs int)
            $validated['meeting_type'] = (int) $validated['meeting_type'];

            $visitor->update($validated);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Visitor updated successfully!',
                    'data' => $visitor
                ]);
            }

            return redirect()->route('receptionist.visitors.index')->with('success', 'Visitor updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update visitor: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to update visitor: ' . $e->getMessage());
        }
    }

    public function destroy(Visitor $visitor)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeAccess($visitor);

        try {
            // Delete associated files
            if ($visitor->visitor_photo) {
                Storage::disk('public')->delete($visitor->visitor_photo);
            }
            if ($visitor->id_proof) {
                Storage::disk('public')->delete($visitor->id_proof);
            }

            $visitor->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Visitor deleted successfully!'
                ]);
            }

            return redirect()->route('receptionist.visitors.index')->with('success', 'Visitor deleted successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete visitor: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('receptionist.visitors.index')->with('error', 'Failed to delete visitor: ' . $e->getMessage());
        }
    }

    public function checkIn(Visitor $visitor)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeAccess($visitor);

        try {
            $visitor->update([
                'check_in' => now(),
                'status' => 'checked_in',
            ]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Visitor checked in successfully!'
                ]);
            }

            return back()->with('success', 'Visitor checked in successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to check in: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to check in: ' . $e->getMessage());
        }
    }

    public function checkOut(Visitor $visitor)
    {
        $this->authorize('receptionist:operate');
        $this->authorizeAccess($visitor);

        try {
            $visitor->update([
                'check_out' => now(),
                'status' => 'completed',
            ]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Visitor checked out successfully!'
                ]);
            }

            return back()->with('success', 'Visitor checked out successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to check out: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to check out: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $this->authorize('receptionist:operate');

        // Excel export functionality - to be implemented with Laravel Excel
        return back()->with('info', 'Export functionality coming soon.');
    }

    protected function authorizeAccess(Visitor $visitor)
    {
        if ($visitor->school_id !== $this->getSchoolId()) {
            abort(403);
        }
    }

}
