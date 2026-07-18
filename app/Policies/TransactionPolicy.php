<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function approve(User $user, Transaction $transaction): bool
    {
        // Check business-level permissions first
        $businessRole = $user->getBusinessRole($transaction->business);
        if (in_array($businessRole, ['primary_admin', 'admin'])) {
            return true;
        }

        // Check book-level permissions
        $bookRole = $user->getBookRole($transaction->book);
        return in_array($bookRole, ['primary_admin', 'admin']);
    }

    public function view(User $user, Transaction $transaction): bool
    {
        // Check business-level permissions first
        $businessRole = $user->getBusinessRole($transaction->business);
        if (in_array($businessRole, ['primary_admin', 'admin'])) {
            return true;
        }

        // Check book-level permissions
        return $user->canViewBook($transaction->book);
    }

    public function update(User $user, Transaction $transaction): bool
    {
        // Check business-level permissions first
        $businessRole = $user->getBusinessRole($transaction->business);
        if (in_array($businessRole, ['primary_admin', 'admin'])) {
            return true;
        }

        // Check book-level permissions
        $bookRole = $user->getBookRole($transaction->book);

        // Primary admins and admins can edit any transaction
        if (in_array($bookRole, ['primary_admin', 'admin'])) {
            return true;
        }

        // Employees can only edit their own transactions
        if ($bookRole === 'employee') {
            return $transaction->user_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        // Check business-level permissions first
        $businessRole = $user->getBusinessRole($transaction->business);
        if (in_array($businessRole, ['primary_admin', 'admin'])) {
            return true;
        }

        // Check book-level permissions
        $bookRole = $user->getBookRole($transaction->book);

        // Primary admins and admins can delete any transaction
        if (in_array($bookRole, ['primary_admin', 'admin'])) {
            return true;
        }

        // Employees can only delete their own transactions
        if ($bookRole === 'employee') {
            return $transaction->user_id === $user->id;
        }

        return false;
    }
}
