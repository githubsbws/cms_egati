<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('admin/menuleft.home')) - {{ config('app.name') }}</title>
    @vite(['resources/sass/admin.scss', 'resources/js/admin.js'])
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        @include('admin.layouts.partials.header')

        @include('admin.layouts.partials.menu-left')

        {{-- Main content --}}
        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <h3 class="mb-0">@yield('title', __('admin/menuleft.home'))</h3>
                    <ol class="breadcrumb float-sm-end mb-0">
                        @yield('breadcrumb')
                    </ol>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </main>

        @include('admin.layouts.partials.footer')
    </div>

    @stack('scripts')
</body>
</html>
