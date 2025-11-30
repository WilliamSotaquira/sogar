<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Integration;
use App\Jobs\SyncGoogleCalendarEvent;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();

        $budgets = Budget::with('category')
            ->where('user_id', $user->id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        $categories = Category::where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })
            ->where('type', 'expense')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('budgets.index', [
            'budgets' => $budgets,
            'categories' => $categories,
            'currentMonth' => (int) $now->format('m'),
            'currentYear' => (int) $now->format('Y'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('sogar_categories', 'id')->where(function ($q) use ($user) {
                    $q->whereNull('user_id')->orWhere('user_id', $user->id);
                }),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'is_flexible' => ['sometimes', 'boolean'],
            'sync_to_calendar' => ['sometimes', 'boolean'],
        ]);

        $budget = Budget::updateOrCreate(
            [
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'month' => $data['month'],
                'year' => $data['year'],
            ],
            [
                'amount' => $data['amount'],
                'is_flexible' => $request->boolean('is_flexible'),
                'sync_to_calendar' => $request->boolean('sync_to_calendar'),
            ]
        );

        if ($request->boolean('sync_to_calendar')) {
            $integration = Integration::where('user_id', $user->id)->where('provider', 'google')->first();
            if ($integration) {
                $startDate = Carbon::createFromDate($data['year'], $data['month'], 1);
                SyncGoogleCalendarEvent::dispatchSync($integration, [
                    'summary' => 'Presupuesto: ' . $this->categoryName($data['category_id']),
                    'description' => 'Monto: ' . $data['amount'],
                    'start' => $startDate->toDateString(),
                    'provider_event_id' => null,
                    'model' => 'budget',
                    'model_id' => $budget->id,
                ]);
            }
        }

        return redirect()->route('budgets.index')->with('status', 'Presupuesto guardado.');
    }

    public function destroy(Request $request, Budget $budget): RedirectResponse
    {
        if ($budget->user_id !== $request->user()->id) {
            abort(403);
        }
        $budget->delete();

        return redirect()->route('budgets.index')->with('status', 'Presupuesto eliminado.');
    }

    private function categoryName(int $categoryId): string
    {
        return Category::find($categoryId)?->name ?? 'Categoría';
    }
}
