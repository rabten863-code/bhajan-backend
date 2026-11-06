<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $since = $this->since($request);

        $items = Announcement::latest()->limit(50)
            ->get(['id', 'title', 'message', 'type', 'event_at', 'created_at'])
            ->map(fn ($a) => [
                'id'         => $a->id,
                'title'      => $a->title,
                'message'    => $a->message,
                'type'       => $a->type,
                'event_at'   => $a->event_at?->toIso8601String(),
                'created_at' => $a->created_at->toIso8601String(),
                'is_read'    => $since ? $a->created_at->lte($since) : true,
            ]);

        return response()->json([
            'unread_count' => $since ? Announcement::where('created_at', '>', $since)->count() : 0,
            'items'        => $items,
        ]);
    }

    public function unreadCount(Request $request)
    {
        $since = $this->since($request);

        return response()->json([
            'unread_count' => $since ? Announcement::where('created_at', '>', $since)->count() : 0,
            'latest_at'    => Announcement::latest()->first(['created_at'])?->created_at->toIso8601String(),
        ]);
    }

    // Admin only
    public function store(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Admins only');

        $data = $request->validate([
            'title'    => 'required|string|max:120',
            'message'  => 'required|string|max:1000',
            'type'     => 'required|in:event,song',
            'event_at' => 'nullable|date',
        ]);

        $a = Announcement::create($data + ['created_by' => $request->user()->id]);

        return response()->json($a, 201);
    }

    private function since(Request $request): ?Carbon
    {
        $value = $request->query('since');
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}