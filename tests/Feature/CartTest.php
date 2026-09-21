<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Country;
use App\Models\Inventory;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function setupCustomerAndBook(): array
    {
        Country::firstOrCreate(
            ['code' => 'IN'],
            ['name' => 'India', 'phone_code' => '+91', 'currency_code' => 'INR', 'is_active' => true]
        );

        $customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer_' . uniqid() . '@example.com',
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
            'business_name' => 'Bengal Publishing',
        ]);

        $category = Category::firstOrCreate(['slug' => 'fiction'], ['name' => 'Fiction']);
        $author = Author::firstOrCreate(['name' => 'Satyajit Ray']);

        $book = Book::create([
            'publisher_id' => $publisher->id,
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Professor Shonku',
            'isbn' => '978-' . rand(1000000000, 9999999999),
            'price' => 200.00,
            'status' => 'active',
        ]);

        Inventory::create([
            'book_id' => $book->id,
            'quantity' => 15,
        ]);

        return [$customer, $book];
    }

    public function test_customer_can_add_book_to_cart(): void
    {
        [$customer, $book] = $this->setupCustomerAndBook();

        $response = $this->actingAs($customer)->post(route('cart.store'), [
            'book_id' => $book->id,
            'quantity' => 2,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('carts', [
            'customer_id' => $customer->id,
            'book_id' => $book->id,
            'quantity' => 2,
        ]);
    }

    public function test_customer_can_view_cart_page(): void
    {
        [$customer, $book] = $this->setupCustomerAndBook();

        Cart::create([
            'customer_id' => $customer->id,
            'book_id' => $book->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($customer)->get(route('cart.index'));
        $response->assertStatus(200);
        $response->assertSee('Professor Shonku');
    }

    public function test_customer_can_remove_item_from_cart(): void
    {
        [$customer, $book] = $this->setupCustomerAndBook();

        $cart = Cart::create([
            'customer_id' => $customer->id,
            'book_id' => $book->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($customer)->delete(route('cart.destroy', $cart->id));
        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseMissing('carts', [
            'id' => $cart->id,
        ]);
    }
}

