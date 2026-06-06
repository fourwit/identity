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
        $query = ActivityLog::with(['causer', 'subject']);

        // Search by description
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where('description', 'like', $searchTerm);
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

        $sortBy = (string) $request->get('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['created_at', 'description', 'source', 'causer_id', 'ip_address'];
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }
        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) $request->get('per_page', config('identity.user.per_page', 25));
        $logs = $query->paginate($perPage)->appends($request->query());

        // Get all users for filter dropdown
        $userModelClass = IdentityConfig::userModelClass();
        $users = $userModelClass::query()->orderBy('name')->get();

        return view('identity::admin.activity-logs.index', compact('logs', 'users'));
    }
}
