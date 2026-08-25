<?php
namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /** FR-3: search/filter on the inventory screen (previously missing per the SRS known-issues log). */
    public function index(Request $request)
    {
        $publisher = auth()->user()->publisher;
        $totalBooks = $publisher->books()->count();
        $lowStockCount = $publisher->books()
            ->whereHas('inventory', fn ($query) => $query->whereColumn('quantity', '<=', 'low_stock_threshold'))
            ->count();
        $inventory = $publisher->books()
            ->with('inventory')
            ->when($request->query('q'), fn ($q, $term) => $q->search($term))
            ->when($request->boolean('low_only'), fn ($q) => $q->whereHas('inventory', fn ($i) => $i->whereColumn('quantity', '<=', 'low_stock_threshold')))
            ->paginate(15)
            ->withQueryString();

        return view('publisher.inventory', compact('inventory', 'totalBooks', 'lowStockCount'));
    }

    public function restock(Request $request, \App\Models\Book $book, InventoryService $inventoryService)
    {
        abort_unless($book->publisher_id === auth()->user()->publisher->id, 403);
        $data = $request->validate(['quantity' => 'required|integer|min:1']);
        $inventoryService->recordRestock($book, $data['quantity']);
        return back()->with('success', 'Stock updated.');
    }

    public function reduce(Request $request, \App\Models\Book $book, InventoryService $inventoryService)
    {
        abort_unless($book->publisher_id === auth()->user()->publisher->id, 403);
        $data = $request->validate(['quantity' => 'required|integer|min:1']);
        $inventoryService->recordAdjustment($book, -$data['quantity']);
        return back()->with('success', 'Stock reduced.');
    }

    public function adjust(Request $request, \App\Models\Book $book, InventoryService $inventoryService)
    {
        abort_unless($book->publisher_id === auth()->user()->publisher->id, 403);
        $data = $request->validate([
            'quantity' => 'required|integer|not_in:0',
        ], [
            'quantity.not_in' => 'Enter a positive number to add stock or a negative number to reduce it.',
        ]);

        $quantity = (int) $data['quantity'];
        if ($quantity > 0) {
            $inventoryService->recordRestock($book, $quantity);
        } else {
            $inventoryService->recordAdjustment($book, $quantity);
        }

        return back()->with('success', 'Stock adjusted successfully.');
    }
}
