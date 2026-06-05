<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('nms.branding.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 font-sans antialiased" x-data="{ sidebarOpen: false }">
        @include('layouts.partials.sidebar')

        <div class="nms-main">
            <header class="sticky top-0 z-20 flex h-14 shrink-0 items-center gap-3 border-b border-gray-200 bg-white px-4 lg:hidden">
                <button type="button" @click="sidebarOpen = !sidebarOpen"
                        class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:bg-gray-100">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <img src="{{ asset(config('nms.branding.logo')) }}"
                     alt="{{ config('nms.branding.organization') }}"
                     class="nms-img-logo-md">
                <span class="truncate text-sm font-semibold text-indigo-700">{{ config('nms.branding.name') }}</span>
            </header>

            @isset($header)
                <header class="border-b border-gray-200 bg-white">
                    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
