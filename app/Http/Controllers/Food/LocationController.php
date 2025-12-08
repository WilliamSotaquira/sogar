<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\FoodLocation;
use App\Models\FoodStockBatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        $locations = FoodLocation::where('user_id', $request->user()->id)
            ->withCount(['products as products_count', 'batches as batches_count'])
            ->orderBy('sort_order')
            ->get();

        if ($request->wantsJson()) {
            return response()->json($locations);
        }

        return view('food.locations.index', [
            'locations' => $locations,
        ]);
    }

    public function create(): View
    {
        return view('food.locations.create');
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
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_default'] = !empty($data['is_default']);

        // Si es default, quitar default de las demás
        if (!empty($data['is_default'])) {
            FoodLocation::where('user_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        $location = FoodLocation::create($data);

        if ($request->wantsJson()) {
            return response()->json($location, 201);
        }

        return redirect()
            ->route('food.locations.show', $location)
            ->with('status', 'Ubicación creada correctamente.');
    }

    public function show(Request $request, FoodLocation $location): View
    {
        abort_unless($location->user_id === $request->user()->id, 403);

        $batches = $location->batches()
            ->with(['product' => fn ($q) => $q->select('id', 'name', 'brand', 'unit_base', 'default_location_id')])
            ->orderByDesc('created_at')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'location' => $location,
                'batches' => $batches,
            ]);
        }

        $stats = [
            'products' => $location->products()->count(),
            'batches' => $batches->count(),
            'expiring' => $batches->filter(fn ($batch) => $this->isExpiringSoon($batch))->count(),
            'expired' => $batches->filter(fn ($batch) => $this->isExpired($batch))->count(),
        ];

        return view('food.locations.show', [
            'location' => $location,
            'batches' => $batches,
            'stats' => $stats,
        ]);
    }

    public function edit(Request $request, FoodLocation $location): View
    {
        abort_unless($location->user_id === $request->user()->id, 403);

        return view('food.locations.edit', [
            'location' => $location,
        ]);
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
        $data['color'] = $data['color'] ?? $location->color;
        $data['sort_order'] = $data['sort_order'] ?? $location->sort_order;
        $data['is_default'] = !empty($data['is_default']);

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

        return redirect()
            ->route('food.locations.show', $location)
            ->with('status', 'Ubicación actualizada.');
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

        return redirect()
            ->route('food.locations.index')
            ->with('status', 'Ubicación eliminada.');
    }

    private function isExpiringSoon(FoodStockBatch $batch): bool
    {
        if (!$batch->expires_on) {
            return false;
        }

        $days = now()->diffInDays($batch->expires_on, false);
        return $days >= 0 && $days <= 7;
    }

    private function isExpired(FoodStockBatch $batch): bool
    {
        return $batch->expires_on ? now()->gt($batch->expires_on) : false;
    }
}
