<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function logo(Request $request)
    {
        $query = School::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        
        // Validate sort column to prevent SQL injection
        $allowedSortColumns = ['id', 'name', 'code', 'email', 'status', 'created_at'];
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

        // Paginate results
        $schools = $query->paginate($perPage);

        return view('admin.settings.logo', compact('schools'));
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'site_icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:512',
        ]);

        $school = \App\Models\School::findOrFail($request->school_id);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($school->logo) {
                Storage::disk('public')->delete($school->logo);
            }
            $school->logo = $request->file('logo')->store('schools/logos', 'public');
        }

        // Handle site icon upload
        if ($request->hasFile('site_icon')) {
            // Delete old site icon
            if ($school->site_icon) {
                Storage::disk('public')->delete($school->site_icon);
            }
            $school->site_icon = $request->file('site_icon')->store('schools/site-icons', 'public');
        }

        $school->save();

        return redirect()->route('admin.settings.logo')
            ->with('success', 'Logo updated successfully.');
    }

    public function deleteLogo(School $school)
    {
        if ($school->logo) {
            Storage::disk('public')->delete($school->logo);
            $school->logo = null;
        }
        
        if ($school->site_icon) {
            Storage::disk('public')->delete($school->site_icon);
            $school->site_icon = null;
        }
        
        $school->save();

        return redirect()->route('admin.settings.logo')
            ->with('success', 'Logo deleted successfully.');
    }

    public function basicInfo()
    {
        return view('admin.settings.basic-info');
    }

    public function registrationFee()
    {
        return view('admin.settings.registration-fee');
    }

    public function admissionFee()
    {
        return view('admin.settings.admission-fee');
    }

    public function receiptNote()
    {
        return view('admin.settings.receipt-note');
    }

    public function setSession()
    {
        return view('admin.settings.set-session');
    }

    public function lateReturnFine()
    {
        return view('admin.settings.late-return-fine');
    }

    public function admissionFeeApplicable()
    {
        return view('admin.settings.admission-fee-applicable');
    }

    public function admissionNews()
    {
        return view('admin.admission-news');
    }

    public function support()
    {
        return view('admin.support');
    }

    public function changePassword()
    {
        return view('admin.change-password');
    }

    public function profile()
    {
        return view('admin.profile');
    }
}

