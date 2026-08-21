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
        $inventory = $publisher->books()
            ->with('inventory')
            ->when($request->query('q'), fn ($q, $term) => $q->search($term))
            ->when($request->boolean('low_only'), fn ($q) => $q->whereHas('inventory', fn ($i) => $i->whereColumn('quantity', '<=', 'low_stock_threshold')))
            ->paginate(15)
            ->withQueryString();

        return view('publisher.inventory', compact('inventory'));
    }

    public function restock(Request $request, \App\Models\Book $book, InventoryService $inventoryService)
    {
        abort_unless($book->publisher_id === auth()->user()->publisher->id, 403);
        $data = $request->validate(['quantity' => 'required|integer|min:1']);
        $inventoryService->recordRestock($book, $data['quantity']);
        return back()->with('success', 'Stock updated.');
    }
}
