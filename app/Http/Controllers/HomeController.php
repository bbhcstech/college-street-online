<?php
namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\OrderItem;

class HomeController extends Controller
{
    public function index()
    {
        $recommendedBooks = collect();

        if (auth()->check() && auth()->user()->isCustomer()) {
            $orderedBookIds = OrderItem::whereHas('order', fn ($query) =>
                $query->where('customer_id', auth()->id())
            )->pluck('book_id');

            $preferredCategoryIds = Book::whereIn('id', $orderedBookIds)
                ->whereNotNull('category_id')
                ->pluck('category_id')
                ->unique();

            if ($preferredCategoryIds->isNotEmpty()) {
                $recommendedBooks = Book::active()
                    ->whereIn('category_id', $preferredCategoryIds)
                    ->whereNotIn('id', $orderedBookIds)
                    ->inRandomOrder()
                    ->limit(4)
                    ->get();
            }
        }

        return view('pages.home', [
            'newArrivals' => Book::active()->latest()->limit(4)->get(),
            'bestsellers' => Book::active()
                ->withSum(['orderItems as units_sold' => fn ($query) =>
                    $query->whereHas('order', fn ($order) => $order->whereIn('status', ['delivered', 'completed']))
                ], 'quantity')
                ->orderByDesc('units_sold')
                ->limit(4)
                ->get(),
            'deals' => Book::active()
                ->whereNotNull('mrp')
                ->whereColumn('mrp', '>', 'price')
                ->orderByRaw('((mrp - price) / mrp) DESC')
                ->limit(4)
                ->get(),
            'recommendedBooks' => $recommendedBooks,
        ]);
    }
}
