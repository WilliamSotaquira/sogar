<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\FoodLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $locations = FoodLocation::where('user_id', $request->user()->id)
            ->orderBy('sort_order')
            ->get();

        return response()->json($locations);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_default' => 'nullable|boolean',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        $data['color'] = $data['color'] ?? '#6b7280';

        // Si es default, quitar default de las demás
        if (!empty($data['is_default'])) {
            FoodLocation::where('user_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        $location = FoodLocation::create($data);

        if ($request->wantsJson()) {
            return response()->json($location, 201);
        }

        return back()->with('status', 'Ubicación creada correctamente.');
    }

    public function update(Request $request, FoodLocation $location): RedirectResponse|JsonResponse
    {
        abort_unless($location->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_default' => 'nullable|boolean',
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);

        // Si es default, quitar default de las demás
        if (!empty($data['is_default'])) {
            FoodLocation::where('user_id', $request->user()->id)
                ->where('id', '!=', $location->id)
                ->update(['is_default' => false]);
        }

        $location->update($data);

        if ($request->wantsJson()) {
            return response()->json($location);
        }

        return back()->with('status', 'Ubicación actualizada.');
    }

    public function destroy(Request $request, FoodLocation $location): RedirectResponse|JsonResponse
    {
        abort_unless($location->user_id === $request->user()->id, 403);

        // Verificar si tiene productos asociados
        if ($location->products()->count() > 0) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Esta ubicación tiene productos asociados.'], 422);
            }
            return back()->with('error', 'No se puede eliminar: tiene productos asociados.');
        }

        $location->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('status', 'Ubicación eliminada.');
    }
}
