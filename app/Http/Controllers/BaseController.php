<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

/**
 * Base Controller
 * 
 * Provides common functionality for all controllers
 */
abstract class BaseController extends Controller
{
    /**
     * Default pagination per page
     */
    protected int $perPage = 15;

    /**
     * Allowed per page values
     */
    protected array $allowedPerPage = [10, 15, 25, 50, 100];

    /**
     * Return success response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Operation successful',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return error response
     */
    protected function errorResponse(
        string $message = 'Operation failed',
        int $code = 400,
        mixed $errors = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Return paginated response
     */
    protected function paginatedResponse(
        $query,
        ?int $perPage = null,
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        $perPage = $this->validatePerPage($perPage);
        $data = $query->paginate($perPage);

        return $this->successResponse($data, $message);
    }

    /**
     * Validate and return per page value
     */
    protected function validatePerPage(?int $perPage = null): int
    {
        $perPage = $perPage ?? request('per_page', $this->perPage);
        
        if (!in_array($perPage, $this->allowedPerPage)) {
            return $this->perPage;
        }

        return $perPage;
    }

    /**
     * Validate sort parameters
     */
    protected function validateSort(
        ?string $column = null,
        ?string $direction = null,
        array $allowedColumns = ['id', 'created_at']
    ): array {
        $column = $column ?? request('sort', 'id');
        $direction = $direction ?? request('direction', 'desc');

        // Validate column
        if (!in_array($column, $allowedColumns)) {
            $column = 'id';
        }

        // Validate direction
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        return [$column, $direction];
    }

    /**
     * Apply search to query
     */
    protected function applySearch($query, array $searchableFields, ?string $search = null)
    {
        $search = $search ?? request('search');

        if (!$search || empty($searchableFields)) {
            return $query;
        }

        return $query->where(function ($q) use ($searchableFields, $search) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters($query, array $filters)
    {
        foreach ($filters as $field => $value) {
            if (request()->filled($field)) {
                $query->where($field, $value ?? request($field));
            }
        }

        return $query;
    }

    /**
     * Log activity
     */
    protected function logActivity(string $action, string $description, array $properties = []): void
    {
        Log::info($action, [
            'description' => $description,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'properties' => $properties,
        ]);
    }

    /**
     * Redirect with success message
     */
    protected function redirectWithSuccess(string $route, string $message): RedirectResponse
    {
        return redirect()->route($route)->with('success', $message);
    }

    /**
     * Redirect with error message
     */
    protected function redirectWithError(string $route, string $message): RedirectResponse
    {
        return redirect()->route($route)->with('error', $message);
    }

    /**
     * Redirect back with success
     */
    protected function backWithSuccess(string $message): RedirectResponse
    {
        return back()->with('success', $message);
    }

    /**
     * Redirect back with error
     */
    protected function backWithError(string $message): RedirectResponse
    {
        return back()->with('error', $message)->withInput();
    }
}
