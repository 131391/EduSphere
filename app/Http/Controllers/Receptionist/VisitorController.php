<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\Visitor;
use App\Enums\VisitorPriority;
use App\Enums\VisitorMode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VisitorController extends TenantController
{
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $query = Visitor::where('school_id', $schoolId);

        // Filter by today if requested
        if ($request->has('today')) {
            $query->whereDate('created_at', Carbon::today());
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('visitor_no', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $visitors = $query->paginate($perPage)->withQueryString();

        // Statistics for the page
        $stats = [
            'total' => Visitor::where('school_id', $schoolId)->count(),
            'online' => Visitor::where('school_id', $schoolId)->where('meeting_type', 'online')->count(),
            'offline' => Visitor::where('school_id', $schoolId)->where('meeting_type', 'offline')->count(),
            'office' => Visitor::where('school_id', $schoolId)->where('meeting_type', 'office')->count(),
            'cancelled' => Visitor::where('school_id', $schoolId)->where('status', 'cancelled')->count(),
        ];

        // Get priority options from enum
        $priorities = VisitorPriority::cases();
        
        // Get meeting type options from enum
        $meetingTypes = VisitorMode::cases();

        return view('receptionist.visitors.index', compact('visitors', 'stats', 'priorities', 'meetingTypes'));
    }

    public function show(Visitor $visitor)
    {
        $this->authorizeAccess($visitor);
        
        return view('receptionist.visitors.show', compact('visitor'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'visitor_type' => 'required|string',
            'visit_purpose' => 'required|string',
            'meeting_purpose' => 'nullable|string',
            'meeting_with' => 'required|string',
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

        Visitor::create($validated);

        return redirect()->route('receptionist.visitors.index')->with('success', 'Visitor added successfully.');
    }

    public function update(Request $request, Visitor $visitor)
    {
        $this->authorizeAccess($visitor);
        
        // Store visitor ID for redirect back on validation error
        $request->merge(['visitor_id' => $visitor->id]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'visitor_type' => 'required|string',
            'visit_purpose' => 'required|string',
            'meeting_purpose' => 'nullable|string',
            'meeting_with' => 'required|string',
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

        return redirect()->route('receptionist.visitors.index')->with('success', 'Visitor updated successfully.');
    }

    public function destroy(Visitor $visitor)
    {
        $this->authorizeAccess($visitor);

        // Delete associated files
        if ($visitor->visitor_photo) {
            Storage::disk('public')->delete($visitor->visitor_photo);
        }
        if ($visitor->id_proof) {
            Storage::disk('public')->delete($visitor->id_proof);
        }

        $visitor->delete();

        return redirect()->route('receptionist.visitors.index')->with('success', 'Visitor deleted successfully.');
    }

    public function checkIn(Visitor $visitor)
    {
        $this->authorizeAccess($visitor);

        $visitor->update([
            'check_in' => now(),
            'status' => 'checked_in',
        ]);

        return back()->with('success', 'Visitor checked in successfully.');
    }

    public function checkOut(Visitor $visitor)
    {
        $this->authorizeAccess($visitor);

        $visitor->update([
            'check_out' => now(),
            'status' => 'completed',
        ]);

        return back()->with('success', 'Visitor checked out successfully.');
    }

    public function export()
    {
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
