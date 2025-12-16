<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\ShoppingListType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShoppingListTypeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $name = trim($data['name']);
        $base = Str::slug($name) ?: 'tipo';

        $slug = $base;
        $i = 2;
        while (ShoppingListType::where('user_id', $userId)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        $sortOrder = (int) (ShoppingListType::where('user_id', $userId)->max('sort_order') ?? 0) + 10;

        $type = ShoppingListType::create([
            'user_id' => $userId,
            'name' => $name,
            'slug' => $slug,
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);

        return response()->json([
            'status' => 'ok',
            'data' => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
            ],
        ]);
    }
}

