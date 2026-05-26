<?php

namespace Modules\Identity\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    /**
     * Success Response
     */
    protected function successResponse($data, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], $code);
    }

    /**
     * Error Response
     */
    protected function errorResponse(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }

    /**
     * Not Found Response
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Validation Error Response
     */
    protected function validationErrorResponse($errors, string $message = 'Validation failed'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors
        ], 422);
    }

    /**
     * Return paginated response with full pagination metadata
     */
    protected function paginatedResponse(
        mixed $resource,
        string $message = 'Success',
        $paginator = null
    ): JsonResponse {
        // Auto-detect paginator from resource if not passed
        if ($paginator === null && method_exists($resource, 'resource')) {
            $paginator = $resource->resource;
        }

        $pagination = null;
        if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator) {
            $pagination = [
                'current_page'   => $paginator->currentPage(),
                'per_page'       => $paginator->perPage(),
                'next_page_url'  => $paginator->nextPageUrl(),
                'prev_page_url'  => $paginator->previousPageUrl(),
            ];

            if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
                $pagination['total'] = $paginator->total();
                $pagination['last_page'] = $paginator->lastPage();
            }
        }

        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $resource,
        ];

        if ($pagination !== null) {
            $response['pagination'] = $pagination;
        }

        return response()->json($response);
    }
}