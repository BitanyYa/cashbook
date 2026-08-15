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
        // Reset non-primary_admin business_user roles back to 'employee'
        // so that book-level admin rights are scoped strictly to book_user pivot table
        DB::table('business_user')
            ->where('role', 'admin')
            ->update(['role' => 'employee']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
