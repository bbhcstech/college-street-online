<?php
namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;

class DashboardController extends Controller
{
    public function index()
    {
        $publisher = auth()->user()->publisher;
        $books = $publisher->books();
        return view('publisher.dashboard', [
            'bookCount' => $books->count(),
            'lowStockCount' => $books->whereHas('inventory', fn ($q) => $q->whereColumn('quantity', '<=', 'low_stock_threshold'))->count(),
            'recentOrderItems' => OrderItem::whereHas('book', fn ($q) => $q->where('publisher_id', $publisher->id))
                ->with(['book', 'order'])->latest()->limit(8)->get(),
        ]);
    }
}
