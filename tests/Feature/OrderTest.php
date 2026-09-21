<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Country;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_orders_page(): void
    {
        Country::firstOrCreate(
            ['code' => 'IN'],
            ['name' => 'India', 'phone_code' => '+91', 'currency_code' => 'INR', 'is_active' => true]
        );

        $customer = User::create([
            'name' => 'Order Customer',
            'email' => 'ordercust_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'country_code' => 'IN',
        ]);

        $pubUser = User::create([
            'name' => 'Pub User',
            'email' => 'pub_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'publisher',
        ]);

        $publisher = Publisher::create([
            'user_id' => $pubUser->id,
            'business_name' => 'Kolkata Books',
        ]);

        $category = Category::firstOrCreate(['slug' => 'history'], ['name' => 'History']);
        $author = Author::firstOrCreate(['name' => 'RC Majumdar']);

        $book = Book::create([
            'publisher_id' => $publisher->id,
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Ancient India',
            'isbn' => '978-' . rand(1000000000, 9999999999),
            'price' => 450.00,
            'status' => 'active',
        ]);

        Inventory::create([
            'book_id' => $book->id,
            'quantity' => 20,
        ]);

        $order = Order::create([
            'order_number' => 'CSO-' . strtoupper(uniqid()),
            'customer_id' => $customer->id,
            'total_amount' => 450.00,
            'shipping_address' => '123 College Street, Kolkata',
            'contact_phone' => '+91 9876543210',
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'bank_transfer',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book->id,
            'publisher_id' => $publisher->id,
            'quantity' => 1,
            'unit_price' => 450.00,
            'line_total' => 450.00,
        ]);

        $response = $this->actingAs($customer)->get(route('account.orders'));
        $response->assertStatus(200);
        $response->assertSee($order->order_number);
    }
}

