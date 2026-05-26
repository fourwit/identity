@extends('identity::components.layouts.master')

@section('title', 'Account Verification Status')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Account Verification</h1>
            <p class="text-gray-500 mt-1">Check and manage your email and phone verification status.</p>
        </div>

        <div class="space-y-6">
            <!-- Email Verification -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $user->hasVerifiedEmail() ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                        <i class="fas {{ $user->hasVerifiedEmail() ? 'fa-check' : 'fa-envelope' }}"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Email Address</h3>
                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>
                <div>
                    @if($user->hasVerifiedEmail())
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Verified</span>
                    @else
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Pending Verification</span>
                    @endif
                </div>
            </div>

            <!-- Phone Verification -->
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center {{ !is_null($user->phone_verified_at) ? 'bg-green-100 text-green-600' : 'bg-yellow-100 text-yellow-600' }}">
                        <i class="fas {{ !is_null($user->phone_verified_at) ? 'fa-check' : 'fa-phone' }}"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Phone Number</h3>
                        <p class="text-sm text-gray-500">{{ $user->phone ?? 'Not provided' }}</p>
                    </div>
                </div>
                <div>
                    @if(!is_null($user->phone_verified_at))
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Verified</span>
                    @else
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">Pending Verification</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
