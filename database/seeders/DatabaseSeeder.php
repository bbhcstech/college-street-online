<?php
namespace Database\Seeders;

use App\Models\{Author, Book, Category, Coupon, Inventory, Publisher, User};
use App\Services\TransliterationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Admin ---
        User::create([
            'name' => 'Site Admin', 'email' => 'admin@collegestreetonline.com',
            'password' => Hash::make('password'), 'role' => 'admin',
        ]);

        // --- Categories ---
        $categoryNames = ['Academic & Textbooks', 'Bengali Literature', 'Competitive Exams', "Children's Books"];
        $categories = collect($categoryNames)->map(fn ($n) => Category::create(['name' => $n, 'slug' => Str::slug($n)]));

        // --- Authors ---
        $authorNames = ['S.K. Roy', 'Rabindranath Tagore', 'Editorial Board', 'Dr. P. Chatterjee', 'Satyajit Ray', 'Bipan Chandra'];
        $authors = collect($authorNames)->map(fn ($n) => Author::create(['name' => $n]));

        // --- Publishers (2) ---
        $pub1User = User::create(['name' => 'Ganguram Publications', 'email' => 'ganguram@collegestreetonline.com', 'password' => Hash::make('password'), 'role' => 'publisher']);
        $pub1 = Publisher::create(['user_id' => $pub1User->id, 'business_name' => 'Ganguram Publications', 'contact_details' => 'College Street, Kolkata']);

        $pub2User = User::create(['name' => 'Bengal Academic House', 'email' => 'bengalacademic@collegestreetonline.com', 'password' => Hash::make('password'), 'role' => 'publisher']);
        $pub2 = Publisher::create(['user_id' => $pub2User->id, 'business_name' => 'Bengal Academic House', 'contact_details' => 'Boipara, Kolkata']);

        // --- Demo customer ---
        User::create(['name' => 'Demo Customer', 'email' => 'customer@collegestreetonline.com', 'password' => Hash::make('password'), 'role' => 'customer']);

        // --- Books ---
        $transliterator = app(TransliterationService::class);
        $bookSeeds = [
            ['Bengali Grammar Simplified', 349, 449, 12, 0],
            ['Physics for Class XII', 520, 599, 4, 0],
            ['WBCS Preliminary Guide 2026', 640, 750, 20, 0],
            ["Tagore's Selected Poems", 220, null, 30, 1],
            ['Organic Chemistry Essentials', 480, 560, 15, 0],
            ["Panchatantra for Children", 180, 220, 25, 3],
            ['History of Modern India', 410, null, 6, 5],
            ['NEET Biology Crash Course', 590, 690, 18, 0],
        ];
        foreach ($bookSeeds as $i => [$title, $price, $mrp, $stock, $authorIdx]) {
            $book = Book::create([
                'publisher_id' => $i % 2 === 0 ? $pub1->id : $pub2->id,
                'category_id' => $categories[$i % $categories->count()]->id,
                'author_id' => $authors[$authorIdx]->id,
                'title' => $title,
                'title_transliterated' => $transliterator->transliterate($title),
                'isbn' => '978-93-' . str_pad((string) (1000 + $i), 4, '0', STR_PAD_LEFT) . '-' . rand(10, 99) . '-' . rand(1, 9),
                'price' => $price,
                'mrp' => $mrp,
                'description' => "A well-regarded title in the {$categories[$i % $categories->count()]->name} category.",
                'status' => 'active',
            ]);
            Inventory::create(['book_id' => $book->id, 'quantity' => $stock, 'low_stock_threshold' => 5]);
        }

        // --- Coupons ---
        Coupon::create(['code' => 'WELCOME10', 'discount_type' => 'percentage', 'discount_value' => 10, 'min_order_value' => 300, 'usage_limit' => 500, 'is_active' => true]);
        Coupon::create(['code' => 'FLAT50', 'discount_type' => 'fixed', 'discount_value' => 50, 'min_order_value' => 500, 'usage_limit' => 200, 'is_active' => true]);
    }
}
