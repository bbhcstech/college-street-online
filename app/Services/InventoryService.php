<?php
namespace App\Services;

use App\Models\Book;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

/**
 * FR-3 fix: the single write-path for every stock-affecting event. Every
 * caller (checkout, admin restock, cancellation, return) goes through this
 * service instead of touching inventory.quantity directly, and stock
 * decrements use row-level locking to prevent overselling under concurrent
 * checkouts.
 */
class InventoryService
{
    public function recordSale(Book $book, int $qty, ?int $orderId = null): void
    {
        $this->applyTransaction($book, 'sale', -$qty, $orderId);
    }

    public function recordRestock(Book $book, int $qty): void
    {
        $this->applyTransaction($book, 'restock', $qty, null);
    }

    public function recordCancel(Book $book, int $qty, ?int $orderId = null): void
    {
        $this->applyTransaction($book, 'cancel', $qty, $orderId);
    }

    public function recordReturn(Book $book, int $qty, ?int $orderId = null): void
    {
        $this->applyTransaction($book, 'return', $qty, $orderId);
    }

    protected function applyTransaction(Book $book, string $type, int $signedQty, ?int $orderId): void
    {
        DB::transaction(function () use ($book, $type, $signedQty, $orderId) {
            // Row-level lock on the inventory row prevents two concurrent
            // checkouts from overselling the same title.
            $inventory = Inventory::where('book_id', $book->id)->lockForUpdate()->first()
                ?? Inventory::create(['book_id' => $book->id, 'quantity' => 0]);

            InventoryTransaction::create([
                'book_id' => $book->id,
                'transaction_type' => $type,
                'change_qty' => $signedQty,
                'order_id' => $orderId,
            ]);

            $inventory->increment('quantity', $signedQty);
        });
    }

    /** Recompute quantity from the ledger — useful for a nightly reconciliation job. */
    public function reconcile(Book $book): int
    {
        $sum = InventoryTransaction::where('book_id', $book->id)->sum('change_qty');
        Inventory::updateOrCreate(['book_id' => $book->id], ['quantity' => $sum]);
        return $sum;
    }
}
