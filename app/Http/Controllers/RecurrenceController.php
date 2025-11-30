<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Integration;
use App\Models\Recurrence;
use App\Models\Wallet;
use App\Jobs\SyncGoogleCalendarEvent;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecurrenceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $recurrences = Recurrence::with(['category', 'wallet'])
            ->where('user_id', $user->id)
            ->orderByDesc('next_run_on')
            ->get();

        $categories = Category::where(function ($q) use ($user) {
            $q->whereNull('user_id')->orWhere('user_id', $user->id);
        })
            ->where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $wallets = Wallet::where('user_id', $user->id)->where('is_active', true)->orderBy('name')->get();

        return view('recurrences.index', [
            'recurrences' => $recurrences,
            'categories' => $categories,
            'wallets' => $wallets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'next_run_on' => ['required', 'date'],
            'category_id' => [
                'required',
                Rule::exists('sogar_categories', 'id')->where(function ($q) use ($user) {
                    $q->whereNull('user_id')->orWhere('user_id', $user->id);
                }),
            ],
            'wallet_id' => [
                'nullable',
                Rule::exists('sogar_wallets', 'id')->where('user_id', $user->id),
            ],
            'note' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'sync_to_calendar' => ['sometimes', 'boolean'],
        ]);

        $recurrence = Recurrence::create([
            'user_id' => $user->id,
            'category_id' => $data['category_id'],
            'wallet_id' => $data['wallet_id'] ?? null,
            'name' => $data['name'],
            'amount' => $data['amount'],
            'frequency' => $data['frequency'],
            'next_run_on' => Carbon::parse($data['next_run_on']),
            'last_run_at' => null,
            'is_active' => $request->boolean('is_active', true),
            'sync_to_calendar' => $request->boolean('sync_to_calendar'),
            'note' => $data['note'] ?? null,
        ]);

        if ($request->boolean('sync_to_calendar')) {
            $integration = Integration::where('user_id', $user->id)->where('provider', 'google')->first();
            if ($integration) {
                SyncGoogleCalendarEvent::dispatch($integration, [
                    'summary' => 'Recurrencia: ' . $data['name'],
                    'description' => 'Monto: ' . $data['amount'],
                    'start' => $data['next_run_on'],
                    'provider_event_id' => null,
                    'model' => 'recurrence',
                    'model_id' => $recurrence->id,
                ]);
            }
        }

        return redirect()->route('recurrences.index')->with('status', 'Recurrencia guardada.');
    }

    public function destroy(Request $request, Recurrence $recurrence): RedirectResponse
    {
        if ($recurrence->user_id !== $request->user()->id) {
            abort(403);
        }

        $recurrence->delete();

        return redirect()->route('recurrences.index')->with('status', 'Recurrencia eliminada.');
    }
}
