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
        if ($businessRole === 'primary_admin') {
            return true;
        }

        // Check book-level permissions
        $bookRole = $user->getBookRole($transaction->book);
        return $bookRole !== null && in_array($bookRole, ['primary_admin', 'admin']);
    }

    public function view(User $user, Transaction $transaction): bool
    {
        // Check business-level permissions first
        $businessRole = $user->getBusinessRole($transaction->business);
        if ($businessRole === 'primary_admin') {
            return true;
        }

        // Check book-level permissions
        return $user->canViewBook($transaction->book);
    }

    public function update(User $user, Transaction $transaction): bool
    {
        // Check business-level permissions first
        $businessRole = $user->getBusinessRole($transaction->business);
        if ($businessRole === 'primary_admin') {
            return true;
        }

        // Check book-level permissions
        $bookRole = $user->getBookRole($transaction->book);
        return $bookRole !== null && in_array($bookRole, ['primary_admin', 'admin']);
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        // Check business-level permissions first
        $businessRole = $user->getBusinessRole($transaction->business);
        if ($businessRole === 'primary_admin') {
            return true;
        }

        // Check book-level permissions
        $bookRole = $user->getBookRole($transaction->book);
        return $bookRole !== null && in_array($bookRole, ['primary_admin', 'admin']);
    }
}
