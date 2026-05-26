@extends('identity::components.layouts.master')

@section('title', 'Users Management')

@section('content')
<div class="p-6">
    <!-- Header -->
    <!-- <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Users</h1>
            <p class="text-gray-600 mt-1">Manage all system users</p>
        </div>
        <a href="{{ route('admin.users.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add New User
        </a>
    </div> -->

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Users</h1>
            <p class="text-gray-600 mt-1">Manage all system users</p>
        </div>
        
        <div class="flex gap-3">
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

    <!-- Search & Filters -->
    <form method="GET" action="{{ route('admin.users.index') }}">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-3">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Search by name, email or phone..." 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                <button type="submit" class="px-6 py-2 bg-gray-800 text-white rounded-lg">Filter</button>
            </div>
        </div>
    </form>

    <!-- <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row gap-4">
        <input type="text" name="search" value="{{ request('search') }}" 
            placeholder="Search by name, email or phone..." 
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
        
        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">All Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
        
        <button type="submit" class="px-6 py-2 bg-gray-800 text-white rounded-lg">Filter</button>
    </form> -->

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Last Login</th>
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
                                    <span class="text-gray-600 font-medium">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
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
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            @if($user->status->label() === 'Active') bg-green-100 text-green-800
                            @elseif($user->status->label() === 'Inactive') bg-gray-100 text-gray-800
                            @elseif($user->status->label() === 'Suspended') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($user->status->label()) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" 
                               class="px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition">Edit</a>
                            
                            <button onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')"
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

    <div class="mt-6">
        {{ $users->links() }}
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
                <button type="submit" 
                        class="px-5 py-2.5 bg-red-600 text-white rounded-xl hover:bg-red-700 transition">Delete User</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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