<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
<body class="min-h-screen bg-[#0e1628] text-body antialiased">
    <div class="relative flex min-h-svh flex-col items-center justify-center px-4 py-10 md:px-8">
        <div class="absolute inset-0 bg-[radial-gradient(900px_circle_at_15%_20%,rgba(255,255,255,0.08),transparent_45%),radial-gradient(800px_circle_at_85%_0%,rgba(59,130,246,0.12),transparent_40%),linear-gradient(135deg,#0f172a_0%,#111827_55%,#0b1221_100%)]"></div>

        <div class="relative z-10 flex w-full max-w-md flex-col gap-6 rounded-2xl border border-slate-200/70 bg-white/98 p-6 shadow-2xl backdrop-blur dark:border-white/10 dark:bg-slate-950/90">
            <a href="{{ url('/') }}" class="flex items-center gap-3 text-heading dark:text-white" wire:navigate>
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white shadow ring-1 ring-slate-200 dark:bg-slate-900 dark:ring-slate-700">
                    <x-app-logo-icon class="size-8 fill-current text-heading dark:text-white" />
                </span>
                <div class="text-sm font-semibold">{{ config('app.name') }}</div>
            </a>
            <div class="flex flex-col gap-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
