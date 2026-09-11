<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Inventory;
use App\Models\Publisher;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $publishers = Publisher::orderBy('business_name')->get();

        $totalBooks = Book::count();
        $inStockCount = Book::whereHas('inventory', fn ($q) => $q->where('quantity', '>', 0))->count();
        $lowStockCount = Book::whereHas('inventory', fn ($q) => $q->whereColumn('quantity', '<=', 'low_stock_threshold')->where('quantity', '>', 0))->count();
        $outOfStockCount = Book::whereHas('inventory', fn ($q) => $q->where('quantity', '<=', 0))->count()
            + Book::doesntHave('inventory')->count();

        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 15;

        $books = $this->filteredQuery($request)->orderBy('title')->paginate($perPage)->withQueryString();

        return view('admin.inventory.index', compact('books', 'publishers', 'totalBooks', 'inStockCount', 'lowStockCount', 'outOfStockCount'));
    }

    public function update(Request $request, Book $book, InventoryService $inventoryService)
    {
        $data = $request->validate([
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        $inventory = Inventory::firstOrCreate(
            ['book_id' => $book->id],
            ['quantity' => 0, 'low_stock_threshold' => 5]
        );

        $diff = (int)$data['quantity'] - (int)$inventory->quantity;
        if ($diff !== 0) {
            $inventoryService->recordAdjustment($book, $diff);
        }

        $inventory->update([
            'low_stock_threshold' => (int)$data['low_stock_threshold'],
        ]);

        return back()->with('success', "Inventory updated for '{$book->title}'.");
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['csv', 'excel', 'print', 'pdf'], true), 404);

        $books = $this->filteredQuery($request)->orderBy('title')->get();

        if ($type === 'csv') {
            return response()->streamDownload(function () use ($books) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Book Title', 'ISBN', 'Publisher', 'Current Stock', 'Low-stock Threshold', 'Stock Status', 'Book Status']);
                foreach ($books as $book) {
                    $quantity = $book->inventory?->quantity ?? 0;
                    $threshold = $book->inventory?->low_stock_threshold ?? 5;
                    $status = $quantity <= 0 ? 'Out of stock' : ($quantity <= $threshold ? 'Low stock' : 'Healthy');
                    fputcsv($output, [
                        $book->title,
                        $book->isbn,
                        $book->publisher?->business_name ?? 'N/A',
                        $quantity,
                        $threshold,
                        $status,
                        ucfirst($book->status)
                    ]);
                }
                fclose($output);
            }, 'admin-inventory-'.now()->format('Y-m-d').'.csv');
        }

        if ($type === 'excel') {
            return response()->view('admin.inventory.report', compact('books') + ['mode' => 'excel'])
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="admin-inventory-'.now()->format('Y-m-d').'.xls"');
        }

        return view('admin.inventory.report', compact('books') + ['mode' => $type]);
    }

    private function filteredQuery(Request $request)
    {
        return Book::with(['inventory', 'publisher', 'author', 'category'])
            ->when($request->filled('q'), fn ($query) => $query->search(trim($request->query('q'))))
            ->when($request->filled('publisher_id'), fn ($query) => $query->where('publisher_id', $request->publisher_id))
            ->when($request->status === 'active', fn ($query) => $query->where('status', 'active'))
            ->when($request->status === 'inactive', fn ($query) => $query->where('status', 'inactive'))
            ->when($request->stock === 'low', fn ($query) => $query->whereHas('inventory', fn ($i) => $i->whereColumn('quantity', '<=', 'low_stock_threshold')->where('quantity', '>', 0)))
            ->when($request->stock === 'out', fn ($query) => $query->whereHas('inventory', fn ($i) => $i->where('quantity', '<=', 0))->orDoesntHave('inventory'))
            ->when($request->stock === 'healthy', fn ($query) => $query->whereHas('inventory', fn ($i) => $i->whereColumn('quantity', '>', 'low_stock_threshold')));
    }
}

