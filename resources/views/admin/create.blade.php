@extends(config('identity.views.layout'))

@section('title', 'Create New User')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i> Back to Users
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Create New User</h1>
            <p class="text-gray-600 mt-2">Create a new user account.</p>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" novalidate>
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                
                <!-- Full Name (Required) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name 
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- First Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('first_name') border-red-500 @enderror">
                    @error('first_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Email (Optional) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Email Address 
                        @if(config('identity.user.require_email'))
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Phone Number 
                        @if(config('identity.user.require_phone'))
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username (Optional) -->

                <!-- Username -->
                @if(config('identity.features.username'))
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Username 
                            @if(config('identity.user.require_username'))
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        <input type="text" name="username" value="{{ old('username') }}"
                             class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('username') border-red-500 @enderror">
                        @error('username')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                        @foreach(config('identity.statuses', ['active', 'inactive', 'suspended', 'pending']) as $status)
                            <option value="{{ $status }}" {{ old('status', 'active') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                    <input type="password" name="password" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>

            </div>

            <div class="mt-10 pt-6 border-t flex justify-end gap-4">
                <a href="{{ route('admin.users.index') }}" 
                   class="px-6 py-2.5 text-gray-700 hover:bg-gray-100 rounded-xl transition">Cancel</a>
                <button type="submit" 
                        class="px-8 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-semibold">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection