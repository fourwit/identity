@extends(config('identity.views.layout'))

@section('title', 'Users Management')

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    @if(config('identity.views.show_page_title_row', true))
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-6">
            <div>
                <h1 class="font-semibold text-xl text-gray-800 leading-tight">Users</h1>
                <p class="text-gray-600 mt-1">Manage all system users</p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.activity-logs.index') }}" 
                class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition">
                    <i class="fas fa-history mr-2"></i> View Activity Logs
                </a>
                
                <a href="{{ route('admin.users.create') }}" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-plus mr-2"></i> Add New User
                </a>
            </div>
        </div>
    @endif

    <!-- Search & Filters -->
    <form method="GET" action="{{ route('admin.users.index') }}">
        @php
            $selectedPerPage = (string) request('per_page', config('identity.user.per_page', 15));
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-wrap items-end gap-4">
                <div class="flex-[2] min-w-[220px]">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Search by name, email or phone..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex-1 min-w-[170px]">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="mb-1 block text-sm font-medium text-gray-700">&nbsp;</label>
                    <button type="submit" class="w-full px-6 py-2 bg-gray-800 text-white rounded-lg">Filter</button>
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="mb-1 block text-sm font-medium text-gray-700">&nbsp;</label>
                    <a href="{{ route('admin.users.index') }}" class="block w-full px-6 py-2 text-center text-gray-700 hover:bg-gray-100 rounded-lg transition">Clear</a>
                </div>
            </div>
        </div>
    </form>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto w-full">
        <table class="w-full min-w-[960px] divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><a class="inline-flex items-center justify-start" href="{{ $sortLink('name') }}">User {!! $sortIcon('name') !!}</a></th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><a class="inline-flex items-center justify-start" href="{{ $sortLink('phone') }}">Contact {!! $sortIcon('phone') !!}</a></th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><a class="inline-flex items-center justify-start" href="{{ $sortLink('status') }}">Status {!! $sortIcon('status') !!}</a></th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider"><a class="inline-flex items-center justify-start" href="{{ $sortLink('last_login_at') }}">Last Login {!! $sortIcon('last_login_at') !!}</a></th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden">
                                @if($user->avatar_id)
                                    <img src="{{ $user->avatar?->url }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center rounded-full bg-blue-100 text-blue-600 font-semibold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $user->phone ?? '—' }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusLabel = $user->status?->label() ?? 'Pending';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($statusLabel === 'Active') bg-green-100 text-green-800
                            @elseif($statusLabel === 'Inactive') bg-gray-100 text-gray-800
                            @elseif($statusLabel === 'Suspended') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($statusLabel) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            @php
                                $editUrl = route('admin.users.edit', $user) . '?' . http_build_query(['redirect_to' => request()->fullUrl()]);
                            @endphp
                            <a
                               href="{{ $editUrl }}"
                               class="px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition">Edit</a>
                            
                            <button onclick="confirmDelete({{ $user->id }}, {{ json_encode($user->name) }})"
                                    class="px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition">Delete</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="text-gray-400">No users found</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <form id="perPageForm" method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="sort_by" value="{{ request('sort_by', 'created_at') }}">
            <input type="hidden" name="sort_dir" value="{{ request('sort_dir', 'desc') }}">
            Per page:
            <select id="perPageSelect" name="per_page" class="px-3 py-1.5 border border-gray-300 rounded-lg">
                @foreach(config('identity.user.per_page_options', [5, 15, 25, 50, 100, 500, 1000]) as $size)
                    <option value="{{ $size }}" {{ $selectedPerPage === (string) $size ? 'selected' : '' }}>
                        {{ $size }}
                    </option>
                @endforeach
            </select>
            <span>
                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results
            </span>
        </form>
        <div class="identity-pagination overflow-x-auto">
            {{ $users->onEachSide(1)->appends(request()->query())->links('identity::pagination.tailwind-no-summary') }}
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md mx-4">
        <h3 class="text-xl font-semibold mb-4">Delete User?</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to delete <span id="deleteUserName" class="font-semibold"></span>? This action cannot be undone.</p>
        
        <div class="flex justify-end gap-3">
            <button onclick="closeDeleteModal()" 
                    class="px-5 py-2.5 text-gray-700 hover:bg-gray-100 rounded-xl transition">Cancel</button>
            
            <form id="deleteForm" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <input type="hidden" name="redirect_to" id="deleteRedirectTo" value="{{ request()->fullUrl() }}">
                <button type="submit" 
                        class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition">Delete User</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const key = 'identity_admin_users_per_page';
    const select = document.getElementById('perPageSelect');
    if (!select) return;

    const form = document.getElementById('perPageForm');
    const url = new URL(window.location.href);
    const perPageFromUrl = url.searchParams.get('per_page');

    if (perPageFromUrl) {
        localStorage.setItem(key, perPageFromUrl);
    } else {
        const stored = localStorage.getItem(key);
        if (stored && [...select.options].some(option => option.value === stored)) {
            select.value = stored;
            if (form) {
                form.submit();
            }
        }
    }

    select.addEventListener('change', function () {
        localStorage.setItem(key, this.value);
        if (form) {
            form.submit();
        }
    });
});

function confirmDelete(id, name) {
    document.getElementById('deleteUserName').innerText = name;
    const form = document.getElementById('deleteForm');
    form.action = `/admin/users/${id}`;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteModal').classList.add('flex');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}
</script>
@endpush
