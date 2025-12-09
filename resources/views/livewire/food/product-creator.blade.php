<div>
    <form wire:submit="createProduct" class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="new_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nombre del producto *
                </label>
                <input
                    type="text"
                    id="new_name"
                    wire:model="name"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                    required>
                @error('name') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="new_brand" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Marca
                </label>
                <input
                    type="text"
                    id="new_brand"
                    wire:model="brand"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                @error('brand') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="new_unit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Unidad de medida *
                </label>
                <select
                    id="new_unit"
                    wire:model="unit_base"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                    required>
                    <option value="unidad">Unidad</option>
                    <option value="g">Gramos (g)</option>
                    <option value="kg">Kilogramos (kg)</option>
                    <option value="ml">Mililitros (ml)</option>
                    <option value="L">Litros (L)</option>
                    <option value="paquete">Paquete</option>
                    <option value="caja">Caja</option>
                </select>
                @error('unit_base') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="new_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tipo de producto
                </label>
                <select
                    id="new_type"
                    wire:model="type_id"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    <option value="">-- Sin tipo --</option>
                    @foreach($productTypes ?? [] as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('type_id') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="new_barcode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Código de barras
                </label>
                <input
                    type="text"
                    id="new_barcode"
                    wire:model="barcode"
                    class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                @error('barcode') <p class="text-xs text-rose-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex gap-2 pt-2">
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading wire:target="createProduct">⏳</span>
                <span wire:loading.remove wire:target="createProduct">Crear y asociar</span>
                <span wire:loading wire:target="createProduct">Creando...</span>
            </button>
            <button
                type="button"
                wire:click="$dispatch('close-modal'); resetForm()"
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
