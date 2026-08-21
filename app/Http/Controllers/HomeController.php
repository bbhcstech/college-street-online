<?php
namespace App\Http\Controllers;

use App\Models\Book;

class HomeController extends Controller
{
    public function index()
    {
        return view('pages.home', [
            'newArrivals' => Book::active()->latest()->limit(4)->get(),
            'bestsellers' => Book::active()->inRandomOrder()->limit(4)->get(),
        ]);
    }
}
