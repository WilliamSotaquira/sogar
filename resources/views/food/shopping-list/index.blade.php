@php
    $label = 'block text-sm font-medium text-gray-700 dark:text-gray-300';
    $input = 'mt-1 block h-11 w-full rounded-xl border border-gray-200 bg-white px-3 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100';
    $btnPrimary = 'inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1';
    $btnSecondary = 'inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700';
    
    $budgets = \App\Models\Budget::where('user_id', auth()->id())
        ->with('category')
        ->where('month', now()->month)
        ->where('year', now()->year)
        ->get();
    
    $types = \App\Models\FoodType::where('user_id', auth()->id())->where('is_active', true)->orderBy('sort_order')->get();
    $locations = \App\Models\FoodLocation::where('user_id', auth()->id())->orderBy('sort_order')->get();
@endphp

<x-layouts.app :title="__('Lista de compra')">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        {{-- Hero Panel --}}
        <div class="hero-panel p-6">
            <div class="hero-panel-content flex flex-col gap-2 md:flex-row md:items-center md:justify-between text-white">
                <div>
                    <p class="text-sm uppercase tracking-wide font-semibold">Compras inteligentes</p>
                    <h1 class="text-3xl font-bold">Lista de Compra</h1>
                    <p class="text-sm text-white/80">Vinculada a presupuesto, con alertas y escaneo inteligente.</p>
                </div>
                @if($list && $list->budget)
                    <div class="rounded-xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                        <p class="text-xs text-white/80">Presupuesto: {{ $list->budget->category->name }}</p>
                        <p class="text-xl font-bold">${{ number_format($list->budget->amount, 0) }}</p>
                        @if($list->actual_total > 0)
                            <p class="text-xs text-white/90 mt-1">Gastado: ${{ number_format($list->actual_total, 0) }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Status Messages --}}
        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-900/30 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-3">
            {{-- Main Content --}}
            <div class="md:col-span-2 space-y-4">
                {{-- Generar Lista con Presupuesto --}}
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50 mb-3">
                        @if($list)
                            {{ $list->name }}
                            <span class="ml-2 text-xs font-normal text-gray-500">
                                (Generada: {{ $list->generated_at?->format('d/m/Y H:i') }})
                            </span>
                        @else
                            Generar Nueva Lista
                        @endif
                    </h3>

                    @if(!$list)
                        <form method="POST" action="{{ route('food.shopping-list.generate') }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="{{ $label }}">Nombre de la lista</label>
                                <input type="text" name="name" placeholder="Ej: Compra semanal" class="{{ $input }}" value="{{ old('name', 'Compra ' . now()->format('d/m')) }}">
                            </div>

                            <div>
                                <label class="{{ $label }}">
                                    Presupuesto <span class="text-rose-500">*</span>
                                </label>
                                <select name="budget_id" required class="{{ $input }}">
                                    <option value="">Selecciona un presupuesto...</option>
                                    @foreach($budgets as $budget)
                                        <option value="{{ $budget->id }}">
                                            {{ $budget->category->name }} - ${{ number_format($budget->amount, 0) }} 
                                            ({{ now()->monthName }} {{ now()->year }})
                                        </option>
                                    @endforeach
                                </select>
                                @if($budgets->isEmpty())
                                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                        ⚠️ Debes crear un presupuesto primero en 
                                        <a href="{{ route('budgets.index') }}" class="underline">Presupuestos</a>
                                    </p>
                                @endif
                            </div>

                            <div class="grid gap-3 md:grid-cols-3">
                                <div>
                                    <label class="{{ $label }}">Fecha estimada</label>
                                    <input type="date" name="expected_purchase_on" value="{{ now()->addDays(3)->format('Y-m-d') }}" class="{{ $input }}">
                                </div>
                                <div>
                                    <label class="{{ $label }}">Horizonte (días)</label>
                                    <input type="number" name="horizon_days" min="1" max="30" value="7" class="{{ $input }}">
                                </div>
                                <div>
                                    <label class="{{ $label }}">Personas</label>
                                    <input type="number" name="people_count" min="1" max="10" value="3" class="{{ $input }}">
                                </div>
                            </div>

                            <div class="flex justify-end pt-2">
                                <button type="submit" class="{{ $btnPrimary }}" @if($budgets->isEmpty()) disabled @endif>
                                    🚀 Generar Lista
                                </button>
                            </div>
                        </form>
                    @endif
                </div>

                {{-- Items de la Lista --}}
                @if($list)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50">
                                Items ({{ $list->items->where('is_checked', true)->count() }}/{{ $list->items->count() }} ✓)
                            </h3>
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                Total: ${{ number_format($list->actual_total ?: $list->estimated_budget, 0) }}
                            </div>
                        </div>

                        {{-- Agregar Item --}}
                        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 mb-4">
                            <div class="grid gap-2 md:grid-cols-5 items-center">
                                <input type="text" id="search-product-input" placeholder="Buscar producto..." class="h-10 rounded-lg border border-gray-200 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 md:col-span-2">
                                <input type="number" id="quick-qty" placeholder="Cant." value="1" min="1" class="h-10 rounded-lg border border-gray-200 px-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                <button type="button" id="scan-product-btn" class="h-10 rounded-lg border border-emerald-500 bg-emerald-50 px-3 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                    📷 Escanear
                                </button>
                                <button type="button" id="quick-add-btn" class="h-10 rounded-lg bg-emerald-600 px-3 text-sm font-semibold text-white hover:bg-emerald-700">
                                    + Agregar
                                </button>
                            </div>
                            <p id="search-status" class="text-xs text-gray-500 mt-2"></p>
                        </div>

                        {{-- Lista de Items --}}
                        <div id="items-container" class="space-y-2 max-h-[600px] overflow-y-auto">
                            @forelse($list->items as $item)
                                <div class="rounded-lg border {{ $item->is_checked ? 'border-emerald-200 bg-emerald-50/30 dark:border-emerald-800 dark:bg-emerald-900/10' : 'border-gray-100 dark:border-gray-800' }} p-3">
                                    <div class="flex items-start gap-3">
                                        {{-- Checkbox --}}
                                        <button type="button" 
                                                onclick="toggleItem({{ $list->id }}, {{ $item->id }}, {{ $item->is_checked ? 0 : 1 }})"
                                                class="mt-1 h-6 w-6 flex-shrink-0 rounded-md border transition {{ $item->is_checked ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-gray-300 dark:border-gray-700 hover:border-emerald-400' }}">
                                            @if($item->is_checked)
                                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                        </button>

                                        {{-- Item Info --}}
                                        <div class="flex-1 space-y-1">
                                            <div class="flex items-center justify-between gap-3">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 {{ $item->is_checked ? 'line-through' : '' }}">
                                                    {{ $item->name }}
                                                    @if($item->product)
                                                        <span class="ml-1 text-xs text-gray-500">(en catálogo)</span>
                                                    @else
                                                        <span class="ml-1 text-xs text-amber-600">⚠️ no catalogado</span>
                                                    @endif
                                                </p>
                                                @if($item->low_stock_alert)
                                                    <span class="text-xs rounded-full px-2 py-1 bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-100">
                                                        ⚠️ Stock bajo
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                Cantidad: {{ $item->qty_to_buy_base }} {{ $item->unit_base }}
                                                · Stock actual: {{ $item->qty_current_base }}
                                            </p>
                                            <div class="flex items-center gap-3 text-xs">
                                                <span class="text-gray-600 dark:text-gray-300">
                                                    Est: ${{ number_format($item->estimated_price, 2) }}
                                                </span>
                                                @if($item->actual_price)
                                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                                        Real: ${{ number_format($item->actual_price, 2) }}
                                                    </span>
                                                    @if($item->vendor_name)
                                                        <span class="text-gray-500">en {{ $item->vendor_name }}</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Actions --}}
                                        @if(!$item->is_checked)
                                            <div class="flex gap-2">
                                                @if(!$item->product)
                                                    <button type="button" 
                                                            onclick="showCreateProductModal('{{ $item->name }}', {{ $item->id }})"
                                                            class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 px-2 py-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                                            title="Crear en catálogo">
                                                        📝 Crear
                                                    </button>
                                                @endif
                                                <button type="button" 
                                                        onclick="showPriceModal({{ $list->id }}, {{ $item->id }}, '{{ $item->name }}', {{ $item->qty_to_buy_base }})"
                                                        class="text-xs font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 px-2 py-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                                    💰 Precio
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500 text-center py-8">La lista está vacía. Agrega productos arriba.</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">
                {{-- Resumen --}}
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50 mb-3">Resumen</h3>
                    @if($list)
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-300">Estimado:</span>
                                <span class="font-semibold">${{ number_format($list->estimated_budget, 0) }}</span>
                            </div>
                            @if($list->actual_total > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-300">Real:</span>
                                    <span class="font-semibold text-emerald-600">${{ number_format($list->actual_total, 0) }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-600 dark:text-gray-300">Diferencia:</span>
                                    <span class="font-semibold {{ $list->actual_total > $list->estimated_budget ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ $list->actual_total > $list->estimated_budget ? '+' : '' }}${{ number_format($list->actual_total - $list->estimated_budget, 0) }}
                                    </span>
                                </div>
                            @endif
                            @if($list->budget)
                                <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
                                    <span class="text-gray-600 dark:text-gray-300">Presupuesto disponible:</span>
                                    <span class="font-semibold">${{ number_format($list->budget->amount - $list->actual_total, 0) }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Genera una lista para ver el resumen.</p>
                    @endif
                </div>

                {{-- Listas Recientes --}}
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-md font-semibold text-gray-900 dark:text-gray-50 mb-3">Listas recientes</h3>
                    <div class="space-y-2">
                        @forelse(($recentLists ?? collect())->take(5) as $recent)
                            <div class="flex justify-between items-center text-sm">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ Str::limit($recent->name, 20) }}</p>
                                    <p class="text-xs text-gray-500">{{ $recent->generated_at?->format('d/m/Y') }}</p>
                                </div>
                                <a href="{{ route('food.shopping-list.show', $recent) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                                    Ver
                                </a>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Sin listas anteriores.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Precio --}}
    <div id="price-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50" onclick="if(event.target===this) closePriceModal()">
        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 max-w-md w-full mx-4 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Registrar Precio</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4" id="modal-product-name"></p>
            <form id="price-form">
                <div class="space-y-3">
                    <div>
                        <label class="{{ $label }}">Precio Total Pagado</label>
                        <input type="number" step="0.01" id="modal-price" placeholder="0.00" class="{{ $input }}" required>
                    </div>
                    <div>
                        <label class="{{ $label }}">Tienda/Vendor</label>
                        <input type="text" id="modal-vendor" placeholder="Ej: Walmart, Soriana" class="{{ $input }}">
                    </div>
                </div>
                <div class="flex gap-2 mt-6">
                    <button type="button" onclick="closePriceModal()" class="{{ $btnSecondary }} flex-1">
                        Cancelar
                    </button>
                    <button type="submit" class="{{ $btnPrimary }} flex-1">
                        ✓ Marcar Comprado
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Crear Producto --}}
    <div id="create-product-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 overflow-y-auto" onclick="if(event.target===this) closeCreateProductModal()">
        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 max-w-2xl w-full mx-4 my-8 shadow-xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Crear Producto en Catálogo</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Este producto no existe en tu catálogo. Compléta los datos para crearlo:</p>
            
            <form id="create-product-form" class="space-y-4">
                <input type="hidden" id="create-item-id">
                
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="{{ $label }}">Nombre del Producto *</label>
                        <input type="text" id="create-name" required class="{{ $input }}">
                    </div>
                    
                    <div>
                        <label class="{{ $label }}">Marca</label>
                        <input type="text" id="create-brand" class="{{ $input }}">
                    </div>
                    
                    <div>
                        <label class="{{ $label }}">Código de Barras</label>
                        <input type="text" id="create-barcode" class="{{ $input }}">
                    </div>
                    
                    <div>
                        <label class="{{ $label }}">Tipo</label>
                        <select id="create-type" class="{{ $input }}">
                            <option value="">Selecciona...</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="{{ $label }}">Ubicación</label>
                        <select id="create-location" class="{{ $input }}">
                            <option value="">Selecciona...</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="{{ $label }}">Unidad Base</label>
                        <select id="create-unit-base" class="{{ $input }}">
                            <option value="unit">Unidad</option>
                            <option value="g">Gramos</option>
                            <option value="ml">Mililitros</option>
                            <option value="kg">Kilogramos</option>
                            <option value="l">Litros</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="{{ $label }}">Factor de Tamaño</label>
                        <input type="number" step="0.001" id="create-unit-size" value="1" class="{{ $input }}">
                    </div>
                    
                    <div>
                        <label class="{{ $label }}">Stock Mínimo</label>
                        <input type="number" step="0.1" id="create-min-stock" placeholder="Ej: 3" class="{{ $input }}">
                    </div>
                    
                    <div>
                        <label class="{{ $label }}">Vida Útil (días)</label>
                        <input type="number" id="create-shelf-life" placeholder="Ej: 7" class="{{ $input }}">
                    </div>
                </div>
                
                <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" onclick="closeCreateProductModal()" class="{{ $btnSecondary }} flex-1">
                        Cancelar
                    </button>
                    <button type="submit" class="{{ $btnPrimary }} flex-1">
                        ✓ Crear y Vincular
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const listId = {{ $list->id ?? 'null' }};
        let currentListId = listId;
        let currentItemId = null;
        let foundProducts = [];

        // Toggle item checked
        async function toggleItem(listId, itemId, isChecked) {
            try {
                const res = await fetch(`/food/shopping-list/${listId}/items/${itemId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ is_checked: isChecked }),
                });
                if (res.ok) {
                    location.reload();
                }
            } catch (err) {
                console.error(err);
            }
        }

        // Show price modal
        function showPriceModal(listId, itemId, productName, qty) {
            currentListId = listId;
            currentItemId = itemId;
            document.getElementById('modal-product-name').textContent = `${productName} (${qty} unidades)`;
            document.getElementById('price-modal').classList.remove('hidden');
            document.getElementById('modal-price').focus();
        }

        // Close price modal
        function closePriceModal() {
            document.getElementById('price-modal').classList.add('hidden');
            document.getElementById('price-form').reset();
        }

        // Show create product modal
        function showCreateProductModal(name, itemId) {
            document.getElementById('create-name').value = name;
            document.getElementById('create-item-id').value = itemId;
            document.getElementById('create-product-modal').classList.remove('hidden');
        }

        // Close create product modal
        function closeCreateProductModal() {
            document.getElementById('create-product-modal').classList.add('hidden');
            document.getElementById('create-product-form').reset();
        }

        // Submit price
        document.getElementById('price-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const price = document.getElementById('modal-price').value;
            const vendor = document.getElementById('modal-vendor').value;

            try {
                const res = await fetch(`/food/shopping-list/${currentListId}/items/${currentItemId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        is_checked: true,
                        actual_price: parseFloat(price),
                        vendor_name: vendor,
                    }),
                });
                if (res.ok) {
                    location.reload();
                } else {
                    alert('Error al guardar');
                }
            } catch (err) {
                console.error(err);
                alert('Error al guardar');
            }
        });

        // NUEVA: Autocompletar barcode
        let barcodeTimeout;
        document.getElementById('create-barcode')?.addEventListener('input', async (e) => {
            clearTimeout(barcodeTimeout);
            const code = e.target.value.trim();

            // Solo buscar si tiene al menos 8 caracteres (códigos de barras típicos)
            if (code.length < 8) {
                return;
            }

            // Crear indicador de búsqueda
            const statusSpan = document.createElement('span');
            statusSpan.id = 'barcode-status';
            statusSpan.className = 'text-xs text-blue-600 mt-1';
            statusSpan.textContent = '🔍 Buscando producto...';
            
            const existingStatus = document.getElementById('barcode-status');
            if (existingStatus) {
                existingStatus.remove();
            }
            e.target.parentElement.appendChild(statusSpan);

            barcodeTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`/food/barcode/${encodeURIComponent(code)}`, {
                        headers: {
                            'Authorization': `Bearer ${localStorage.getItem('api_token') || ''}`,
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        credentials: 'same-origin',
                    });

                    const data = await res.json();

                    if (data.found) {
                        // Autocompletar campos
                        const productData = data.data;

                        if (productData.name && !document.getElementById('create-name').value) {
                            document.getElementById('create-name').value = productData.name;
                        }

                        if (productData.brand) {
                            document.getElementById('create-brand').value = productData.brand;
                        }

                        if (productData.type_id) {
                            document.getElementById('create-type').value = productData.type_id;
                        }

                        if (productData.location_id) {
                            document.getElementById('create-location').value = productData.location_id;
                        }

                        if (productData.unit_base) {
                            document.getElementById('create-unit-base').value = productData.unit_base;
                        }

                        if (productData.unit_size) {
                            document.getElementById('create-unit-size').value = productData.unit_size;
                        }

                        if (productData.min_stock_qty) {
                            document.getElementById('create-min-stock').value = productData.min_stock_qty;
                        }

                        if (productData.shelf_life_days || productData.suggested_shelf_life) {
                            document.getElementById('create-shelf-life').value = productData.shelf_life_days || productData.suggested_shelf_life;
                        }

                        // Actualizar status
                        statusSpan.textContent = data.source === 'local' 
                            ? '✅ Datos cargados desde tu inventario'
                            : '✅ Datos cargados desde OpenFoodFacts';
                        statusSpan.className = 'text-xs text-emerald-600 mt-1';

                        // Si es producto local, avisar que ya existe
                        if (data.source === 'local') {
                            statusSpan.textContent += ' (Este producto ya existe en tu catálogo)';
                            statusSpan.className = 'text-xs text-amber-600 mt-1';
                        }

                    } else {
                        statusSpan.textContent = '⚠️ Código no encontrado. Completa datos manualmente.';
                        statusSpan.className = 'text-xs text-amber-600 mt-1';
                    }

                } catch (err) {
                    console.error(err);
                    statusSpan.textContent = '❌ Error al buscar. Verifica tu conexión.';
                    statusSpan.className = 'text-xs text-rose-600 mt-1';
                }

                // Remover status después de 5 segundos
                setTimeout(() => {
                    statusSpan?.remove();
                }, 5000);
            }, 800); // Esperar 800ms después de que deje de escribir
        });

        // Submit create product
        document.getElementById('create-product-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const itemId = document.getElementById('create-item-id').value;
            const name = document.getElementById('create-name').value;
            const brand = document.getElementById('create-brand').value;
            const barcode = document.getElementById('create-barcode').value;
            const typeId = document.getElementById('create-type').value;
            const locationId = document.getElementById('create-location').value;
            const unitBase = document.getElementById('create-unit-base').value;
            const unitSize = document.getElementById('create-unit-size').value;
            const minStock = document.getElementById('create-min-stock').value;
            const shelfLife = document.getElementById('create-shelf-life').value;

            try {
                const res = await fetch('{{ route('food.shopping-list.items.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        list_id: listId,
                        name,
                        qty_to_buy_base: 1,
                        create_product: true,
                        brand,
                        barcode,
                        type_id: typeId || null,
                        location_id: locationId || null,
                        unit_base: unitBase,
                        unit_size: parseFloat(unitSize),
                        min_stock_qty: minStock ? parseFloat(minStock) : null,
                        shelf_life_days: shelfLife ? parseInt(shelfLife) : null,
                    }),
                });

                if (res.ok) {
                    location.reload();
                } else {
                    alert('Error al crear producto');
                }
            } catch (err) {
                console.error(err);
                alert('Error al crear producto');
            }
        });

        // Quick add button
        document.getElementById('quick-add-btn')?.addEventListener('click', async () => {
            const name = document.getElementById('search-product-input').value.trim();
            const qty = document.getElementById('quick-qty').value;

            if (!name) {
                alert('Escribe el nombre del producto');
                return;
            }

            // Si hay un producto encontrado en la búsqueda
            const found = foundProducts.find(p => p.name.toLowerCase() === name.toLowerCase());

            if (found) {
                // Agregar producto existente
                await addItemToList(found.id, found.name, qty);
            } else {
                // Mostrar modal para crear producto
                showCreateProductModal(name, null);
            }
        });

        // Search products as you type
        let searchTimeout;
        document.getElementById('search-product-input')?.addEventListener('input', async (e) => {
            clearTimeout(searchTimeout);
            const term = e.target.value;
            const statusEl = document.getElementById('search-status');

            if (term.length < 2) {
                statusEl.textContent = '';
                foundProducts = [];
                return;
            }

            searchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`{{ route('food.shopping-list.items.search') }}?q=${encodeURIComponent(term)}`);
                    const data = await res.json();
                    foundProducts = data.data || [];

                    if (foundProducts.length > 0) {
                        const match = foundProducts[0];
                        statusEl.textContent = `✅ Encontrado: ${match.name} (Stock: ${match.stock})`;
                        statusEl.className = 'text-xs text-emerald-600 mt-2';
                    } else {
                        statusEl.textContent = `⚠️ "${term}" no existe en tu catálogo. Se creará al agregar.`;
                        statusEl.className = 'text-xs text-amber-600 mt-2';
                    }
                } catch (err) {
                    console.error(err);
                }
            }, 300);
        });

        // Add item to list helper
        async function addItemToList(productId, name, qty) {
            try {
                const res = await fetch('{{ route('food.shopping-list.items.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        list_id: listId,
                        product_id: productId,
                        name,
                        qty_to_buy_base: parseFloat(qty),
                    }),
                });

                if (res.ok) {
                    location.reload();
                } else {
                    alert('Error al agregar');
                }
            } catch (err) {
                console.error(err);
                alert('Error al agregar');
            }
        }
    </script>
    @endpush
</x-layouts.app>
