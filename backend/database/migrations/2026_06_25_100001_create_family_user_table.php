<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['family_admin', 'member', 'guest'])->default('member');
            $table->enum('status', ['active', 'pending', 'blocked'])->default('active');
            $table->timestamps();
            $table->unique(['family_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_user');
    }
};
