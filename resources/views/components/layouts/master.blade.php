<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'User Module') | {{ config('identity.branding.name', 'Fourwit') }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&amp;display=swap');
        
        :root {
            --primary: #2563eb;
        }
        
        body {
            font-family: 'Inter', system_ui, sans-serif;
        }
        
        .nav-active {
            background-color: #eff6ff;
            color: #2563eb;
            font-weight: 600;
        }
        
        .module-card {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Top Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center">
                        <span class="text-white font-bold text-xl">{{ substr(config('identity.branding.name', 'Fourwit'), 0, 1) }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-xl text-gray-900">{{ config('identity.branding.name', 'Fourwit') }}</span>
                        <span class="text-xs text-gray-500 block -mt-1">User Module</span>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="flex items-center gap-4">
                    @auth
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-gray-500">{{ auth()->user()->email }}</div>
                            </div>
                            
                            <div class="w-9 h-9 bg-gray-200 rounded-full overflow-hidden">
                                @if(auth()->user()->avatar_id)
                                    <img src="{{ auth()->user()->avatar?->url }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-blue-100 text-blue-600 text-sm font-bold">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-200 text-green-800 px-5 py-3 rounded-2xl flex items-center gap-3">
                <i class="fas fa-check-circle text-green-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-100 border border-red-200 text-red-800 px-5 py-3 rounded-2xl flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-red-600"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Main Content -->
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="border-t border-gray-200 mt-12 py-6">
        <div class="max-w-7xl mx-auto px-6 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} {{ config('identity.branding.name', 'Fourwit') }} • Professional Laravel Module Library
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
<?php 
/*<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <title>User Module - {{ config('app.name', 'Laravel') }}</title>

        <meta name="description" content="{{ $description ?? '' }}">
        <meta name="keywords" content="{{ $keywords ?? '' }}">
        <meta name="author" content="{{ $author ?? '' }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        {{-- Vite CSS --}}
        {{-- {{ module_vite('build-user', 'resources/assets/sass/app.scss') }} --}}
    </head>

    <body>
        {{ $slot }}

        {{-- Vite JS --}}
        {{-- {{ module_vite('build-user', 'resources/assets/js/app.js') }} --}}
    </body>
</html>
*/ ?>