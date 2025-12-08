<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-neutral-primary-soft text-body">
        <x-navigation.flowbite />

        <main class="pt-28">
            <div class="mx-auto w-full max-w-6xl px-6">
                <div class="mb-4">
                    <x-breadcrumbs />
                </div>
                {{ $slot }}
            </div>
        </main>

        @stack('scripts')
    </body>
</html>
