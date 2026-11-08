<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'หลังบ้าน') - {{ config('app.name') }}</title>
    @vite(['resources/sass/admin.scss', 'resources/js/admin.js'])
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        @include('layouts.partials.header')

        @include('layouts.partials.menu-left')

        {{-- Main content --}}
        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <h3 class="mb-0">@yield('title', 'หน้าหลัก')</h3>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </main>

        @include('layouts.partials.footer')
    </div>
</body>
</html>