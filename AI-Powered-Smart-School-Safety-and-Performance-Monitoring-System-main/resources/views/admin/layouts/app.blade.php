<!DOCTYPE html>
<html lang="en">

@include('admin.layouts.head')

<body class="g-sidenav-show  bg-gray-100">
    @yield('content')

    @include('admin.layouts.notifications')

    @include('admin.layouts.script')

    @stack('scripts')
    @yield('js')
</body>

</html>
