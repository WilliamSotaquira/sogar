<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleIntegrationController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/calendar.events'])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $user = $request->user();
        $googleUser = Socialite::driver('google')->stateless()->user();

        Integration::updateOrCreate(
            ['user_id' => $user->id, 'provider' => 'google'],
            [
                'provider_user_id' => $googleUser->getId(),
                'access_token' => $googleUser->token,
                'refresh_token' => $googleUser->refreshToken,
                'expires_at' => $googleUser->expiresIn ? now()->addSeconds($googleUser->expiresIn) : null,
                'scopes' => implode(' ', $googleUser->approvedScopes ?? []),
                'status' => 'active',
                'meta' => [
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                ],
            ]
        );

        return redirect()->route('dashboard')->with('status', 'Google conectado para calendario.');
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $user = $request->user();
        Integration::where('user_id', $user->id)->where('provider', 'google')->delete();

        return back()->with('status', 'Integración Google desconectada.');
    }
}
