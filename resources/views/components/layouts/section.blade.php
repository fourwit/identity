{{--
| Generic section-based adapter.
| Use when host layout is based on @extends + @section('content').
| Example host env: IDENTITY_LAYOUT=identity::components.layouts.section
--}}
@extends('layouts.app')

@section('content')
    @yield('content')
@endsection

@push('styles')
    @stack('styles')
@endpush

@push('scripts')
    @stack('scripts')
@endpush
