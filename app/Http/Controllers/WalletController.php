<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        return view('wallets.index', ['wallets' => $wallets]);
    }

    public function storeMovement(Request $request, Wallet $wallet): RedirectResponse
    {
        if ($wallet->user_id !== $request->user()->id) {
            abort(403);
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
}
