<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('business_name', 200);
            $table->text('contact_details')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('publishers'); }
};
