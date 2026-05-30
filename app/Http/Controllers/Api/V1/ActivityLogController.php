<?php

namespace Modules\Identity\Http\Controllers\Api\V1;

use Modules\Identity\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends BaseApiController
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', config('identity.user.per_page', 20));
        $sortBy = (string) $request->get('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['created_at', 'description', 'source', 'causer_id', 'ip_address'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        $logs = ActivityLog::with(['causer', 'subject'])
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->appends($request->query());

        return $this->successResponse(
            $logs,
            'Activity logs retrieved successfully'
        );
    }
}
