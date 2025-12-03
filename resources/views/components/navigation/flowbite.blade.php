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

<nav class="bg-neutral-primary fixed w-full z-20 top-0 start-0 border-b border-default">
    <div class="mx-auto flex w-full max-w-screen-xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <div class="flex items-center gap-3">
            <a
                href="{{ route('dashboard') }}"
                class="group flex items-center gap-3 rounded-full px-2 py-1 transition hover:-translate-y-0.5"
                aria-label="{{ config('app.name') }}"
            >
                <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white shadow-md ring-1 ring-white/20 transition group-hover:ring-2 group-hover:ring-white/50">
                    <x-app-logo-icon class="h-10 w-10" />
                </span>
                <span class="text-lg font-semibold text-heading transition group-hover:text-fg-brand dark:text-white">
                    {{ config('app.name') }}
                </span>
            </a>
        </div>

        <div class="flex items-center gap-3 md:order-3">
            <button
                id="theme-toggle"
                type="button"
                class="inline-flex items-center justify-center rounded-lg p-2 text-heading hover:bg-neutral-secondary-soft hover:text-fg-brand focus:outline-none focus:ring-2 focus:ring-default"
            >
                <svg id="theme-toggle-dark-icon" class="hidden h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                <svg id="theme-toggle-light-icon" class="hidden h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"/></svg>
            </button>

            <button
                data-collapse-toggle="primary-menu"
                type="button"
                class="inline-flex items-center justify-center rounded-lg p-2 text-heading hover:bg-neutral-secondary-soft hover:text-fg-brand focus:outline-none focus:ring-2 focus:ring-default md:hidden"
                aria-controls="primary-menu"
                aria-expanded="false"
            >
                <span class="sr-only">Abrir menú principal</span>
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            @auth
                <div class="relative">
                    <button
                        id="user-menu-button"
                        data-dropdown-toggle="user-menu"
                        class="flex items-center gap-2 rounded-full bg-neutral-primary-soft px-2 py-1 text-heading hover:text-fg-brand focus:outline-none focus:ring-2 focus:ring-default"
                        type="button"
                    >
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-600 text-sm font-semibold text-white">
                            {{ auth()->user()->initials() }}
                        </span>
                        <span class="hidden text-sm font-medium md:block">{{ auth()->user()->name }}</span>
                        <svg class="h-4 w-4 text-heading" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        id="user-menu"
                        class="z-50 hidden w-48 divide-y divide-default overflow-hidden rounded-lg border border-default bg-white text-sm shadow-lg dark:bg-neutral-900"
                    >
                        <div class="px-4 py-3">
                            <p class="truncate font-semibold text-heading">{{ auth()->user()->name }}</p>
                            <p class="truncate text-body">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="py-1">
                        <a href="{{ route('profile.edit') }}" wire:navigate class="block px-4 py-2 hover:bg-neutral-100 dark:hover:bg-neutral-800">
                                Ajustes de perfil
                            </a>
                        </div>
                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-red-600 hover:bg-neutral-100 dark:hover:bg-neutral-800">
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endauth
        </div>

        <div id="primary-menu" class="hidden w-full md:block md:w-auto md:order-2">
            <ul class="mt-2 flex flex-col rounded-lg bg-neutral-primary-soft p-3 font-medium shadow-sm md:mt-0 md:flex-row md:items-center md:gap-6 md:bg-transparent md:p-0 md:shadow-none">
                @foreach ($links as $link)
                    @php $active = request()->routeIs(\Illuminate\Support\Str::before($link['route'], '.') . '*'); @endphp
                    <li>
                        <a
                            href="{{ route($link['route']) }}"
                            wire:navigate
                            class="block rounded-md px-3 py-2 transition {{ $active ? 'text-fg-brand font-semibold' : 'text-heading' }} hover:text-fg-brand hover:bg-neutral-secondary-soft md:hover:bg-transparent"
                            aria-current="{{ $active ? 'page' : 'false' }}"
                        >
                            {{ $link['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>
