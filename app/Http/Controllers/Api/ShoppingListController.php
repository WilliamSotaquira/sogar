<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShoppingList;
use App\Services\ShoppingListGenerator;
use Illuminate\Http\Request;

class ShoppingListController extends Controller
{
    public function active(Request $request)
    {
        $list = ShoppingList::with('items')
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->latest('generated_at')
            ->first();

        return response()->json(['data' => $list]);
    }

    public function generate(Request $request, ShoppingListGenerator $generator)
    {
        $data = $request->validate([
            'horizon_days' => 'nullable|integer|min:1|max:30',
            'people_count' => 'nullable|integer|min:1|max:10',
            'safety_factor' => 'nullable|numeric|min:1|max:2',
        ]);

        $list = $generator->generate(
            $request->user()->id,
            $data['horizon_days'] ?? 7,
            $data['people_count'] ?? 3,
            $data['safety_factor'] ?? 1.2
        );

        return response()->json(['data' => $list->load('items')], 201);
    }

    public function updateItem(Request $request, ShoppingList $list, int $itemId)
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'is_checked' => 'nullable|boolean',
            'qty_to_buy_base' => 'nullable|numeric|min:0',
            'estimated_price' => 'nullable|numeric|min:0',
        ]);

        $list->items()->where('id', $itemId)->update($data);

        return response()->json(['status' => 'ok']);
    }
}
