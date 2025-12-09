<?php

namespace App\Livewire\Wallets;

use App\Models\Wallet;
use Livewire\Component;

class Index extends Component
{
    public $showForm = false;
    public $editingWallet = null;

    public $name = '';
    public $initial_balance = 0;
    public $target_amount = null;
    public $description = '';
    public $is_shared = false;
    public $is_active = true;

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'initial_balance' => ['required', 'numeric'],
            'target_amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_shared' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function mount()
    {
        $this->is_active = true;
    }

    public function openForm($walletId = null)
    {
        $this->resetForm();

        if ($walletId) {
            $wallet = Wallet::where('user_id', auth()->id())->findOrFail($walletId);
            $this->editingWallet = $wallet->id;
            $this->name = $wallet->name;
            $this->initial_balance = $wallet->initial_balance;
            $this->target_amount = $wallet->target_amount;
            $this->description = $wallet->description;
            $this->is_shared = $wallet->is_shared;
            $this->is_active = $wallet->is_active;
        }

        $this->showForm = true;
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'initial_balance' => $this->initial_balance,
            'target_amount' => $this->target_amount,
            'description' => $this->description,
            'is_shared' => $this->is_shared,
            'is_active' => $this->is_active,
        ];

        if ($this->editingWallet) {
            $wallet = Wallet::where('user_id', auth()->id())->findOrFail($this->editingWallet);
            $wallet->update($data);
            session()->flash('status', 'Bolsillo actualizado.');
        } else {
            Wallet::create([
                ...$data,
                'user_id' => auth()->id(),
            ]);
            session()->flash('status', 'Bolsillo creado.');
        }

        $this->closeForm();
    }

    public function delete($walletId)
    {
        $wallet = Wallet::where('user_id', auth()->id())->findOrFail($walletId);

        $hasUsage = $wallet->transactions()->exists() || $wallet->movements()->exists();
        if ($hasUsage) {
            session()->flash('error', 'No se puede eliminar porque tiene transacciones o movimientos asociados.');
            return;
        }

        $wallet->delete();
        session()->flash('status', 'Bolsillo eliminado.');
    }

    private function resetForm()
    {
        $this->editingWallet = null;
        $this->name = '';
        $this->initial_balance = 0;
        $this->target_amount = null;
        $this->description = '';
        $this->is_shared = false;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $wallets = Wallet::where('user_id', auth()->id())
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

        return view('livewire.wallets.index', [
            'wallets' => $wallets,
        ]);
    }
}
