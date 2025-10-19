<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />

    {{-- Pakai Vite untuk CSS & JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/ui.js'])
    
    <title>Inventaris TX | @yield('title')</title>
    
</head>

<body class="min-h-screen bg-gray-50 dark:bg-gray-950">
    <div class="flex min-h-screen">
        {{-- Sidebar (pakai $store.layout.sidebarToggle) --}}
        @persist('sidebar')
          @include('layouts.sidebar')
        @endpersist
        {{-- Konten utama --}}
        <div class="flex min-h-screen flex-1 flex-col">
            {{-- Navbar (tombolnya ubah $store.layout.sidebarToggle) --}}

            @include('layouts.navbar')
            @yield('content')
        </div>

</body>
@include('layouts.script')
</html>
