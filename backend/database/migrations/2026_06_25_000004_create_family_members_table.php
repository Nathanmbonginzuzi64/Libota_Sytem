<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();
            $table->foreignId('father_id')->nullable()->constrained('family_members')->nullOnDelete();
            $table->foreignId('mother_id')->nullable()->constrained('family_members')->nullOnDelete();
            $table->boolean('is_adopted')->default(false);
            $table->text('biography')->nullable();
            $table->timestamps();
        });

        Schema::create('marriages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spouse_one_id')->constrained('family_members')->cascadeOnDelete();
            $table->foreignId('spouse_two_id')->constrained('family_members')->cascadeOnDelete();
            $table->date('marriage_date')->nullable();
            $table->date('divorce_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marriages');
        Schema::dropIfExists('family_members');
    }
};
