<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $school = auth()->user()->school;
        
        $query = Visitor::where('school_id', $school->id);

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
            'total' => Visitor::where('school_id', $school->id)->count(),
            'online' => Visitor::where('school_id', $school->id)->where('meeting_type', 'online')->count(),
            'offline' => Visitor::where('school_id', $school->id)->where('meeting_type', 'offline')->count(),
            'office' => Visitor::where('school_id', $school->id)->where('meeting_type', 'office')->count(),
            'cancelled' => Visitor::where('school_id', $school->id)->where('status', 'cancelled')->count(),
        ];

        return view('receptionist.visitors.index', compact('visitors', 'stats'));
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
            'priority' => 'required|in:Low,Medium,High,Urgent,low,medium,high,urgent',
            'no_of_guests' => 'nullable|integer|min:1',
            'meeting_type' => 'required|in:online,offline,office',
            'source' => 'nullable|string',
            'meeting_scheduled' => 'nullable|date',
            'visitor_photo' => 'nullable|image|max:2048',
            'id_proof' => 'nullable|file|max:2048',
        ]);

        $school = auth()->user()->school;
        $validated['school_id'] = $school->id;
        $validated['visitor_no'] = Visitor::generateVisitorNo($school->id);

        // Handle file uploads
        if ($request->hasFile('visitor_photo')) {
            $validated['visitor_photo'] = $request->file('visitor_photo')->store('visitors/photos', 'public');
        }

        if ($request->hasFile('id_proof')) {
            $validated['id_proof'] = $request->file('id_proof')->store('visitors/proofs', 'public');
        }

        Visitor::create($validated);

        return redirect()->route('receptionist.visitors.index')->with('success', 'Visitor added successfully.');
    }

    public function update(Request $request, Visitor $visitor)
    {
        $this->authorizeAccess($visitor);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'visitor_type' => 'required|string',
            'visit_purpose' => 'required|string',
            'meeting_purpose' => 'nullable|string',
            'meeting_with' => 'required|string',
            'priority' => 'required|in:Low,Medium,High,Urgent,low,medium,high,urgent',
            'no_of_guests' => 'nullable|integer|min:1',
            'meeting_type' => 'required|in:online,offline,office',
            'source' => 'nullable|string',
            'meeting_scheduled' => 'nullable|date',
            'status' => 'required|in:scheduled,checked_in,completed,cancelled',
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
        if ($visitor->school_id !== auth()->user()->school_id) {
            abort(403);
        }
    }
}
