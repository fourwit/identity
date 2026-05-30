{{--
| Breeze-only adapter.
| Use when host app uses Laravel Breeze's component layout (<x-app-layout>).
| Example host env: IDENTITY_LAYOUT=identity::components.layouts.breeze-app
--}}
<x-app-layout>
    @hasSection('header')
        <x-slot name="header">
            @yield('header')
        </x-slot>
    @endif

    @yield('content')

    @stack('styles')
    @stack('scripts')
</x-app-layout>
