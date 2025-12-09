@php
    $menus = [
        [
            'group' => 'Finanzas',
            'links' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => '📊'],
                ['label' => 'Bolsillos', 'route' => 'wallets.index', 'icon' => '💰'],
                ['label' => 'Presupuestos', 'route' => 'budgets.index', 'icon' => '💵'],
                ['label' => 'Categorías', 'route' => 'categories.index', 'icon' => '🏷️'],
                ['label' => 'Recurrencias', 'route' => 'recurrences.index', 'icon' => '🔄'],
                ['label' => 'Transacciones', 'route' => 'transactions.index', 'icon' => '💳'],
            ],
        ],
        [
            'group' => 'Alimentos',
            'links' => [
                ['label' => 'Inventario', 'route' => 'food.inventory.index', 'icon' => '📦'],
                ['label' => 'Ubicaciones', 'route' => 'food.locations.index', 'icon' => '📍'],
                ['label' => 'Productos', 'route' => 'food.products.index', 'icon' => '🥫'],
                ['label' => 'Mis Listas', 'route' => 'food.shopping-list.all', 'icon' => '📋'],
                ['label' => 'Compras', 'route' => 'food.purchases.index', 'icon' => '🛒'],
            ],
        ],
        [
            'group' => 'Familia',
            'links' => [
                ['label' => 'Mi Núcleo Familiar', 'route' => 'family.index', 'icon' => '👨‍👩‍👧‍👦'],
            ],
        ],
    ];
@endphp

