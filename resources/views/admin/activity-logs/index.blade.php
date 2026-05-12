@extends('identity::components.layouts.master')

@section('title', 'Activity Logs')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Activity Logs</h1>
            <p class="text-gray-600 mt-1">Track all user-related activities</p>
        </div>
        <a href="{{ route('admin.users.index') }}" 
           class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition">
            ← Back to Users
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow border p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            
            <!-- Search -->
            <div class="md:col-span-2">
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

            <div class="md:col-span-5 flex justify-end gap-3 mt-2">
                <a href="{{ route('admin.activity-logs.index') }}" 
                   class="px-6 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition">Clear</a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl shadow border overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Time</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Source</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">Performed By</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600">IP Address</th>
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

    <div class="mt-6">
        {{ $logs->withQueryString()->links() }}
    </div>
</div>
@endsection