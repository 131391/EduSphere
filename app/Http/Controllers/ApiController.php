<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * API Controller
 * 
 * Base controller for all API endpoints
 * Provides standardized JSON responses
 */
abstract class ApiController extends BaseController
{
    /**
     * Return success response with data
     */
    protected function success(
        mixed $data = null,
        string $message = 'Success',
        int $code = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return created response
     */
    protected function created(
        mixed $data = null,
        string $message = 'Resource created successfully'
    ): JsonResponse {
        return $this->success($data, $message, 201);
    }

    /**
     * Return no content response
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return error response
     */
    protected function error(
        string $message = 'An error occurred',
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
     * Return validation error response
     */
    protected function validationError(
        mixed $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return $this->error($message, 422, $errors);
    }

    /**
     * Return unauthorized response
     */
    protected function unauthorized(
        string $message = 'Unauthorized'
    ): JsonResponse {
        return $this->error($message, 401);
    }

    /**
     * Return forbidden response
     */
    protected function forbidden(
        string $message = 'Forbidden'
    ): JsonResponse {
        return $this->error($message, 403);
    }

    /**
     * Return not found response
     */
    protected function notFound(
        string $message = 'Resource not found'
    ): JsonResponse {
        return $this->error($message, 404);
    }

    /**
     * Return server error response
     */
    protected function serverError(
        string $message = 'Internal server error'
    ): JsonResponse {
        return $this->error($message, 500);
    }

    /**
     * Return paginated API response
     */
    protected function paginated(
        $query,
        ?int $perPage = null,
        string $message = 'Data retrieved successfully'
    ): JsonResponse {
        $perPage = $this->validatePerPage($perPage);
        $paginated = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'from' => $paginated->firstItem(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'to' => $paginated->lastItem(),
                'total' => $paginated->total(),
            ],
            'links' => [
                'first' => $paginated->url(1),
                'last' => $paginated->url($paginated->lastPage()),
                'prev' => $paginated->previousPageUrl(),
                'next' => $paginated->nextPageUrl(),
            ],
        ]);
    }
}
