<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryKeyword;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();
        $categories = Category::where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $wallets = Wallet::where('user_id', $user->id)->where('is_active', true)->orderBy('name')->get();

        $suggested = $this->suggestCategoryId($user->id, $request->old('note'));

        return view('transactions.create', [
            'categories' => $categories,
            'wallets' => $wallets,
            'suggestedCategoryId' => $suggested,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'occurred_on' => ['required', 'date'],
            'category_id' => [
                'nullable',
                Rule::exists('sogar_categories', 'id')->where(function ($q) use ($user) {
                    $q->whereNull('user_id')->orWhere('user_id', $user->id);
                }),
            ],
            'wallet_id' => [
                'nullable',
                Rule::exists('sogar_wallets', 'id')->where('user_id', $user->id),
            ],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $categoryId = $validated['category_id'] ?? $this->suggestCategoryId($user->id, $validated['note'] ?? '');

        if (!$categoryId) {
            return back()
                ->withErrors(['category_id' => 'Selecciona una categoría o escribe una nota con una palabra clave.'])
                ->withInput();
        }

        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $categoryId,
            'wallet_id' => $validated['wallet_id'] ?? null,
            'amount' => $validated['amount'],
            'occurred_on' => Carbon::parse($validated['occurred_on']),
            'note' => $validated['note'] ?? null,
            'origin' => 'manual',
            'tags' => null,
        ]);

        return redirect()->route('dashboard')->with('status', 'Transacción registrada');
    }

    private function suggestCategoryId(int $userId, ?string $note): ?int
    {
        if (!$note) {
            return null;
        }

        $text = mb_strtolower($note);

        $keyword = CategoryKeyword::where('user_id', $userId)
            ->get()
            ->first(function (CategoryKeyword $kw) use ($text) {
                return str_contains($text, mb_strtolower($kw->keyword));
            });

        return $keyword?->category_id;
    }
}
