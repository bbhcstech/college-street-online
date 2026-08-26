<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories', [
            'categories' => Category::withTrashed()->withCount(['books' => fn ($query) => $query->withTrashed()])->orderBy('name')->get(),
            'authors' => Author::withTrashed()->withCount(['books' => fn ($query) => $query->withTrashed()])->orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:150|unique:categories,name']);
        $name = trim($data['name']);
        $slug = $this->uniqueCategorySlug($name);
        Category::create(compact('name', 'slug'));
        return back()->with('success', 'Category added.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('categories', 'name')->ignore($category->id)],
        ]);
        $name = trim($data['name']);
        $category->update(['name' => $name, 'slug' => $this->uniqueCategorySlug($name, $category)]);
        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Category archived. Existing books were not changed.');
    }

    public function restoreCategory(Category $category)
    {
        $category->restore();
        return back()->with('success', 'Category restored.');
    }

    public function forceDestroyCategory(int $id)
    {
        $category = Category::onlyTrashed()->findOrFail($id);

        if ($category->books()->withTrashed()->exists()) {
            return back()->withErrors(['category' => 'This category cannot be permanently deleted because books are linked to it.']);
        }

        $category->forceDelete();
        return back()->with('success', 'Category permanently deleted.');
    }

    public function storeAuthor(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:150|unique:authors,name']);
        Author::create(['name' => trim($data['name'])]);
        return back()->with('success', 'Author added.');
    }

    public function updateAuthor(Request $request, Author $author)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('authors', 'name')->ignore($author->id)],
        ]);
        $author->update(['name' => trim($data['name'])]);
        return back()->with('success', 'Author updated.');
    }

    public function destroyAuthor(Author $author)
    {
        $author->delete();
        return back()->with('success', 'Author archived. Existing books were not changed.');
    }

    public function restoreAuthor(Author $author)
    {
        $author->restore();
        return back()->with('success', 'Author restored.');
    }

    public function forceDestroyAuthor(int $id)
    {
        $author = Author::onlyTrashed()->findOrFail($id);

        if ($author->books()->withTrashed()->exists()) {
            return back()->withErrors(['author' => 'This author cannot be permanently deleted because books are linked to them.']);
        }

        $author->forceDelete();
        return back()->with('success', 'Author permanently deleted.');
    }

    protected function uniqueCategorySlug(string $name, ?Category $ignore = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $suffix = 2;

        while (Category::where('slug', $slug)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))
            ->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
