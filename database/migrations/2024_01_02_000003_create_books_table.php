<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('publishers')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete();
            $table->string('title', 250);
            // Bengali -> English transliteration index, re-generated on save (FR-4)
            $table->string('title_transliterated', 250)->nullable();
            $table->string('isbn', 20)->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('mrp', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image_url', 500)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->softDeletes(); // soft-delete so historical order lines stay intact (FR-2)
            $table->timestamps();
            $table->fullText('title');
            $table->index('title_transliterated');
        });
    }
    public function down(): void { Schema::dropIfExists('books'); }
};
