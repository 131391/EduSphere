<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolSettingsController extends TenantController
{
    public function index()
    {
        return redirect()->route('school.settings.basic-info');
    }

    public function basicInfo()
    {
        $school = $this->getSchool();
        return view('school.settings.basic-info', compact('school'));
    }

    public function updateBasicInfo(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
        ]);

        $this->getSchool()->update($request->only([
            'name', 'email', 'phone', 'address', 'city', 'state', 'country', 'pincode', 'website'
        ]));

        return back()->with('success', 'Basic information updated successfully.');
    }

    public function logo()
    {
        $school = $this->getSchool();
        return view('school.settings.logo', compact('school'));
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $school = $this->getSchool();

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($school->logo) {
                Storage::disk('public')->delete($school->logo);
            }

            $path = $request->file('logo')->store('school-logos', 'public');
            $school->update(['logo' => $path]);
        }

        return back()->with('success', 'Logo updated successfully.');
    }

    public function generalSettings()
    {
        $school = $this->getSchool();
        $settings = $school->settings ?? [];
        return view('school.settings.general', compact('school', 'settings'));
    }

    public function updateGeneralSettings(Request $request)
    {
        $request->validate([
            'registration_fee' => 'nullable|numeric|min:0',
            'admission_fee' => 'nullable|numeric|min:0',
            'receipt_note' => 'nullable|string|max:1000',
            'late_return_library_book_fine' => 'nullable|numeric|min:0',
            'admission_fee_applicable' => 'nullable|boolean',
        ]);

        $school = $this->getSchool();
        $settings = $school->settings ?? [];

        $settings['registration_fee'] = $request->input('registration_fee');
        $settings['admission_fee'] = $request->input('admission_fee');
        $settings['receipt_note'] = $request->input('receipt_note');
        $settings['late_return_library_book_fine'] = $request->input('late_return_library_book_fine');
        $settings['admission_fee_applicable'] = $request->boolean('admission_fee_applicable');

        $school->update(['settings' => $settings]);

        return back()->with('success', 'Settings updated successfully.');
    }

    public function session()
    {
        $school = $this->getSchool();
        $academicYears = AcademicYear::where('school_id', $school->id)->orderBy('start_date', 'desc')->get();
        $currentSessionId = $school->settings['current_session_id'] ?? null;
        
        return view('school.settings.session', compact('school', 'academicYears', 'currentSessionId'));
    }

    public function updateSession(Request $request)
    {
        $request->validate([
            'current_session_id' => 'required|exists:academic_years,id',
        ]);

        $school = $this->getSchool();
        $settings = $school->settings ?? [];
        
        $settings['current_session_id'] = $request->input('current_session_id');
        
        $school->update(['settings' => $settings]);

        return back()->with('success', 'Session updated successfully.');
    }
    public function receiptNote()
    {
        $school = $this->getSchool();
        $settings = $school->settings ?? [];
        return view('school.settings.receipt-note', compact('school', 'settings'));
    }

    public function updateReceiptNote(Request $request)
    {
        $request->validate([
            'registration_receipt_note' => 'nullable|string|max:1000',
            'admission_receipt_note' => 'nullable|string|max:1000',
            'fee_receipt_note' => 'nullable|string|max:1000',
        ]);

        $school = $this->getSchool();
        $settings = $school->settings ?? [];

        $settings['registration_receipt_note'] = $request->input('registration_receipt_note');
        $settings['admission_receipt_note'] = $request->input('admission_receipt_note');
        $settings['fee_receipt_note'] = $request->input('fee_receipt_note');

        $school->update(['settings' => $settings]);

        return back()->with('success', 'Receipt notes updated successfully.');
    }
}
