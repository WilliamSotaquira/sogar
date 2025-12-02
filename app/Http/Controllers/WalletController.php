<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $wallets = Wallet::where('user_id', $user->id)
            ->with('movements')
            ->orderBy('name')
            ->get()
            ->map(function (Wallet $wallet) {
                $balance = $wallet->initial_balance + $wallet->movements->sum('amount');
                return [
                    'model' => $wallet,
                    'balance' => $balance,
                    'movements' => $wallet->movements()->latest('occurred_on')->latest('id')->take(10)->get(),
                ];
            });

        $editingWallet = null;
        $editId = $request->input('edit');
        if ($editId) {
            $editingWallet = Wallet::where('user_id', $user->id)->find((int) $editId);
        }

        return view('wallets.index', [
            'wallets' => $wallets,
            'editingWallet' => $editingWallet,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $this->validatedData($request, $user->id);

        Wallet::create([
            ...$data,
            'user_id' => $user->id,
            'is_shared' => $request->boolean('is_shared'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('wallets.index')->with('status', 'Bolsillo creado.');
    }

    public function update(Request $request, Wallet $wallet): RedirectResponse
    {
        if ($wallet->user_id !== $request->user()->id) {
            abort(403);
        }

        $data = $this->validatedData($request, $wallet->user_id, $wallet->id);

        $wallet->update([
            ...$data,
            'is_shared' => $request->boolean('is_shared'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('wallets.index')->with('status', 'Bolsillo actualizado.');
    }

    public function destroy(Request $request, Wallet $wallet): RedirectResponse
    {
        if ($wallet->user_id !== $request->user()->id) {
            abort(403);
        }

        $hasUsage = $wallet->transactions()->exists() || $wallet->movements()->exists();
        if ($hasUsage) {
            return redirect()->route('wallets.index')->with('error', 'No puedes eliminar un bolsillo con movimientos o transacciones.');
        }

        $wallet->delete();

        return redirect()->route('wallets.index')->with('status', 'Bolsillo eliminado.');
    }

    public function storeMovement(Request $request, Wallet $wallet): RedirectResponse
    {
        if ($wallet->user_id !== $request->user()->id) {
            abort(403);
        }

        if (!$wallet->is_active) {
            return redirect()->route('wallets.index')->with('error', 'Activa el bolsillo antes de registrar movimientos.');
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'not_in:0'],
            'occurred_on' => ['required', 'date'],
            'concept' => ['nullable', 'string', 'max:255'],
        ]);

        WalletMovement::create([
            'wallet_id' => $wallet->id,
            'user_id' => $request->user()->id,
            'category_id' => null,
            'transaction_id' => null,
            'amount' => $data['amount'],
            'occurred_on' => $data['occurred_on'],
            'concept' => $data['concept'],
        ]);

        return redirect()->route('wallets.index')->with('status', 'Movimiento registrado.');
    }

    private function validatedData(Request $request, int $userId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('sogar_wallets', 'name')
                    ->where('user_id', $userId)
                    ->ignore($ignoreId),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'initial_balance' => ['required', 'numeric'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'is_shared' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
