<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename legacy role values in business_user and book_user pivot tables.
     *
     * business_user: owner → primary_admin, staff → employee
     * book_user:     manager → primary_admin, editor → admin, viewer → employee
     *
     * MySQL requires the enum column to be redefined before the data update
     * so that both old and new values are temporarily valid during the migration.
     */
    public function up(): void
    {
        // ── business_user ────────────────────────────────────────────────────
        // Widen enum to accept both old and new values during transition
        Schema::table('business_user', function (Blueprint $table) {
            $table->enum('role', ['owner', 'primary_admin', 'admin', 'staff', 'employee'])
                  ->default('employee')
                  ->change();
        });

        DB::table('business_user')->where('role', 'owner')->update(['role' => 'primary_admin']);
        DB::table('business_user')->where('role', 'staff')->update(['role' => 'employee']);

        // Narrow enum to new values only
        Schema::table('business_user', function (Blueprint $table) {
            $table->enum('role', ['primary_admin', 'admin', 'employee'])
                  ->default('employee')
                  ->change();
        });

        // ── book_user ────────────────────────────────────────────────────────
        Schema::table('book_user', function (Blueprint $table) {
            $table->enum('role', ['manager', 'primary_admin', 'admin', 'editor', 'employee', 'viewer'])
                  ->default('employee')
                  ->change();
        });

        DB::table('book_user')->where('role', 'manager')->update(['role' => 'primary_admin']);
        DB::table('book_user')->where('role', 'editor')->update(['role' => 'admin']);
        DB::table('book_user')->where('role', 'viewer')->update(['role' => 'employee']);

        Schema::table('book_user', function (Blueprint $table) {
            $table->enum('role', ['primary_admin', 'admin', 'employee'])
                  ->default('employee')
                  ->change();
        });
    }

    public function down(): void
    {
        // ── business_user ────────────────────────────────────────────────────
        Schema::table('business_user', function (Blueprint $table) {
            $table->enum('role', ['owner', 'primary_admin', 'admin', 'staff', 'employee'])
                  ->default('employee')
                  ->change();
        });

        DB::table('business_user')->where('role', 'primary_admin')->update(['role' => 'owner']);
        DB::table('business_user')->where('role', 'employee')->update(['role' => 'staff']);

        Schema::table('business_user', function (Blueprint $table) {
            $table->enum('role', ['owner', 'admin', 'staff'])
                  ->default('staff')
                  ->change();
        });

        // ── book_user ────────────────────────────────────────────────────────
        Schema::table('book_user', function (Blueprint $table) {
            $table->enum('role', ['manager', 'primary_admin', 'admin', 'editor', 'employee', 'viewer'])
                  ->default('viewer')
                  ->change();
        });

        DB::table('book_user')->where('role', 'primary_admin')->update(['role' => 'manager']);
        DB::table('book_user')->where('role', 'admin')->update(['role' => 'editor']);
        DB::table('book_user')->where('role', 'employee')->update(['role' => 'viewer']);

        Schema::table('book_user', function (Blueprint $table) {
            $table->enum('role', ['manager', 'editor', 'viewer'])
                  ->default('viewer')
                  ->change();
        });
    }
};
