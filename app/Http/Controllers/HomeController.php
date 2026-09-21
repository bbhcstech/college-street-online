<?php
namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $recommendedBooks = collect();
        $activeOrders = collect();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->isCustomer()) {
            $userId = $user->id;
            $activeOrders = Order::where('customer_id', $userId)
                ->whereNotIn('status', ['delivered', 'completed', 'cancelled', 'returned'])
                ->withCount('items')
                ->with(['items.book'])
                ->latest()
                ->limit(3)
                ->get();

            $recentOrders = Order::where('customer_id', $userId)
                ->withCount('items')
                ->with(['items.book'])
                ->latest()
                ->limit(3)
                ->get();

            $orderedBookIds = OrderItem::whereHas('order', fn ($query) =>
                $query->where('customer_id', $userId)
            )->pluck('book_id');

            $preferredCategoryIds = Book::whereIn('id', $orderedBookIds)
                ->whereNotNull('category_id')
                ->pluck('category_id')
                ->unique();

            if ($preferredCategoryIds->isNotEmpty()) {
                $recommendedBooks = Book::active()
                    ->whereIn('category_id', $preferredCategoryIds)
                    ->whereNotIn('id', $orderedBookIds)
                    ->with(['author', 'category', 'inventory', 'markets'])
                    ->inRandomOrder()
                    ->limit(4)
                    ->get();
            }
        }

        $recentlyViewedIds = session()->get('recently_viewed_books', []);
        $recentlyViewedBooks = collect();

        if (!empty($recentlyViewedIds)) {
            $viewedBooksMap = Book::active()
                ->whereIn('id', $recentlyViewedIds)
                ->with(['author', 'category', 'inventory', 'markets'])
                ->get()
                ->keyBy('id');

            $recentlyViewedBooks = collect($recentlyViewedIds)
                ->map(fn ($id) => $viewedBooksMap->get($id))
                ->filter()
                ->take(4);
        }

        $availableCoupons = \App\Models\Coupon::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()))
            ->where(fn ($q) => $q->whereNull('valid_to')->orWhere('valid_to', '>=', now()))
            ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('times_used', '<', 'usage_limit'))
            ->latest()
            ->limit(4)
            ->get();

        $relations = ['author', 'category', 'inventory', 'markets'];

        return view('pages.home', [
            'newArrivals' => Book::active()->with($relations)->latest()->limit(4)->get(),
            'bestsellers' => Book::active()
                ->with($relations)
                ->withSum(['orderItems as units_sold' => fn ($query) =>
                    $query->whereHas('order', fn ($order) => $order->whereIn('status', ['delivered', 'completed']))
                ], 'quantity')
                ->orderByDesc('units_sold')
                ->limit(4)
                ->get(),
            'deals' => Book::active()
                ->with($relations)
                ->whereNotNull('mrp')
                ->whereColumn('mrp', '>', 'price')
                ->orderByRaw('((mrp - price) / mrp) DESC')
                ->limit(4)
                ->get(),
            'recommendedBooks' => $recommendedBooks,
            'recentlyViewedBooks' => $recentlyViewedBooks,
            'activeOrders' => $activeOrders,
            'recentOrders' => $recentOrders ?? collect(),
            'availableCoupons' => $availableCoupons,
        ]);
    }
}
