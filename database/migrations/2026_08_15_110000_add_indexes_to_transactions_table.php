<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['book_id', 'transaction_date', 'id'], 'idx_book_date_id');
            $table->index(['book_id', 'user_id'], 'idx_book_user');
            $table->index(['book_id', 'category_id'], 'idx_book_category');
            $table->index(['book_id', 'type'], 'idx_book_type');
            $table->index(['book_id', 'mode'], 'idx_book_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_book_date_id');
            $table->dropIndex('idx_book_user');
            $table->dropIndex('idx_book_category');
            $table->dropIndex('idx_book_type');
            $table->dropIndex('idx_book_mode');
        });
    }
};
