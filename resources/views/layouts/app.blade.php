<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'HAUS Gateway'))</title>

    @vite(array_merge(['resources/css/app.css'], $vite ?? ['resources/js/app.js']))

    @stack('head')
</head>
<body class="@yield('body_class')">

    @yield('content')

    @stack('scripts')
</body>
</html>
