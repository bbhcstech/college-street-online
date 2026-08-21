<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories', [
            'categories' => Category::withCount('books')->orderBy('name')->get(),
            'authors' => Author::withCount('books')->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:150']);
        Category::create(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);
        return back()->with('success', 'Category added.');
    }

    public function storeAuthor(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:150']);
        Author::create($data);
        return back()->with('success', 'Author added.');
    }
}
