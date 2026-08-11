<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Business;
use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create or update the Primary Admin User (without deleting other registered users)
        $admin = User::updateOrCreate(
            ['email' => 'admin@cashbook.com'],
            [
                'name' => 'Primary Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 3. Create the default Business
        $business = Business::updateOrCreate(
            ['name' => 'CashBook Corporate'],
            [
                'currency' => 'USD',
            ]
        );

        // Attach Primary Admin role in business_user pivot if not already attached
        if (!$business->users()->where('user_id', $admin->id)->exists()) {
            $business->users()->attach($admin->id, ['role' => 'primary_admin']);
        }

        // 4. Create the default Main Cashbook
        $book = Book::updateOrCreate(
            ['name' => 'Main Cashbook', 'business_id' => $business->id],
            [
                'description' => 'Primary Business Cashbook',
                'currency' => 'USD',
            ]
        );

        // Attach Primary Admin role in book_user pivot if not already attached
        if (!$book->users()->where('user_id', $admin->id)->exists()) {
            $book->users()->attach($admin->id, ['role' => 'primary_admin']);
        }
    }
}
