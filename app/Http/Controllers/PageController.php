<?php
namespace App\Http\Controllers;

class PageController extends Controller
{
    public function about() { return view('pages.about'); }
    public function bulkOrders() { return view('pages.bulk-orders'); }
    public function bookRights() { return view('pages.book-rights'); }
}
