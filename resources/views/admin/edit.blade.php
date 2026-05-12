@extends('identity::components.layouts.master')

@section('title', 'Edit User')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i> Back to Users
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-14 h-14 bg-gray-200 rounded-full flex-shrink-0 overflow-hidden">
                @if($user->avatar_id)
                    <img src="{{ $user->avatar?->url }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-blue-100 text-blue-600 text-3xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit {{ $user->name }}</h1>
                <p class="text-gray-500">User ID: #{{ $user->id }}</p>
            </div>
        </div>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                
                <!-- Full Name -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
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
                    <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('first_name') border-red-500 @enderror">
                    @error('first_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Email Address 
                        @if(config('identity.require_email'))
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Phone Number 
                        @if(config('identity.require_phone'))
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Username -->
                @if(config('identity.enable_username'))
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Username 
                        @if(config('identity.require_username'))
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}"
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
                            <option value="{{ $status }}" {{ old('status', $user->status) == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="mt-10 pt-6 border-t flex justify-end gap-4">
                <a href="{{ route('admin.users.index') }}" 
                   class="px-6 py-2.5 text-gray-700 hover:bg-gray-100 rounded-xl transition">Cancel</a>
                <button type="submit" 
                        class="px-8 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-semibold">
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection