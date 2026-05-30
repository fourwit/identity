{{--
| Blank/embedded adapter.
| Renders only module content with no shell markup.
| Useful for iframe, modal, or externally composed host shells.
--}}
@yield('content')

@stack('styles')
@stack('scripts')
