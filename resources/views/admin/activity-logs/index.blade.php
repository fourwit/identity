@extends(config('identity.views.layout'))

@section('title', 'Activity Logs')

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    @php
        $selectedPerPage = (string) request('per_page', config('identity.user.per_page', 25));
        $sortBy = request('sort_by', 'created_at');
        $sortDir = request('sort_dir', 'desc');
        $sortIcon = function (string $column) use ($sortBy, $sortDir): string {
            if ($sortBy !== $column) {
                return '<svg class="w-4 h-4 inline-block ml-1 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 4l3 3H7l3-3zm0 12l-3-3h6l-3 3z"/></svg>';
            }

            if ($sortDir === 'asc') {
                return '<svg class="w-4 h-4 inline-block ml-1 text-gray-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 4l4 4H6l4-4z"/></svg>';
            }

            return '<svg class="w-4 h-4 inline-block ml-1 text-gray-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 16l-4-4h8l-4 4z"/></svg>';
        };
        $sortLink = function (string $column) use ($sortBy, $sortDir): string {
            $dir = ($sortBy === $column && $sortDir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_dir' => $dir]);
        };
    @endphp
    @if(config('identity.views.show_page_title_row', true))
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-6">
            <div>
                <h1 class="font-semibold text-xl text-gray-800 leading-tight">Activity Logs</h1>
                <p class="text-gray-600 mt-1">Track all user-related activities</p>
            </div>
            <a href="{{ route('admin.users.index') }}" 
               class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition">
                ← Back to Users
            </a>
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow border p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            
            <!-- Search -->
            <div class="md:col-span-2 lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search Action</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search description..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <!-- Source -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                <select name="source" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Sources</option>
                    <option value="web" {{ request('source') == 'web' ? 'selected' : '' }}>Web</option>
                    <option value="api" {{ request('source') == 'api' ? 'selected' : '' }}>API</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>

            <div class="md:col-span-2 lg:col-span-5 flex flex-col sm:flex-row sm:justify-end gap-3 mt-2">
                <a href="{{ route('admin.activity-logs.index') }}" 
                   class="w-full sm:w-auto text-center px-6 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">Clear</a>
                <button type="submit" 
                        class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl shadow border overflow-x-auto w-full">
        <table class="w-full min-w-[960px] divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600"><a href="{{ $sortLink('created_at') }}">Time {!! $sortIcon('created_at') !!}</a></th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600"><a href="{{ $sortLink('description') }}">Action {!! $sortIcon('description') !!}</a></th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600"><a href="{{ $sortLink('source') }}">Source {!! $sortIcon('source') !!}</a></th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600"><a href="{{ $sortLink('causer_id') }}">Performed By {!! $sortIcon('causer_id') !!}</a></th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600"><a href="{{ $sortLink('ip_address') }}">IP Address {!! $sortIcon('ip_address') !!}</a></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $log->created_at->format('d M Y, h:i A') }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $log->description }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-block px-2.5 py-1 text-xs font-medium rounded-full 
                            {{ $log->source === 'api' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ strtoupper($log->source) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $log->causer?->name ?? 'System' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 font-mono">
                        {{ $log->ip_address ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        No activity logs found matching your filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <form id="activityPerPageForm" method="GET" action="{{ route('admin.activity-logs.index') }}" class="flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="source" value="{{ request('source') }}">
            <input type="hidden" name="date_from" value="{{ request('date_from') }}">
            <input type="hidden" name="date_to" value="{{ request('date_to') }}">
            <input type="hidden" name="user_id" value="{{ request('user_id') }}">
            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'created_at') }}">
            <input type="hidden" name="sort_dir" value="{{ request('sort_dir', 'desc') }}">
            Per page:
            <select id="activityPerPageSelect" name="per_page" class="px-3 py-1.5 border border-gray-300 rounded-lg">
                @foreach(config('identity.user.per_page_options', [5, 15, 25, 50, 100, 500, 1000]) as $size)
                    <option value="{{ $size }}" {{ $selectedPerPage === (string) $size ? 'selected' : '' }}>
                        {{ $size }}
                    </option>
                @endforeach
            </select>
            <span>
                Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} results
            </span>
        </form>

        <div class="activity-pagination">
            {{ $logs->withQueryString()->links('identity::pagination.tailwind-no-summary') }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const key = 'identity_admin_activity_logs_per_page';
    const select = document.getElementById('activityPerPageSelect');
    const form = document.getElementById('activityPerPageForm');
    if (!select || !form) return;

    const url = new URL(window.location.href);
    const perPageFromUrl = url.searchParams.get('per_page');

    if (perPageFromUrl) {
        localStorage.setItem(key, perPageFromUrl);
    } else {
        const stored = localStorage.getItem(key);
        if (stored && [...select.options].some(option => option.value === stored)) {
            select.value = stored;
            form.submit();
        }
    }

    select.addEventListener('change', function () {
        localStorage.setItem(key, this.value);
        form.submit();
    });
});
</script>
@endpush
