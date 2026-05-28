<?php

namespace Modules\Identity\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Identity\Models\ActivityLog;
use Illuminate\Http\Request;
use Modules\Identity\Support\IdentityConfig;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['causer', 'subject'])->latest();

        // Search by description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Filter by source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by user (causer)
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        $logs = $query->paginate(25);

        // Get all users for filter dropdown
        $userModelClass = IdentityConfig::userModelClass();
        $users = $userModelClass::query()->orderBy('name')->get();

        return view('identity::admin.activity-logs.index', compact('logs', 'users'));
    }
}
