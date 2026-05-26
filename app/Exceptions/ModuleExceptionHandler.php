<?php

namespace Modules\Identity\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModuleExceptionHandler
{
    /**
     * Handle module exceptions and return appropriate response
     */
    public static function handle(\Throwable $exception, Request $request): JsonResponse|RedirectResponse
    {
        if (!$exception instanceof ModuleException) {
            // Let Laravel handle non-module exceptions
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred',
            ], 500);
        }

        // API Response
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], $exception->getStatusCode());
        }

        // Web Response (redirect with error)
        return redirect()
            ->back()
            ->withInput()
            ->with('error', $exception->getMessage());
    }
}