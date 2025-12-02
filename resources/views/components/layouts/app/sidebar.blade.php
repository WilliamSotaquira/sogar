<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800" x-data="{ mobileOpen: false }">
        <!-- Mobile overlay -->
        <div
            x-show="mobileOpen"
            class="fixed inset-0 z-30 bg-black/50 lg:hidden"
            x-transition.opacity
            @click="mobileOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 z-40 w-72 border-r border-zinc-200 bg-zinc-50/90 backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/90 transition-transform duration-200 lg:fixed lg:translate-x-0"
            :class="mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                    <x-app-logo />
                </a>
                <button
                    type="button"
                    class="lg:hidden rounded-lg p-2 text-zinc-500 hover:bg-zinc-800/5 dark:text-zinc-300 dark:hover:bg-white/10"
                    @click="mobileOpen = false"
                    aria-label="Cerrar menú"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav class="mt-4 space-y-1 px-3">
                @php
                    $links = [
                        ['label' => 'Dashboard', 'route' => 'dashboard'],
                        ['label' => 'Bolsillos', 'route' => 'wallets.index'],
                        ['label' => 'Presupuestos', 'route' => 'budgets.index'],
                        ['label' => 'Categorías', 'route' => 'categories.index'],
                        ['label' => 'Recurrencias', 'route' => 'recurrences.index'],
                        ['label' => 'Transacciones', 'route' => 'transactions.index'],
                    ];
                @endphp
                @foreach ($links as $link)
                    @php $active = request()->routeIs(Str::before($link['route'], '.') . '*'); @endphp
                    <a href="{{ route($link['route']) }}" wire:navigate class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-800/5 dark:text-zinc-200 dark:hover:bg-white/10 {{ $active ? 'bg-zinc-800/5 dark:bg-white/10 text-emerald-600 dark:text-emerald-300' : '' }}">
                        <span class="h-2 w-2 rounded-full {{ $active ? 'bg-emerald-500' : 'bg-zinc-400 dark:bg-zinc-500' }}"></span>
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto px-3 py-4 border-t border-white/10">
                <div class="hidden lg:block">
                    <flux:dropdown position="bottom" align="start">
                        <flux:profile
                            :name="auth()->user()->name"
                            :initials="auth()->user()->initials()"
                            icon:trailing="chevrons-up-down"
                        />

                        <flux:menu class="w-[220px]">
                            <flux:menu.radio.group>
                                <div class="p-0 text-sm font-normal">
                                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                        <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                            <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                                {{ auth()->user()->initials() }}
                                            </span>
                                        </span>

                                        <div class="grid flex-1 text-start text-sm leading-tight">
                                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                        </div>
                                    </div>
                                </div>
                            </flux:menu.radio.group>

                            <flux:menu.separator />

                            <flux:menu.radio.group>
                                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                            </flux:menu.radio.group>

                            <flux:menu.separator />

                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                    {{ __('Log Out') }}
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </aside>

        <!-- Mobile header -->
        <header class="lg:hidden flex items-center gap-3 px-4 py-3 border-b border-zinc-200/60 dark:border-zinc-700/60">
            <button
                type="button"
                class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-800/5 dark:text-zinc-300 dark:hover:bg-white/10"
                @click="mobileOpen = true"
                aria-label="Abrir menú"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                    <x-app-logo />
                </a>
            </div>
            <div class="ml-auto">
                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </header>

        <main class="lg:ml-72">
            {{ $slot }}
        </main>

        @fluxScripts
    </body>
</html>
