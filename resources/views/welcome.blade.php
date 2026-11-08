<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/sass/web.scss', 'resources/js/web.js'])
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>
        </div>
    </nav>

    <main class="container py-5">
        <div class="p-5 mb-4 bg-light rounded-3">
            <h1 class="display-5 fw-bold">{{ config('app.name', 'Laravel') }}</h1>
            <p class="col-md-8 fs-4">Website is running (Laravel {{ app()->version() }} / Bootstrap 5)</p>
        </div>
    </main>

    <footer class="container py-4 text-center text-muted border-top">
        &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}
    </footer>
</body>
</html>