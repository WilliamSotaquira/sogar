<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Recurrence;
use App\Models\WalletMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $categories = Category::where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })
            ->orderByRaw('CASE WHEN user_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $editingCategory = null;
        $editId = $request->input('edit');
        if ($editId) {
            $editingCategory = Category::where('user_id', $user->id)->find((int) $editId);
        }

        return view('categories.index', [
            'categories' => $categories,
            'editingCategory' => $editingCategory,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $this->validatedData($request, $user->id);

        Category::create([
            ...$data,
            'user_id' => $user->id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('categories.index')->with('status', 'Categoría creada.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $user = $request->user();

        if ($category->user_id !== $user->id) {
            abort(403);
        }

        $data = $this->validatedData($request, $user->id, $category->id);

        $category->update([
            ...$data,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('categories.index')->with('status', 'Categoría actualizada.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        if ($category->user_id !== $request->user()->id) {
            abort(403);
        }

        $hasUsage = $category->budgets()->exists()
            || $category->transactions()->exists()
            || Recurrence::where('category_id', $category->id)->exists()
            || WalletMovement::where('category_id', $category->id)->exists();

        if ($hasUsage) {
            return redirect()->route('categories.index')->with('error', 'No puedes eliminar una categoría con movimientos, presupuestos o recurrencias.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Categoría eliminada.');
    }

    private function validatedData(Request $request, int $userId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sogar_categories', 'name')
                    ->where(fn ($q) => $q->where('user_id', $userId))
                    ->ignore($ignoreId),
            ],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'description' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
