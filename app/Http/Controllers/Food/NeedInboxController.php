<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Services\NeedInboxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NeedInboxController extends Controller
{
    public function store(Request $request, NeedInboxService $service): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'query' => 'required|string|max:255',
            'unit_base' => 'nullable|string|in:unit,g,kg,ml,l',
            'confirm_new' => 'nullable|boolean',
        ]);

        try {
            $result = $service->addFoodNeed(
                $request->user(),
                $data['query'],
                $data['unit_base'] ?? null,
                (bool) ($data['confirm_new'] ?? false)
            );
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                $errors = $e->errors();
                if (isset($errors['confirm_new'])) {
                    return response()->json([
                        'status' => 'needs_unit',
                        'message' => 'Producto nuevo. Confirma unidad base.',
                    ], 422);
                }
            }

            throw $e;
        }

        $item = $result['item'];
        $message = $result['message'] ?? 'Agregado a la lista activa.';

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => $message,
                'list_id' => $item->shopping_list_id,
                'action' => $result['action'] ?? 'added',
                'item' => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'qty_to_buy_base' => (float) $item->qty_to_buy_base,
                    'created_at_human' => $item->created_at?->diffForHumans(),
                ],
            ]);
        }

        return redirect()
            ->route('food.shopping-list.show', $item->shopping_list_id)
            ->with('status', $message);
    }
}
