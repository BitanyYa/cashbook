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
        $user = $request->user();
        $role = $user->getBusinessRole($business);
        $members = ($role === 'employee') ? collect() : $business->users()->get();
        return view('settings.index', compact('business', 'members', 'role'));
    }

    public function updateBusiness(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $this->authorize('update', $business);
        $data = $request->validate(['name' => 'required|string|max:255', 'currency' => 'required|string|size:3']);
        $business->update($data);
        return back();
    }

    public function invite(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $this->authorize('update', $business);
        $data = $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:primary_admin,admin,employee'
        ]);

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'No registered user found with this email. Users must register first.']);
        }

        // Check if already member
        if ($business->users()->where('users.id', $user->id)->exists()) {
            return back()->withErrors(['email' => 'This user is already a member of the business.']);
        }

        $business->users()->syncWithoutDetaching([$user->id => ['role' => $data['role']]]);
        return back()->with('success', 'User added to business successfully!');
    }

    public function updateRole(Request $request, User $user)
    {
        $business = $request->attributes->get('activeBusiness');
        $this->authorize('update', $business);
        $data = $request->validate(['role' => 'required|in:primary_admin,admin,employee']);

        // Prevent demoting primary admin unless transferred
        if ($user->getBusinessRole($business) === 'primary_admin' && $data['role'] !== 'primary_admin') {
            return back()->withErrors(['role' => 'Cannot demote the Primary Admin from business settings.']);
        }

        $business->users()->updateExistingPivot($user->id, ['role' => $data['role']]);
        return back();
    }

    public function remove(Request $request, User $user)
    {
        $business = $request->attributes->get('activeBusiness');
        $currentUser = $request->user();
        $currentUserRole = $currentUser->getBusinessRole($business);
        $targetUserRole = $user->getBusinessRole($business);

        // Can't remove Primary Admin from business
        if ($targetUserRole === 'primary_admin') {
            return back()->withErrors(['member' => 'Cannot remove the Primary Admin from the business.']);
        }

        // Admins can only remove regular employee members
        if ($currentUserRole === 'admin' && $targetUserRole !== 'employee') {
            return back()->withErrors(['member' => 'Admins can only remove regular employee members.']);
        }

        // Detach user from all books belonging to this business
        $bookIds = $business->books()->pluck('books.id');
        if ($bookIds->isNotEmpty()) {
            $user->books()->detach($bookIds);
        }

        $business->users()->detach($user->id);
        return back()->with('success', 'Member removed from business and all associated cashbooks successfully.');
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

        // Detach user from all books belonging to this business
        $bookIds = $business->books()->pluck('books.id');
        if ($bookIds->isNotEmpty()) {
            $user->books()->detach($bookIds);
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