<nav id="primary-navigation" class="fixed inset-x-0 top-0 z-40 border-b border-gray-200 bg-white/90 text-gray-900 backdrop-blur-md dark:border-gray-800 dark:bg-neutral-950/90 dark:text-gray-100">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-3">
        <a href="{{ route('dashboard') }}" class="group flex flex-shrink-0 items-center gap-2.5" aria-label="{{ config('app.name') }}">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-gray-200 transition group-hover:ring-emerald-300 dark:bg-neutral-900 dark:ring-neutral-700">
                <x-app-logo-icon class="h-7 w-7" />
            </span>
            <span class="hidden text-base font-semibold text-gray-900 transition group-hover:text-emerald-600 sm:block dark:text-gray-100">
                {{ config('app.name') }}
            </span>
        </a>

        <div id="primary-menu-desktop" class="hidden flex-1 items-center justify-center lg:flex">
            <ul class="flex items-center gap-1">
                @foreach ($menus as $menu)
                    <li class="group relative">
                        <button type="button" class="flex h-9 items-center gap-1.5 rounded-lg px-3 text-sm font-medium text-gray-700 transition hover:bg-emerald-50 hover:text-emerald-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 dark:text-gray-200 dark:hover:bg-emerald-900/20 dark:hover:text-emerald-300">
                            <span>{{ $menu['group'] }}</span>
                            <svg class="h-3.5 w-3.5 text-gray-400 transition group-hover:text-emerald-500 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="pointer-events-none invisible absolute left-0 top-full z-50 mt-1 w-56 origin-top-left scale-95 rounded-lg border border-gray-200/80 bg-white p-2 text-sm opacity-0 shadow-xl transition-all duration-150 group-hover:pointer-events-auto group-hover:visible group-hover:scale-100 group-hover:opacity-100 dark:border-neutral-800 dark:bg-neutral-900">
                            <p class="mb-1.5 px-3 pt-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $menu['group'] }}</p>
                            <ul class="space-y-0.5">
                                @foreach ($menu['links'] as $link)
                                    @php $active = request()->routeIs(\Illuminate\Support\Str::before($link['route'], '.') . '*'); @endphp
                                    <li>
                                        <a
                                            href="{{ route($link['route']) }}"
                                            wire:navigate
                                            class="flex items-center gap-2.5 rounded-lg bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-emerald-50 hover:text-emerald-700 dark:bg-neutral-800/50 dark:text-gray-200 dark:hover:bg-emerald-900/20 dark:hover:text-emerald-300"
                                            aria-current="{{ $active ? 'page' : 'false' }}"
                                        >
                                            <span class="flex h-5 w-5 items-center justify-center text-base leading-none">{{ $link['icon'] }}</span>
                                            <span>{{ $link['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="flex flex-shrink-0 items-center gap-2">
            <button
                id="theme-toggle"
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200/70 text-gray-600 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 dark:border-neutral-700 dark:text-gray-200 dark:hover:bg-emerald-900/20"
            >
                <svg id="theme-toggle-dark-icon" class="hidden h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                <svg id="theme-toggle-light-icon" class="hidden h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"/></svg>
            </button>

            @auth
                <div class="relative">
                    <button id="nav-user-menu-button" type="button" class="flex h-9 items-center gap-2 rounded-lg border border-gray-200/70 px-2.5 text-sm font-medium text-gray-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 dark:border-neutral-700 dark:text-gray-200 dark:hover:bg-emerald-900/20 dark:hover:text-emerald-300">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-xs font-semibold text-white">
                            {{ auth()->user()->initials() }}
                        </span>
                        <span class="hidden xl:block">{{ auth()->user()->name }}</span>
                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="nav-user-menu-panel" class="absolute right-0 mt-2 hidden w-56 rounded-lg border border-gray-200/80 bg-white p-2 text-sm shadow-xl dark:border-neutral-800 dark:bg-neutral-900">
                        <div class="rounded-lg bg-gray-50 px-3 py-2 dark:bg-neutral-800/60">
                            <p class="font-semibold text-gray-900 dark:text-gray-100">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" wire:navigate class="mt-1.5 block rounded-lg px-3 py-2 text-gray-700 transition hover:bg-emerald-50 hover:text-emerald-700 dark:text-gray-200 dark:hover:bg-emerald-900/20">
                            Ajustes de perfil
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="mt-0.5">
                            @csrf
                            <button type="submit" class="w-full rounded-lg px-3 py-2 text-left font-semibold text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-900/20">
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            @endauth

            <button
                id="primary-menu-toggle"
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200/70 text-gray-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 lg:hidden dark:border-neutral-700 dark:text-gray-200 dark:hover:bg-emerald-900/20"
                aria-controls="primary-menu-mobile"
                aria-expanded="false"
            >
                <span class="sr-only">Alternar menú principal</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>

    <div id="primary-menu-mobile" class="hidden border-t border-gray-200/70 bg-white/95 text-sm lg:hidden dark:border-neutral-800 dark:bg-neutral-950/90">
        <div class="mx-auto w-full max-w-7xl flex-col gap-3 px-4 py-4">
            @foreach ($menus as $menu)
                <div class="rounded-lg border border-gray-200/80 bg-white p-3 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                    <p class="mb-2 px-1 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $menu['group'] }}</p>
                    <ul class="space-y-0.5">
                        @foreach ($menu['links'] as $link)
                            @php $active = request()->routeIs(\Illuminate\Support\Str::before($link['route'], '.') . '*'); @endphp
                            <li>
                                <a
                                    href="{{ route($link['route']) }}"
                                    wire:navigate
                                    class="flex items-center gap-2.5 rounded-lg bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-emerald-50 hover:text-emerald-700 dark:bg-neutral-800/50 dark:text-gray-200 dark:hover:bg-emerald-900/20 dark:hover:text-emerald-300"
                                    aria-current="{{ $active ? 'page' : 'false' }}"
                                >
                                    <span class="flex h-5 w-5 items-center justify-center text-base leading-none">{{ $link['icon'] }}</span>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const menuToggle = document.getElementById('primary-menu-toggle');
        const mobileMenu = document.getElementById('primary-menu-mobile');
        const userButton = document.getElementById('nav-user-menu-button');
        const userPanel = document.getElementById('nav-user-menu-panel');

        const closeMobileMenu = () => {
            mobileMenu?.classList.add('hidden');
            mobileMenu?.classList.remove('flex');
            menuToggle?.setAttribute('aria-expanded', 'false');
        };

        const openMobileMenu = () => {
            mobileMenu?.classList.remove('hidden');
            mobileMenu?.classList.add('flex');
            menuToggle?.setAttribute('aria-expanded', 'true');
        };

        menuToggle?.addEventListener('click', (event) => {
            event.stopPropagation();
            if (mobileMenu?.classList.contains('hidden')) {
                openMobileMenu();
            } else {
                closeMobileMenu();
            }
        });

        const closeUserMenu = () => userPanel?.classList.add('hidden');

        userButton?.addEventListener('click', (event) => {
            event.stopPropagation();
            userPanel?.classList.toggle('hidden');
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('#nav-user-menu-button') && !event.target.closest('#nav-user-menu-panel')) {
                closeUserMenu();
            }
            if (!event.target.closest('#primary-menu-toggle') && !event.target.closest('#primary-menu-mobile')) {
                closeMobileMenu();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeUserMenu();
                closeMobileMenu();
            }
        });
    });
</script>
