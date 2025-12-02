<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-neutral-950">
        <div
            class="relative flex min-h-svh flex-col items-center justify-center px-4 py-10 md:px-8"
            style="background: linear-gradient(120deg, rgba(11,23,36,0.78), rgba(11,23,36,0.9)), url('{{ asset('WG - Secretaria de movilidad_1638.jpg') }}') center/cover no-repeat;"
        >
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(16,185,129,0.12),transparent_35%),radial-gradient(circle_at_80%_10%,rgba(6,182,212,0.16),transparent_35%)]"></div>
            <div class="relative z-10 flex w-full max-w-md flex-col gap-4 rounded-2xl border border-white/10 bg-white/80 p-6 shadow-2xl backdrop-blur dark:bg-neutral-900/80">
                <a href="{{ route('home') }}" class="flex items-center gap-3 text-white" wire:navigate>
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-r from-emerald-500 to-cyan-500 shadow-lg">
                        <x-app-logo-icon class="size-8 fill-current text-white" />
                    </span>
                    <div class="text-sm font-semibold text-white">Sogar · Finanzas en calma</div>
                </a>
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
