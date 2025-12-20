<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\AdmissionNews;
use Illuminate\Http\Request;

class AdmissionNewsController extends TenantController
{
    public function index()
    {
        $news = AdmissionNews::where('school_id', $this->getSchoolId())
            ->orderBy('publish_date', 'desc')
            ->paginate(10);
            
        return view('school.admission-news.index', compact('news'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'publish_date' => 'required|date',
        ]);

        AdmissionNews::create([
            'school_id' => $this->getSchoolId(),
            'title' => $request->title,
            'content' => $request->content,
            'publish_date' => $request->publish_date,
            'is_active' => true,
        ]);

        return back()->with('success', 'Admission news created successfully.');
    }

    public function update(Request $request, $id)
    {
        $news = AdmissionNews::where('school_id', $this->getSchoolId())->findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'publish_date' => 'required|date',
        ]);

        $news->update([
            'title' => $request->title,
            'content' => $request->content,
            'publish_date' => $request->publish_date,
        ]);

        return back()->with('success', 'Admission news updated successfully.');
    }

    public function destroy($id)
    {
        $news = AdmissionNews::where('school_id', $this->getSchoolId())->findOrFail($id);
        $news->delete();

        return back()->with('success', 'Admission news deleted successfully.');
    }
}
