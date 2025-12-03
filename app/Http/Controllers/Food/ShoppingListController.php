<?php

namespace App\Http\Controllers\Food;

use App\Http\Controllers\Controller;
use App\Models\ShoppingList;
use App\Services\ShoppingListGenerator;
use App\Services\ShoppingListSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShoppingListController extends Controller
{
    public function index(Request $request): View
    {
        $list = ShoppingList::with('items')
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->latest('generated_at')
            ->first();

        return view('food.shopping-list.index', [
            'list' => $list,
        ]);
    }

    public function generate(
        Request $request,
        ShoppingListGenerator $generator
    ): RedirectResponse {
        $data = $request->validate([
            'horizon_days' => 'nullable|integer|min:1|max:30',
            'people_count' => 'nullable|integer|min:1|max:10',
            'safety_factor' => 'nullable|numeric|min:1|max:2',
        ]);

        $generator->generate(
            $request->user()->id,
            $data['horizon_days'] ?? 7,
            $data['people_count'] ?? 3,
            $data['safety_factor'] ?? 1.2
        );

        return back()->with('status', 'Lista generada.');
    }

    public function markItem(Request $request, ShoppingList $list, int $itemId): RedirectResponse
    {
        $this->authorizeList($request, $list);

        $list->items()->where('id', $itemId)->update([
            'is_checked' => $request->boolean('is_checked'),
        ]);

        return back();
    }

    public function sync(Request $request, ShoppingListSyncService $sync): RedirectResponse
    {
        $list = ShoppingList::with('items')->where('user_id', $request->user()->id)->where('status', 'active')->firstOrFail();
        $sync->syncToInventory($list, $request->input('wallet_id'));

        return back()->with('status', 'Lista sincronizada a inventario.');
    }

    private function authorizeList(Request $request, ShoppingList $list): void
    {
        abort_unless($list->user_id === $request->user()->id, 403);
    }
}
