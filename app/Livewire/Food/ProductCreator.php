<?php

namespace App\Livewire\Food;

use App\Models\FoodLocation;
use App\Models\FoodProduct;
use App\Models\FoodType;
use Livewire\Component;

class ProductCreator extends Component
{
    public $location;
    public $name = '';
    public $brand = '';
    public $unit_base = 'unidad';
    public $type_id = '';
    public $barcode = '';
    public $message = '';
    public $messageType = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'unit_base' => 'required|string|in:unidad,g,kg,ml,L,paquete,caja',
            'type_id' => 'nullable|exists:food_types,id',
            'barcode' => 'nullable|string|max:50',
        ];
    }

    public function mount(FoodLocation $location)
    {
        $this->location = $location;
    }

    public function createProduct()
    {
        $this->validate();

        $product = FoodProduct::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'brand' => $this->brand,
            'unit_base' => $this->unit_base,
            'type_id' => $this->type_id ?: null,
            'barcode' => $this->barcode,
            'default_location_id' => $this->location->id,
        ]);

        $this->message = '✓ Producto creado y asociado correctamente';
        $this->messageType = 'success';

        $this->dispatch('product-created', productId: $product->id);

        // Refrescar la vista padre
        $this->dispatch('$refresh');

        $this->resetForm();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->brand = '';
        $this->unit_base = 'unidad';
        $this->type_id = '';
        $this->barcode = '';
        $this->message = '';
        $this->messageType = '';
        $this->resetValidation();
    }

    public function render()
    {
        $productTypes = FoodType::where('user_id', auth()->id())
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.food.product-creator', [
            'productTypes' => $productTypes,
        ]);
    }
}
