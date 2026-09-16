<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publisher_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 50);
            $table->string('subject');
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['publisher_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publisher_activities');
    }
};
