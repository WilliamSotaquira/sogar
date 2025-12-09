<?php

namespace App\Livewire\Food;

use App\Models\FoodLocation;
use App\Models\FoodProduct;
use Livewire\Component;

class ProductAssigner extends Component
{
    public $location;
    public $selectedProduct = '';
    public $message = '';
    public $messageType = '';

    public function mount(FoodLocation $location)
    {
        $this->location = $location;
    }

    public function assignProduct()
    {
        $this->validate([
            'selectedProduct' => 'required|exists:food_products,id',
        ]);

        $product = FoodProduct::where('user_id', auth()->id())
            ->findOrFail($this->selectedProduct);

        $product->update(['default_location_id' => $this->location->id]);

        $this->message = '✓ Producto asociado correctamente';
        $this->messageType = 'success';
        $this->selectedProduct = '';

        $this->dispatch('product-assigned');

        // Refrescar la página para actualizar la lista
        $this->dispatch('$refresh');
    }

    public function render()
    {
        $allProducts = auth()->user()->foodProducts()
            ->select('id', 'name', 'brand')
            ->orderBy('name')
            ->get();

        return view('livewire.food.product-assigner', [
            'allProducts' => $allProducts,
        ]);
    }
}
