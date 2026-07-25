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
        $role = $user->getBookRole($book);

        // Primary admins, admins, and employees can view books they have access to
        return in_array($role, ['primary_admin', 'admin', 'employee']);
    }

    /**
     * Check if user can update the book settings.
     */
    public function update(User $user, Book $book): bool
    {
        // Only Primary Admin (owner) can edit cashbook settings
        return $user->getBookRole($book) === 'primary_admin';
    }

    /**
     * Check if user can delete the book.
     */
    public function delete(User $user, Book $book): bool
    {
        // Only Primary Admin (owner) can delete cashbooks
        return $user->getBookRole($book) === 'primary_admin';
    }

    /**
     * Check if user can manage cashbook members.
     */
    public function manageMembers(User $user, Book $book): bool
    {
        // Primary Admins and Admins can manage members
        return in_array($user->getBookRole($book), ['primary_admin', 'admin']);
    }

    /**
     * Check if user can transfer ownership of the book.
     */
    public function transferOwnership(User $user, Book $book): bool
    {
        // Only Primary Admin (owner) can transfer ownership
        return $user->getBookRole($book) === 'primary_admin';
    }
}
