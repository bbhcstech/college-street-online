<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->timestamps();
        });
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('authors');
        Schema::dropIfExists('categories');
    }
};
