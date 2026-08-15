<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean up any book_user entries for users no longer in the corresponding business
        $orphanedBookUserIds = DB::table('book_user')
            ->join('books', 'books.id', '=', 'book_user.book_id')
            ->leftJoin('business_user', function ($join) {
                $join->on('business_user.business_id', '=', 'books.business_id')
                     ->on('business_user.user_id', '=', 'book_user.user_id');
            })
            ->whereNull('business_user.user_id')
            ->pluck('book_user.id');

        if ($orphanedBookUserIds->isNotEmpty()) {
            DB::table('book_user')->whereIn('id', $orphanedBookUserIds)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
