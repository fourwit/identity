@extends(config('identity.views.layout'))

@section('title', 'My Profile')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        
        <!-- Flash Messages -->
        @if ($errors->any() && ! $errors->has('delete_password'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-red-800">There were some errors with your submission</h3>
                        <ul class="mt-2 text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-green-800">{{ session('success') }}</h3>
                    </div>
                </div>
            </div>
        @endif
        
        @auth
            <!-- Logged In User Profile -->
            <div class="flex items-center gap-6 mb-8">
                <div class="w-20 h-20 bg-gray-200 rounded-full flex-shrink-0 overflow-hidden relative group">
                    @if($user->avatar_id)
                        <img src="{{ $user->avatar?->url }}" class="w-full h-full object-cover">
                        <form action="{{ url('/account/avatar') }}" method="POST" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-white text-xs font-semibold">Remove</button>
                        </form>
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-blue-100 text-blue-600 text-4xl font-bold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                    <p class="text-gray-500">{{ $user->email }}</p>
                </div>
            </div>

            <!-- Profile Info Form -->
            <form action="{{ url('/account/profile') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('first_name') border-red-500 @enderror">
                            @error('first_name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                        @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
                        @error('phone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Timezone</label>
                            <input type="text" name="timezone" value="{{ old('timezone', $user->timezone) }}" placeholder="UTC"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('timezone') border-red-500 @enderror">
                            @error('timezone') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Locale</label>
                            <input type="text" name="locale" value="{{ old('locale', $user->locale) }}" placeholder="en"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('locale') border-red-500 @enderror">
                            @error('locale') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t flex justify-end">
                    <button type="submit" 
                            class="px-8 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
                        Save Changes
                    </button>
                </div>
            </form>

            <!-- Password Update Form -->
            <div class="mt-12 pt-8 border-t">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Change Password</h2>
                <form action="{{ url('/account/password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Current Password</label>
                            <input type="password" name="current_password"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('current_password') border-red-500 @enderror">
                            @error('current_password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">New Password</label>
                                <input type="password" name="password"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                                @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm New Password</label>
                                <input type="password" name="password_confirmation"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t flex justify-end">
                        <button type="submit" 
                                class="px-8 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Delete Account -->
            <div class="mt-12 pt-8 border-t">
                <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Delete your own account</h2>
                            <p class="text-sm text-gray-600 mt-2">
                                This permanently deletes your account after confirming your current password.
                                Super admin protection still applies when configured in Identity.
                            </p>
                        </div>
                        <button type="button"
                                onclick="openDeleteAccountModal()"
                                class="px-6 py-3 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition">
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>

            <div id="deleteAccountModal" class="@if($errors->has('delete_password')) flex @else hidden @endif fixed inset-0 z-50 bg-black/50 items-center justify-center px-4">
                <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Confirm account deletion</h3>
                            <p class="mt-2 text-sm text-gray-600">
                                Enter your current password to delete your account.
                            </p>
                        </div>
                        <button type="button" onclick="closeDeleteAccountModal()" class="text-gray-400 hover:text-gray-600">✕</button>
                    </div>

                    <form method="POST" action="{{ url('/account/delete-account') }}" class="mt-6 space-y-4">
                        @csrf
                        @method('DELETE')
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Current Password</label>
                            <input type="password" name="delete_password"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 @error('delete_password') border-red-500 @enderror">
                            @error('delete_password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" onclick="closeDeleteAccountModal()"
                                    class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2.5 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700">
                                Delete My Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <!-- Fallback if not authenticated (should be blocked by middleware, but kept for design preview) -->
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-user-circle text-6xl mb-4"></i>
                <p class="text-lg">Please login to view and edit your profile.</p>
            </div>
        @endauth
        
    </div>
</div>
@endsection

<script>
function openDeleteAccountModal() {
    document.getElementById('deleteAccountModal').classList.remove('hidden');
    document.getElementById('deleteAccountModal').classList.add('flex');
}

function closeDeleteAccountModal() {
    const modal = document.getElementById('deleteAccountModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

@if ($errors->has('delete_password'))
document.addEventListener('DOMContentLoaded', function () {
    openDeleteAccountModal();
});
@endif
</script>
