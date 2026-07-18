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
     * Check if user can update the book.
     */
    public function update(User $user, Book $book): bool
    {
        // Primary admins and admins can update books
        return $user->canManageBook($book);
    }

    /**
     * Check if user can delete the book.
     */
    public function delete(User $user, Book $book): bool
    {
        // Primary admins and admins can delete books
        return $user->canManageBook($book);
    }
}
