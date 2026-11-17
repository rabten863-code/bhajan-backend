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

        public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        // escape wildcard characters the user may type
        $like = '%' . addcslashes($q, '\\%_') . '%';

        return Bhajan::where('status', 'published')
            ->where(fn ($w) => $w->where('title', 'ilike', $like)
                                 ->orWhere('lyrics', 'ilike', $like))
            ->with('categories:id,name')
            ->orderByRaw('CASE WHEN title ILIKE ? THEN 0 ELSE 1 END', [$like])
            ->orderBy('title')
            ->limit(30)
            ->get(['id', 'title'])
            ->map(fn ($b) => [
                'id'       => $b->id,
                'title'    => $b->title,
                'category' => $b->categories->first()?->name,
            ]);
    }
}