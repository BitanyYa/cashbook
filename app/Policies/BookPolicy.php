<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * Check if user can view the book.
     */
    public function view(User $user, Book $book): bool
    {
        $businessRole = $user->getBusinessRole($book->business);
        if ($businessRole === 'primary_admin') {
            return true;
        }

        $role = $user->getBookRole($book);
        return $role !== null && in_array($role, ['primary_admin', 'admin', 'operator', 'employee', 'viewer']);
    }

    /**
     * Check if user can update the book settings.
     */
    public function update(User $user, Book $book): bool
    {
        $businessRole = $user->getBusinessRole($book->business);
        if ($businessRole === 'primary_admin') {
            return true;
        }

        $role = $user->getBookRole($book);
        return $role !== null && in_array($role, ['primary_admin', 'admin']);
    }

    /**
     * Check if user can delete the book.
     */
    public function delete(User $user, Book $book): bool
    {
        $businessRole = $user->getBusinessRole($book->business);
        if ($businessRole === 'primary_admin') {
            return true;
        }

        $role = $user->getBookRole($book);
        return $role !== null && in_array($role, ['primary_admin', 'admin']);
    }

    /**
     * Check if user can manage cashbook members.
     */
    public function manageMembers(User $user, Book $book): bool
    {
        $businessRole = $user->getBusinessRole($book->business);
        if ($businessRole === 'primary_admin') {
            return true;
        }

        $role = $user->getBookRole($book);
        return $role !== null && in_array($role, ['primary_admin', 'admin']);
    }

    /**
     * Check if user can transfer ownership of the book.
     */
    public function transferOwnership(User $user, Book $book): bool
    {
        $businessRole = $user->getBusinessRole($book->business);
        if ($businessRole === 'primary_admin') {
            return true;
        }

        $role = $user->getBookRole($book);
        return $role === 'primary_admin';
    }
}
