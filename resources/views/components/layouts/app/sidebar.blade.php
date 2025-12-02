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

        <flux:sidebar
            sticky
            stashable
            class="border-e border-zinc-200 bg-zinc-50/80 backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/80 lg:static lg:translate-x-0 fixed inset-y-0 z-40 w-72 transition-transform duration-200"
            x-bind:class="mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div class="flex items-center justify-between lg:justify-start">
                <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                    <x-app-logo />
                </a>
                <button
                    type="button"
                    class="lg:hidden rounded-lg p-2 text-zinc-500 hover:bg-zinc-800/5 dark:text-zinc-400 dark:hover:bg-white/10"
                    @click="mobileOpen = false"
                    aria-label="Cerrar menú"
                >
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>

            <flux:navlist variant="flush" class="mt-4 space-y-1">
                <flux:navlist.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navlist.item>
                <flux:navlist.item icon="layout-grid" :href="route('wallets.index')" :current="request()->routeIs('wallets.*')" wire:navigate>
                    Bolsillos
                </flux:navlist.item>
                <flux:navlist.item icon="folder-git-2" :href="route('budgets.index')" :current="request()->routeIs('budgets.*')" wire:navigate>
                    Presupuestos
                </flux:navlist.item>
                <flux:navlist.item icon="tag" :href="route('categories.index')" :current="request()->routeIs('categories.*')" wire:navigate>
                    Categorías
                </flux:navlist.item>
                <flux:navlist.item icon="layout-grid" :href="route('recurrences.index')" :current="request()->routeIs('recurrences.*')" wire:navigate>
                    Recurrencias
                </flux:navlist.item>
                <flux:navlist.item icon="layout-grid" :href="route('transactions.index')" :current="request()->routeIs('transactions.*')" wire:navigate>
                    Transacciones
                </flux:navlist.item>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist>

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
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
        </flux:sidebar>

        <!-- Mobile Header -->
        <flux:header class="lg:hidden">
            <button
                type="button"
                class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-800/5 dark:text-zinc-400 dark:hover:bg-white/10"
                @click="mobileOpen = true"
                aria-label="Abrir menú"
            >
                <flux:icon name="bars-2" class="size-5" />
            </button>

            <flux:spacer />

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
        </flux:header>

        {{ $slot }}

        @fluxScripts

    </body>
</html>
