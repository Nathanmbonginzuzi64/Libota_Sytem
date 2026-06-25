<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oral_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_member_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('narrator')->nullable();
            $table->enum('media_type', ['audio', 'video'])->default('audio');
            $table->string('file_path');
            $table->text('transcription')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('language', 10)->default('fr');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oral_memories');
    }
};
