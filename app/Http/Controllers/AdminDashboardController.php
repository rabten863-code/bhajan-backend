<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Bhajan;
use App\Models\Category;
use App\Models\FeedbackMessage;
use App\Models\LoginEvent;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Admins only');

        $logins = LoginEvent::with('user:id,name,role')->latest()->limit(10)->get()
            ->map(fn ($e) => [
                'type'   => 'login',
                'title'  => ($e->user?->name ?? 'A devotee') . ' logged in',
                'detail' => $e->user?->role === 'admin' ? 'Admin' : null,
                'at'     => $e->created_at->toIso8601String(),
            ]);

        $ratings = Rating::latest()->limit(10)->get()
            ->map(fn ($r) => [
                'type'   => 'rating',
                'title'  => 'New rating',
                'detail' => null,
                'stars'  => $r->stars,
                'at'     => $r->created_at->toIso8601String(),
            ]);

        $feedback = FeedbackMessage::latest()->limit(10)->get()
            ->map(fn ($f) => [
                'type'   => 'feedback',
                'title'  => ($f->name ?: 'Anonymous') . ' sent feedback',
                'detail' => Str::limit($f->message, 80),
                'at'     => $f->created_at->toIso8601String(),
            ]);

        $activity = collect()
            ->concat($logins)->concat($ratings)->concat($feedback)
            ->sortByDesc('at')->take(10)->values();

        return response()->json([
            'total_users'      => User::where('role', 'devotee')->count(),
            'total_categories' => Category::count(),
            'total_bhajans'    => Bhajan::where('status', 'published')->count(),
            'announcement'     => Announcement::latest()->first(
                ['id', 'title', 'message', 'type', 'event_at', 'created_at']
            ),
            'total_ratings'    => Rating::count(),
            'average_rating'   => round((float) Rating::avg('stars'), 1),
            'total_feedback'   => FeedbackMessage::count(),
            'recent_activity'  => $activity,
        ]);
    }

        public function reviews(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Admins only');

        $counts = Rating::selectRaw('stars, COUNT(*) as total')
            ->groupBy('stars')
            ->pluck('total', 'stars');

        $distribution = collect(range(5, 1))->map(fn ($s) => [
            'stars' => $s,
            'count' => (int) $counts->get($s, 0),
        ])->values();

        $recent = FeedbackMessage::latest()->limit(10)->get()
            ->map(fn ($f) => [
                'id'      => $f->id,
                'name'    => $f->name ?: 'Anonymous',
                'message' => $f->message,
                'at'      => $f->created_at->toIso8601String(),
            ]);

        return response()->json([
            'total_ratings'   => Rating::count(),
            'average_rating'  => round((float) Rating::avg('stars'), 1),
            'distribution'    => $distribution,
            'total_feedback'  => FeedbackMessage::count(),
            'recent_feedback' => $recent,
        ]);
    }
}