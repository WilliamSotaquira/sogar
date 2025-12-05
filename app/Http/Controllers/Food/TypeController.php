<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\FoodType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $types = FoodType::where('user_id', $request->user()->id)
            ->orderBy('sort_order')
            ->get();

        return response()->json($types);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['color'] = $data['color'] ?? '#10b981';
        $data['is_active'] = true;

        $type = FoodType::create($data);

        if ($request->wantsJson()) {
            return response()->json($type, 201);
        }

        return back()->with('status', 'Tipo creado correctamente.');
    }

    public function update(Request $request, FoodType $type): RedirectResponse|JsonResponse
    {
        abort_unless($type->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $type->update($data);

        if ($request->wantsJson()) {
            return response()->json($type);
        }

        return back()->with('status', 'Tipo actualizado.');
    }

    public function destroy(Request $request, FoodType $type): RedirectResponse|JsonResponse
    {
        abort_unless($type->user_id === $request->user()->id, 403);

        // Verificar si tiene productos asociados
        if ($type->products()->count() > 0) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Este tipo tiene productos asociados.'], 422);
            }
            return back()->with('error', 'No se puede eliminar: tiene productos asociados.');
        }

        $type->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'Tipo eliminado.');
    }
}
