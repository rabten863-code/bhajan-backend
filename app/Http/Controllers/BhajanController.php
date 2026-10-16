<?php

namespace App\Http\Controllers;

use App\Models\Bhajan;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BhajanController extends Controller
{
    public function show(Bhajan $bhajan)
    {
        abort_unless($bhajan->status === 'published', 404);

        return $bhajan->only(['id', 'title', 'lyrics']);
    }

    // ---- Admin only ----

    public function store(Request $request, Category $category)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Admins only');

        $data = $this->validated($request);

        $bhajan = DB::transaction(function () use ($data, $category) {
            $bhajan = Bhajan::create($data + ['status' => 'published']);
            $bhajan->categories()->attach($category->id);
            return $bhajan;
        });

        return response()->json($bhajan->only(['id', 'title']), 201);
    }

    public function update(Request $request, Bhajan $bhajan)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Admins only');

        $bhajan->update($this->validated($request));

        return $bhajan->only(['id', 'title']);
    }

    public function destroy(Request $request, Bhajan $bhajan)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Admins only');

        $bhajan->delete(); // category links are removed automatically

        return response()->json(['message' => 'Deleted']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'  => 'required|string|max:200',
            'lyrics' => 'required|string|max:50000',
        ]);
    }
}