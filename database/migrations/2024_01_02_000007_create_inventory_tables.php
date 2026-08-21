<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // FR-3 fix: every stock change writes to inventory_transactions first;
    // inventory.quantity is derived/recomputed from that ledger rather than
    // updated ad hoc from multiple code paths (root cause of the sync bugs
    // called out in the SRS: stock not updating after sale, low-stock page
    // showing incorrect data, restocks not reflecting).
    public function up(): void {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->timestamps();
            $table->unique('book_id');
        });
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->enum('transaction_type', ['restock', 'sale', 'cancel', 'return']);
            $table->integer('change_qty');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['book_id', 'transaction_type']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventories');
    }
};
