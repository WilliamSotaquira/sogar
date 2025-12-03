<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FoodBarcode;
use App\Models\FoodProduct;
use Illuminate\Http\Request;

class FoodScanController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        $userId = $request->user()->id;
        $code = $data['code'];
        $barcode = FoodBarcode::with(['product.type', 'product.defaultLocation'])
            ->where('code', $code)
            ->first();

        // Buscar también en el campo barcode del producto principal
        if (!$barcode) {
            $product = FoodProduct::with(['type', 'defaultLocation'])
                ->where('user_id', $userId)
                ->where('barcode', $code)
                ->first();
            if ($product) {
                return response()->json([
                    'found' => true,
                    'product' => $product,
                ]);
            }
        }

        if ($barcode && $barcode->product->user_id === $userId) {
            return response()->json([
                'found' => true,
                'product' => $barcode->product,
            ]);
        }

        if (!empty($data['name'])) {
            $product = FoodProduct::firstOrCreate(
                ['user_id' => $userId, 'name' => $data['name']],
                ['unit_base' => 'unit', 'unit_size' => 1]
            );

            FoodBarcode::create([
                'product_id' => $product->id,
                'code' => $data['code'],
                'kind' => 'scan',
            ]);

            return response()->json([
                'found' => false,
                'created' => true,
                'product' => $product,
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'No encontrado',
        ], 404);
    }
}
