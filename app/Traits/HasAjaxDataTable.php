<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

/**
 * Trait HasAjaxDataTable
 *
 * Provides standard AJAX response formatting for data table listings.
 * Use this trait in any controller that serves a listing page with
 * search, filter, sort, pagination, and export via AJAX POST.
 *
 * Usage in controller:
 *   use HasAjaxDataTable;
 *
 *   public function index(Request $request)
 *   {
 *       $query = Model::query();
 *       // ... apply search, filters, sorting ...
 *       $paginator = $query->paginate($perPage);
 *
 *       if ($request->ajax()) {
 *           return $this->ajaxResponse($paginator, $stats, $transformer);
 *       }
 *
 *       return view('...');
 *   }
 */
trait HasAjaxDataTable
{
    /**
     * Build a standard JSON response for AJAX data table requests.
     *
     * @param LengthAwarePaginator $paginator  The paginated query result
     * @param array                $stats      Associative array of statistics for cards
     * @param callable|null        $transformer  Optional closure to transform each row
     * @return JsonResponse
     */
    protected function ajaxResponse(LengthAwarePaginator $paginator, array $stats = [], ?callable $transformer = null): JsonResponse
    {
        $items = $paginator->getCollection();

        if ($transformer) {
            $items = $items->map($transformer);
        }

        return response()->json([
            'data' => $items->values(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'stats' => $stats,
        ]);
    }
}
