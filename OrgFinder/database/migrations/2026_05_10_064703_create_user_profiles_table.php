<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('year_level')->nullable();
            $table->string('program')->nullable();
            $table->json('interest')->nullable();
            $table->json('skill_to_improve')->nullable();
            $table->json('preferred_activity')->nullable();
            $table->boolean('profile_completed')->default(false);
            $table->string('profile_photo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
