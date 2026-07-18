<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('business_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['primary_admin','admin','employee'])->default('employee');
            $table->timestamps();
            $table->unique(['business_id','user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_user');
        Schema::dropIfExists('businesses');
    }
};
