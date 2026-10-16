<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    // Devotees: list of messages with read flags
    public function index(Request $request)
    {
        $since = $this->readSince($request->user());

        $items = Announcement::latest()->limit(50)
            ->get(['id', 'title', 'message', 'type', 'event_at', 'created_at'])
            ->map(fn ($a) => [
                'id'         => $a->id,
                'title'      => $a->title,
                'message'    => $a->message,
                'type'       => $a->type,
                'event_at'   => $a->event_at?->toIso8601String(),
                'created_at' => $a->created_at->toIso8601String(),
                'is_read'    => $a->created_at->lte($since),
            ]);

        return response()->json([
            'unread_count' => Announcement::where('created_at', '>', $since)->count(),
            'items'        => $items,
        ]);
    }

    // Lightweight count for the badge
    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => Announcement::where('created_at', '>', $this->readSince($request->user()))->count(),
        ]);
    }

    // Marks everything up to the newest message the app displayed as read
    public function markRead(Request $request)
    {
        $data = $request->validate(['up_to' => 'required|date']);

        $upTo = Carbon::parse($data['up_to']);
        $user = $request->user();

        if (! $user->announcements_read_at || $upTo->gt($user->announcements_read_at)) {
            $user->forceFill(['announcements_read_at' => $upTo])->save();
        }

        return response()->json(['ok' => true]);
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

    private function readSince(User $user): Carbon
    {
        return $user->announcements_read_at ?? $user->created_at;
    }
}