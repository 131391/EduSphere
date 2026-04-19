<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\AdmissionNews;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class AdmissionNewsController extends TenantController
{
    use HasAjaxDataTable;

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'content' => $item->content,
                'publish_date' => $item->publish_date?->format('Y-m-d'),
                'publish_date_formatted' => $item->publish_date?->format('M d, Y'),
                'is_active' => (bool)$item->is_active,
                'created_at' => $item->created_at?->format('M d, Y'),
            ];
        };

        $query = AdmissionNews::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        $sort = $request->input('sort', 'publish_date');
        $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        if (\in_array($sort, ['id', 'title', 'publish_date', 'created_at'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('publish_date', 'desc');
        }

        $stats = [
            'total' => AdmissionNews::where('school_id', $schoolId)->count(),
            'active' => AdmissionNews::where('school_id', $schoolId)->where('is_active', true)->count(),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.admission-news.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'publish_date' => 'required|date',
        ]);

        $news = AdmissionNews::create([
            'school_id' => $this->getSchoolId(),
            'title' => $request->title,
            'content' => $request->input('content'),
            'publish_date' => $request->input('publish_date'),
            'is_active' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admission news created successfully!',
                'data' => $news
            ]);
        }

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
            'content' => $request->input('content'),
            'publish_date' => $request->input('publish_date'),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admission news updated successfully!',
                'data' => $news
            ]);
        }

        return back()->with('success', 'Admission news updated successfully.');
    }

    public function destroy($id)
    {
        $news = AdmissionNews::where('school_id', $this->getSchoolId())->findOrFail($id);
        $news->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Admission news deleted successfully!'
            ]);
        }

        return back()->with('success', 'Admission news deleted successfully.');
    }
}
