@props(['productId', 'productName'])

@php
    $lists = \App\Models\ShoppingList::where('user_id', auth()->id())
        ->where('status', 'active')
        ->orderBy('created_at', 'desc')
        ->get();
@endphp

<div class="relative inline-block" x-data="{ open: false }">
    <button 
        @click="open = !open" 
        type="button" 
        class="inline-flex items-center gap-1 rounded-lg border border-emerald-500 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 transition"
        title="Agregar a lista"
    >
        ➕ Lista
    </button>

    <div 
        x-show="open" 
        @click.away="open = false"
        x-transition
        class="absolute right-0 mt-2 w-64 rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900 z-10"
        style="display: none;"
    >
        <div class="p-3">
            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">
                Agregar "{{ \Str::limit($productName, 25) }}" a:
            </p>
            
            @forelse($lists as $list)
                <button 
                    type="button"
                    onclick="addToList({{ $productId }}, {{ $list->id }}, '{{ addslashes($productName) }}')"
                    class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition flex items-center justify-between group"
                >
                    <span class="flex-1">{{ $list->name }}</span>
                    <span class="text-xs text-gray-400 group-hover:text-emerald-600">{{ $list->items->count() }} items</span>
                </button>
            @empty
                <p class="text-xs text-gray-500 p-2">No hay listas activas</p>
                <a href="{{ route('food.shopping-list.all') }}" class="block text-center mt-2 text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                    + Crear lista
                </a>
            @endforelse
        </div>
    </div>
</div>

@once
    @push('scripts')
    <script>
        async function addToList(productId, listId, productName) {
            try {
                const res = await fetch('/food/shopping-list/items/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        list_id: listId,
                        product_id: productId,
                        name: productName,
                        qty_to_buy_base: 1,
                    }),
                });
                
                if (res.ok) {
                    // Mostrar notificación temporal
                    const notification = document.createElement('div');
                    notification.className = 'fixed bottom-4 right-4 bg-emerald-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 animate-fade-in';
                    notification.innerHTML = `✅ "${productName}" agregado a la lista`;
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
                } else {
                    alert('Error al agregar a la lista');
                }
            } catch (err) {
                console.error(err);
                alert('Error al agregar a la lista');
            }
        }
    </script>

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
    @endpush
@endonce
