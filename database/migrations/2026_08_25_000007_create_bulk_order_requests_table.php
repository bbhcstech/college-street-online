<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bulk_order_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('institution_name', 200);
            $table->string('contact_name', 150);
            $table->string('email', 150);
            $table->string('phone', 30);
            $table->text('requirements');
            $table->text('notes')->nullable();
            $table->enum('status', ['new', 'contacted', 'quoted', 'accepted', 'rejected', 'completed'])->default('new');
            $table->decimal('quoted_amount', 12, 2)->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_order_requests');
    }
};
