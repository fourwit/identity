@extends('identity::components.layouts.master')

@section('title', 'My Profile')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        
        @auth
            <!-- Logged In User Profile -->
            <div class="flex items-center gap-6 mb-8">
                <div class="w-20 h-20 bg-gray-200 rounded-full flex-shrink-0 overflow-hidden relative group">
                    @if($user->avatar_id)
                        <img src="{{ $user->avatar?->url }}" class="w-full h-full object-cover">
                        <form action="{{ route('identity.account.avatar.remove') }}" method="POST" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
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
            <form action="{{ route('identity.account.profile.update') }}" method="POST">
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
                <form action="{{ route('identity.account.password.update') }}" method="POST">
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
