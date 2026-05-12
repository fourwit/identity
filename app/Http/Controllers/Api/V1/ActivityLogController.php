<?php

namespace Modules\Identity\Http\Controllers\Api\V1;

use Modules\Identity\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends BaseApiController
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with(['causer', 'subject'])
            ->latest()
            ->paginate(20);

        return $this->successResponse(
            $logs,
            'Activity logs retrieved successfully'
        );
    }
}