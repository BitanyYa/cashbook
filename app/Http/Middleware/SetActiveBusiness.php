<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetActiveBusiness
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user) {
            $activeId = session('active_business_id');

            // Verify if stored active_business_id is still valid for this user
            if ($activeId && !$user->businesses()->where('business_id', $activeId)->exists()) {
                session()->forget('active_business_id');
                $activeId = null;
            }

            if (!$activeId) {
                $firstBusiness = $user->businesses()->first();
                if ($firstBusiness) {
                    $activeId = $firstBusiness->id;
                    session(['active_business_id' => $activeId]);
                }
            }

            $activeBusiness = $activeId ? Business::find($activeId) : null;
            $request->attributes->set('activeBusiness', $activeBusiness);

            // If user has no active business and is accessing a business-dependent route
            if (!$activeBusiness && !$request->routeIs('unassigned', 'logout')) {
                if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                    return response()->json(['message' => 'User is not yet assigned to any cashbook.'], 403);
                }
                return redirect()->route('unassigned');
            }
        }
        return $next($request);
    }
}
