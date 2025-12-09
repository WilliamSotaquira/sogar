<div>
    <form wire:submit="assignProduct" class="space-y-4">
        <div>
            <label for="product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Selecciona un producto existente
            </label>
            <select
                id="product_id"
                wire:model="selectedProduct"
                class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                required>
                <option value="">-- Elige un producto --</option>
                @foreach($allProducts as $prod)
                    <option value="{{ $prod->id }}">
                        {{ $prod->name }}{{ $prod->brand ? ' - ' . $prod->brand : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading wire:target="assignProduct">⏳</span>
                <span wire:loading.remove wire:target="assignProduct">Asociar a esta ubicación</span>
                <span wire:loading wire:target="assignProduct">Asociando...</span>
            </button>
            <button
                type="button"
                wire:click="$dispatch('close-modal')"
                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                Cancelar
            </button>
        </div>

        @if($message)
            <p class="text-sm font-medium {{ $messageType === 'success' ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                {{ $message }}
            </p>
        @endif
    </form>
</div>
