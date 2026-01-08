<?php

namespace App\Http\Middleware;

use App\Models\FamilyMember;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanManageShopping
{
    /**
     * Allow access if:
     * - user is system admin, OR
     * - user has no active family group set, OR
     * - user is a member of the active family group and has shopping permission.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if ($user->isSystemAdmin()) {
            return $next($request);
        }

        if (!$user->active_family_group_id) {
            return $next($request);
        }

        $membership = FamilyMember::query()
            ->where('family_group_id', $user->active_family_group_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$membership) {
            abort(403);
        }

        if (!$membership->canManage('shopping')) {
            abort(403);
        }

        return $next($request);
    }
}
