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

            // Check if route specifies a book or transaction belonging to a business the user has access to
            $routeBook = $request->route('book');
            $routeTx = $request->route('transaction');
            $targetBusinessId = null;

            if ($routeBook instanceof \App\Models\Book) {
                $targetBusinessId = $routeBook->business_id;
            } elseif (is_numeric($routeBook)) {
                $targetBusinessId = \App\Models\Book::where('id', $routeBook)->value('business_id');
            } elseif ($routeTx instanceof \App\Models\Transaction) {
                $targetBusinessId = $routeTx->business_id;
            } elseif (is_numeric($routeTx)) {
                $targetBusinessId = \App\Models\Transaction::where('id', $routeTx)->value('business_id');
            }

            if ($targetBusinessId && $user->businesses()->where('businesses.id', $targetBusinessId)->exists()) {
                $activeId = $targetBusinessId;
                session(['active_business_id' => $activeId]);
            }

            // Verify if stored active_business_id is still valid for this user
            if ($activeId && !$user->businesses()->where('businesses.id', $activeId)->exists()) {
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
