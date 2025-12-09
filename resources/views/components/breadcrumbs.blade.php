<nav class="flex" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-body hover:text-fg-brand">
                <svg class="w-4 h-4 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5" />
                </svg>
                Inicio
            </a>
        </li>

        @php
            $currentRoute = request()->route()->getName();
            $isFood = str_starts_with($currentRoute, 'food.');

            $breadcrumbs = [];

            if ($isFood) {
                $breadcrumbs[] = ['label' => 'Alimentos', 'route' => 'food.products.index', 'active' => false];

                if (str_contains($currentRoute, 'shopping-list')) {
                    $breadcrumbs[] = ['label' => 'Listas de Compras', 'route' => null, 'active' => true];
                } elseif (str_contains($currentRoute, 'purchases')) {
                    $breadcrumbs[] = ['label' => 'Compras', 'route' => null, 'active' => true];
                } elseif (str_contains($currentRoute, 'inventory')) {
                    $breadcrumbs[] = ['label' => 'Inventario', 'route' => null, 'active' => true];
                } elseif (str_contains($currentRoute, 'locations')) {
                    $breadcrumbs[] = ['label' => 'Ubicaciones', 'route' => null, 'active' => true];
                } elseif (str_contains($currentRoute, 'types')) {
                    $breadcrumbs[] = ['label' => 'Tipos', 'route' => null, 'active' => true];
                } elseif (str_contains($currentRoute, 'products')) {
                    if (str_contains($currentRoute, 'create')) {
                        $breadcrumbs[] = ['label' => 'Productos', 'route' => 'food.products.index', 'active' => false];
                        $breadcrumbs[] = ['label' => 'Nuevo Producto', 'route' => null, 'active' => true];
                    } elseif (str_contains($currentRoute, 'edit')) {
                        $breadcrumbs[] = ['label' => 'Productos', 'route' => 'food.products.index', 'active' => false];
                        $breadcrumbs[] = ['label' => 'Editar', 'route' => null, 'active' => true];
                    } else {
                        $breadcrumbs[] = ['label' => 'Productos', 'route' => null, 'active' => true];
                    }
                }
            }
        @endphp

        @foreach($breadcrumbs as $breadcrumb)
            <li @if($breadcrumb['active']) aria-current="page" @endif>
                <div class="flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5 rtl:rotate-180 text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7" />
                    </svg>
                    @if($breadcrumb['active'] || !$breadcrumb['route'])
                        <span class="inline-flex items-center text-sm font-medium text-body-subtle">{{ $breadcrumb['label'] }}</span>
                    @else
                        <a href="{{ route($breadcrumb['route']) }}" class="inline-flex items-center text-sm font-medium text-body hover:text-fg-brand">{{ $breadcrumb['label'] }}</a>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>
