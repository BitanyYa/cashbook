<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * Display the Admin Users Management page.
     */
    public function index(Request $request)
    {
        $business = $request->attributes->get('activeBusiness');
        $books = $business ? $business->books : collect();
        $selectedBookId = $request->get('book_id', $books->first()?->id);

        return view('admin.users.index', compact('business', 'books', 'selectedBookId'));
    }

    /**
     * Search users by email (case-insensitive) and return cashbook membership status.
     */
    public function search(Request $request)
    {
        $request->validate([
            'email' => 'nullable|string|max:255',
            'book_id' => 'nullable|exists:books,id'
        ]);

        $email = strtolower(trim($request->get('email', '')));
        $bookId = $request->get('book_id');
        $business = $request->attributes->get('activeBusiness');

        if (empty($email)) {
            return response()->json([
                'success' => true,
                'users' => []
            ]);
        }

        $book = $bookId ? Book::find($bookId) : null;
        if ($book && $business && $book->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized book access'], 403);
        }

        // Case-insensitive email search
        $users = User::whereRaw('LOWER(email) LIKE ?', ["%{$email}%"])
            ->limit(15)
            ->get();

        $results = $users->map(function ($user) use ($book) {
            $isMember = $book ? $book->users()->where('users.id', $user->id)->exists() : false;
            $memberRole = $book && $isMember ? $book->users()->where('users.id', $user->id)->first()?->pivot->role : null;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at ? $user->created_at->format('M d, Y') : 'N/A',
                'status' => 'Active',
                'is_member' => $isMember,
                'current_role' => $memberRole,
            ];
        });

        return response()->json([
            'success' => true,
            'users' => $results
        ]);
    }

    /**
     * Add a registered user to a cashbook with a specified role.
     */
    public function addMember(Request $request, Book $book)
    {
        $business = $request->attributes->get('activeBusiness');
        if ($book->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|in:primary_admin,admin,employee'
        ]);

        $role = $data['role'] ?? 'employee';

        $user = User::findOrFail($data['user_id']);

        // Check for duplicate membership
        if ($book->users()->where('users.id', $user->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Already a member.'
            ], 400);
        }

        // Ensure user is in the business with appropriate business role
        $businessRole = in_array($role, ['primary_admin', 'admin']) ? $role : 'employee';

        if (!$business->users()->where('users.id', $user->id)->exists()) {
            $business->users()->attach($user->id, ['role' => $businessRole]);
        } else {
            $currentRole = $business->users()->where('users.id', $user->id)->first()?->pivot->role;
            if ($role === 'primary_admin' || ($role === 'admin' && $currentRole === 'employee')) {
                $business->users()->updateExistingPivot($user->id, ['role' => $role]);
            }
        }

        // Associate user with cashbook with assigned role
        $book->users()->attach($user->id, [
            'role' => $role,
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$user->name} successfully added to Cashbook with role " . ucfirst($role) . "."
        ]);
    }

    /**
     * Remove a member from a cashbook.
     */
    public function removeMember(Request $request, Book $book, User $user)
    {
        $business = $request->attributes->get('activeBusiness');
        if ($book->business_id !== $business->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $currentUser = $request->user();
        $currentUserRole = $currentUser->getBookRole($book);

        if (!in_array($currentUserRole, ['primary_admin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        if (!$book->users()->where('users.id', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'User is not a member of this cashbook'], 404);
        }

        $targetRole = $user->getBookRole($book);

        // Cannot remove Primary Admin
        if ($targetRole === 'primary_admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove the Primary Admin. Ownership must be transferred first.'
            ], 403);
        }

        // Admins CANNOT remove other Admins or Primary Admin
        if ($currentUserRole === 'admin' && in_array($targetRole, ['admin', 'primary_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Admins can only remove regular users.'
            ], 403);
        }

        $book->users()->detach($user->id);

        return response()->json([
            'success' => true,
            'message' => "{$user->name} removed from cashbook successfully."
        ]);
    }
}
