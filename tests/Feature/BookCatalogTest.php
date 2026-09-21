<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function createBook(array $attributes = []): Book
    {
        $user = User::create([
            'name' => 'Publisher User',
            'email' => 'pub_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'publisher',
        ]);

        $publisher = Publisher::create([
            'user_id' => $user->id,
            'business_name' => 'Academic Press',
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'literature'],
            ['name' => 'Literature']
        );

        $author = Author::firstOrCreate(
            ['name' => 'Rabindranath Tagore']
        );

        $book = Book::create(array_merge([
            'publisher_id' => $publisher->id,
            'category_id' => $category->id,
            'author_id' => $author->id,
            'title' => 'Gitanjali',
            'isbn' => '978-' . rand(1000000000, 9999999999),
            'price' => 250.00,
            'mrp' => 300.00,
            'status' => 'active',
        ], $attributes));

        Inventory::create([
            'book_id' => $book->id,
            'quantity' => 10,
        ]);

        return $book;
    }

    public function test_browse_books_page_lists_active_books(): void
    {
        $book = $this->createBook(['title' => 'Feluda Samagra']);

        $response = $this->get(route('books.index'));
        $response->assertStatus(200);
        $response->assertSee('Feluda Samagra');
    }

    public function test_single_book_page_loads_successfully(): void
    {
        $book = $this->createBook(['title' => 'Byomkesh Bakshi']);

        $response = $this->get(route('books.show', $book));
        $response->assertStatus(200);
        $response->assertSee('Byomkesh Bakshi');
    }

    public function test_book_search_suggestions_endpoint_returns_json(): void
    {
        $book = $this->createBook(['title' => 'Charulata Story']);

        $response = $this->getJson('/books/suggestions?q=Charu');
        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Charulata Story']);
    }
}

