@php
    $user = auth()->user();

    $routeModules = [
        // Finanzas
        'wallets.index' => 'finances',
        'budgets.index' => 'finances',
        'categories.index' => 'finances',
        'recurrences.index' => 'finances',
        'transactions.index' => 'finances',

        // Alimentos
        'food.inventory.index' => 'food',
        'food.locations.index' => 'food',
        'food.products.index' => 'food',
        'food.purchases.index' => 'food',

        // Shopping
        'food.shopping-list.all' => 'shopping',

        // Familia (sin control por módulo)
        'family.index' => null,
        'dashboard' => null,
    ];

    $menus = [
        [
            'group' => 'Finanzas',
            'links' => [
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

    $menus = collect($menus)
        ->map(function (array $menu) use ($routeModules, $user) {
            $menu['links'] = collect($menu['links'])
                ->filter(function (array $link) use ($routeModules, $user) {
                    $module = $routeModules[$link['route']] ?? null;
                    if (!$module) {
                        return true;
                    }
                    return $user?->canManageModule($module) ?? false;
                })
                ->values()
                ->all();

            return $menu;
        })
        ->filter(fn (array $menu) => !empty($menu['links']))
        ->values()
        ->all();

    $primaryLinks = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => '📊'],
    ];

    $isActiveRoute = function (string $routeName): bool {
        return request()->routeIs(\Illuminate\Support\Str::before($routeName, '.') . '*');
    };
@endphp

<div class="flex h-full flex-col">
    <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain touch-pan-y [-webkit-overflow-scrolling:touch] p-4 flex flex-col">
        <div class="hidden lg:flex items-center justify-between gap-3">
            <a href="{{ route('dashboard') }}" class="group flex items-center gap-2.5" aria-label="{{ config('app.name') }}">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-neutral-primary-soft shadow-sm ring-1 ring-light transition duration-150 motion-reduce:transition-none group-hover:ring-fg-brand">
                    <x-app-logo-icon class="h-7 w-7" />
                </span>
                <span class="text-base font-semibold text-heading transition duration-150 motion-reduce:transition-none group-hover:text-fg-brand">
                    {{ config('app.name') }}
                </span>
            </a>

            <button
                data-theme-toggle
                type="button"
                class="hidden lg:inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200/70 text-gray-600 transition duration-150 motion-reduce:transition-none hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 active:scale-[0.98] dark:border-neutral-700 dark:text-gray-200 dark:hover:bg-emerald-900/20"
                aria-label="Alternar tema"
            >
                <svg data-theme-icon="dark" class="hidden h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8 0 1010.586 10.586z"/></svg>
                <svg data-theme-icon="light" class="hidden h-5 w-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"/></svg>
            </button>
        </div>

        <nav aria-label="Navegación principal" class="mt-4 space-y-4">
        <div class="rounded-lg border border-light bg-neutral-primary-soft p-3 shadow-sm">
            <p class="mb-2 px-1 text-xs font-semibold uppercase tracking-wide text-body-subtle">Principal</p>
            <ul class="space-y-0.5">
                @foreach ($primaryLinks as $link)
                    @php $active = $isActiveRoute($link['route']); @endphp
                    <li>
                        <a
                            href="{{ route($link['route']) }}"
                            wire:navigate
                            class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition duration-150 motion-reduce:transition-none focus:outline-none focus-visible:ring-2 focus-visible:ring-default active:scale-[0.99] {{ $active ? 'bg-neutral-secondary-soft text-fg-brand' : 'bg-neutral-primary-soft text-body hover:bg-neutral-secondary-soft hover:text-heading' }}"
                            aria-current="{{ $active ? 'page' : 'false' }}"
                        >
                            <span class="flex h-5 w-5 items-center justify-center text-base leading-none">{{ $link['icon'] }}</span>
                            <span>{{ $link['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        @foreach ($menus as $menu)
            @php
                $hasActive = collect($menu['links'])->contains(fn (array $link) => $isActiveRoute($link['route']));
                $panelId = 'sidebar-group-' . \Illuminate\Support\Str::slug($menu['group']);
            @endphp
            <details class="group rounded-lg border border-light bg-neutral-primary-soft shadow-sm" {{ $hasActive ? 'open' : '' }}>
                <summary
                    class="flex cursor-pointer list-none items-center justify-between gap-2 rounded-lg px-4 py-3 text-xs font-semibold uppercase tracking-wide text-body transition duration-150 motion-reduce:transition-none hover:bg-neutral-secondary-soft hover:text-heading focus:outline-none focus-visible:ring-2 focus-visible:ring-default active:scale-[0.99]"
                    aria-controls="{{ $panelId }}"
                >
                    <span>{{ $menu['group'] }}</span>
                    <svg class="h-4 w-4 text-body-subtle transition group-open:rotate-180 group-hover:text-fg-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                    </svg>
                </summary>
                <div id="{{ $panelId }}" class="px-3 pb-3">
                    <ul class="space-y-0.5">
                        @foreach ($menu['links'] as $link)
                            @php $active = $isActiveRoute($link['route']); @endphp
                            <li>
                                <a
                                    href="{{ route($link['route']) }}"
                                    wire:navigate
                                        class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition duration-150 motion-reduce:transition-none active:scale-[0.99] {{ $active ? 'bg-neutral-secondary-soft text-fg-brand' : 'bg-neutral-primary-soft text-body hover:bg-neutral-secondary-soft hover:text-heading' }}"
                                    aria-current="{{ $active ? 'page' : 'false' }}"
                                >
                                    <span class="flex h-5 w-5 items-center justify-center text-base leading-none">{{ $link['icon'] }}</span>
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </details>
        @endforeach
    </nav>

    </div>

    @auth
        <div class="p-4 pt-0">
            <div class="rounded-lg border border-light bg-neutral-primary-soft p-3 text-sm shadow-sm flex flex-col">
            <div class="flex items-center gap-3 rounded-lg bg-neutral-primary-soft px-3 py-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-600 text-xs font-semibold text-white">
                    {{ auth()->user()->initials() }}
                </span>
                <div class="min-w-0">
                    <p class="truncate font-semibold text-heading">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-body-subtle">{{ auth()->user()->email }}</p>
                </div>
            </div>

            <a href="{{ route('profile.edit') }}" wire:navigate class="mt-2 block rounded-lg px-3 py-2 text-body transition hover:bg-neutral-secondary-soft hover:text-heading">
                Ajustes de perfil
            </a>

            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="w-full rounded-lg px-3 py-2 text-left font-semibold text-rose-600 transition duration-150 motion-reduce:transition-none hover:bg-rose-50 active:scale-[0.99] dark:text-rose-400 dark:hover:bg-rose-900/20">
                    Cerrar sesión
                </button>
            </form>
            </div>
        </div>
    @endauth
</div>
