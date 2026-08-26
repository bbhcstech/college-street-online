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
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 10;
        $inventory = $this->filteredQuery($request)->orderBy('title')->paginate($perPage)->withQueryString();

        return view('publisher.inventory', compact('inventory', 'totalBooks', 'lowStockCount'));
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['csv', 'excel', 'print', 'pdf'], true), 404);
        $books = $this->filteredQuery($request)
            ->when($request->filled('ids'), fn ($query) => $query->whereIn('books.id', collect(explode(',', $request->query('ids')))->filter(fn ($id) => ctype_digit($id))))
            ->orderBy('title')->get();

        if ($type === 'csv') {
            return response()->streamDownload(function () use ($books) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Title', 'ISBN', 'Current Stock', 'Low-stock Threshold', 'Stock Status', 'Book Status']);
                foreach ($books as $book) {
                    $quantity = $book->inventory?->quantity ?? 0;
                    $threshold = $book->inventory?->low_stock_threshold ?? 5;
                    fputcsv($output, [$book->title, $book->isbn, $quantity, $threshold, $quantity <= $threshold ? 'Low stock' : 'Healthy', ucfirst($book->status)]);
                }
                fclose($output);
            }, 'inventory-'.now()->format('Y-m-d').'.csv');
        }

        if ($type === 'excel') {
            return response()->view('publisher.inventory-report', compact('books') + ['mode' => 'excel'])
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="inventory-'.now()->format('Y-m-d').'.xls"');
        }

        return view('publisher.inventory-report', compact('books') + ['mode' => $type]);
    }

    private function filteredQuery(Request $request)
    {
        return auth()->user()->publisher->books()->with('inventory')
            ->when($request->filled('q'), fn ($query) => $query->search(trim($request->query('q'))))
            ->when($request->status === 'active', fn ($query) => $query->where('status', 'active'))
            ->when($request->status === 'inactive', fn ($query) => $query->where('status', 'inactive'))
            ->when($request->stock === 'low', fn ($query) => $query->whereHas('inventory', fn ($inventory) => $inventory->whereColumn('quantity', '<=', 'low_stock_threshold')))
            ->when($request->stock === 'out', fn ($query) => $query->whereHas('inventory', fn ($inventory) => $inventory->where('quantity', 0)))
            ->when($request->stock === 'healthy', fn ($query) => $query->whereHas('inventory', fn ($inventory) => $inventory->whereColumn('quantity', '>', 'low_stock_threshold')));
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
