<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Bhajan;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::withCount(['bhajans' => fn ($q) => $q->where('status', 'published')])
            ->orderBy('id')
            ->get();
    }

    public function bhajans(Category $category)
    {
        $bhajans = $category->bhajans()
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['bhajans.id', 'bhajans.title'])
            ->makeHidden('pivot');

        return ['category' => $category, 'bhajans' => $bhajans];
    }

    // ---- Admin only ----

    public function store(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Admins only');

        $data = $request->validate([
            'name' => 'required|string|max:120|unique:categories,name',
        ]);

        $category = Category::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
        ]);

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Admins only');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120',
                Rule::unique('categories', 'name')->ignore($category->id)],
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name'], $category->id),
        ]);

        return $category;
    }

    public function destroy(Request $request, Category $category)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Admins only');

        DB::transaction(function () use ($category) {
            // Delete the bhajans in this category that aren't in any other category
            Bhajan::whereHas('categories', fn ($q) => $q->where('categories.id', $category->id))
                ->whereDoesntHave('categories', fn ($q) => $q->where('categories.id', '!=', $category->id))
                ->delete();

            $category->delete();
        });

        return response()->json(['message' => 'Deleted']);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $i = 2;

        while (Category::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}