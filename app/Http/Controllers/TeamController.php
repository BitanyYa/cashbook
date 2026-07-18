<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $members = $business->users()->get();
        return view('settings.index', compact('business','members'));
    }

    public function updateBusiness(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $data = $request->validate(['name' => 'required|string|max:255', 'currency' => 'required|string|size:3']);
        $business->update($data);
        return back();
    }

    public function invite(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $data = $request->validate(['email' => 'required|email', 'role' => 'required|in:primary_admin,admin,employee']);
        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found. Need to register first.']);
        }
        $business->users()->syncWithoutDetaching([$user->id => ['role' => $data['role']]]);
        return back();
    }

    public function updateRole(Request $request, User $user)
    {
        $business = $request->attributes->get('activeBusiness');
        $data = $request->validate(['role' => 'required|in:primary_admin,admin,employee']);
        // Prevent removing the last primary admin
        if ($data['role'] !== 'primary_admin') {
            $primaryAdminCount = $business->users()->wherePivot('role','primary_admin')->count();
            if ($primaryAdminCount <= 1 && $business->users()->wherePivot('role','primary_admin')->where('users.id', $user->id)->exists()) {
                return back()->withErrors(['role' => 'Cannot demote the last primary admin.']);
            }
        }
        $business->users()->updateExistingPivot($user->id, ['role' => $data['role']]);
        return back();
    }

    public function remove(Request $request, User $user)
    {
        $business = $request->attributes->get('activeBusiness');
        // Prevent removing the last primary admin
        if ($business->users()->wherePivot('role','primary_admin')->where('users.id', $user->id)->exists()) {
            $primaryAdminCount = $business->users()->wherePivot('role','primary_admin')->count();
            if ($primaryAdminCount <= 1) {
                return back()->withErrors(['member' => 'Cannot remove the last primary admin.']);
            }
        }
        $business->users()->detach($user->id);
        return back();
    }

    public function leave(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $user = $request->user();

        // Prevent the last primary admin from leaving
        if ($business->users()->wherePivot('role', 'primary_admin')->where('users.id', $user->id)->exists()) {
            $primaryAdminCount = $business->users()->wherePivot('role', 'primary_admin')->count();
            if ($primaryAdminCount <= 1) {
                return back()->withErrors(['member' => 'You are the only primary admin and cannot leave the business.']);
            }
        }

        $business->users()->detach($user->id);

        // If the user has other businesses, redirect to the first one.
        // Otherwise, redirect to the create business page.
        $nextBusiness = $user->businesses()->first();
        if ($nextBusiness) {
            $user->setActiveBusiness($nextBusiness->id);
            return redirect()->route('dashboard');
        }

        return redirect()->route('businesses.create');
    }
}
