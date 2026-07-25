<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessRole
{
    /**
     * Ensure the authenticated user has one of the allowed roles on the active business.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $business = $request->attributes->get('activeBusiness');
        $book = $request->route('book');

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$business && !$book) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'User is not yet assigned to any cashbook.'], 403);
            }
            return redirect()->route('unassigned');
        }

        $role = $book ? $user->getBookRole($book) : $user->getBusinessRole($business);
        if (!$role || (!empty($roles) && !in_array($role, $roles))) {
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'User is not yet assigned to any cashbook.'], 403);
            }
            return redirect()->route('unassigned');
        }

        return $next($request);
    }
}
