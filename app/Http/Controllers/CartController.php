<?php
namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Cart;
use App\Services\PricingService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(PricingService $pricing)
    {
        $items = Cart::with('book.author')->where('customer_id', auth()->id())->get();
        $quote = $pricing->quote($items, 'IN');
        return view('pages.cart', compact('items', 'quote'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['book_id' => 'required|exists:books,id', 'quantity' => 'nullable|integer|min:1']);
        $cart = Cart::firstOrNew(['customer_id' => auth()->id(), 'book_id' => $data['book_id']]);
        $cart->quantity = ($cart->exists ? $cart->quantity : 0) + ($data['quantity'] ?? 1);
        $cart->save();
        return back()->with('success', 'Added to cart.');
    }

    public function update(Request $request, Cart $cart, PricingService $pricing)
    {
        abort_unless($cart->customer_id === auth()->id(), 403);
        $data = $request->validate(['quantity' => 'required|integer|min:1']);
        $cart->update($data);

        if ($request->expectsJson()) {
            $cart->load('book');
            $items = Cart::with('book')->where('customer_id', auth()->id())->get();

            return response()->json([
                'line_total' => $cart->lineTotal(),
                'quote' => $pricing->quote($items, 'IN'),
            ]);
        }

        return back();
    }

    public function destroy(Cart $cart)
    {
        abort_unless($cart->customer_id === auth()->id(), 403);
        $cart->delete();
        return back()->with('success', 'Removed from cart.');
    }
}
