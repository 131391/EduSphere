<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Exports\DailyCollectionExport;
use App\Exports\DefaultersExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FeeReportController extends TenantController
{
    public function index()
    {
        $this->ensureSchoolActive();
        // Here we could add authorization checks for viewing reports

        return view('school.reports.fees');
    }

    public function dailyCollection(Request $request)
    {
        $this->ensureSchoolActive();

        $date = $request->input('date', Carbon::today()->toDateString());
        $filename = "Daily_Collection_{$date}.xlsx";

        return Excel::download(new DailyCollectionExport($this->getSchoolId(), $date), $filename);
    }

    public function defaulters(Request $request)
    {
        $this->ensureSchoolActive();

        $date = $request->input('date', Carbon::today()->toDateString());
        $filename = "Defaulters_AsOf_{$date}.xlsx";

        return Excel::download(new DefaultersExport($this->getSchoolId(), $date), $filename);
    }
}
