<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Bhajan;
use App\Models\Category;
use App\Models\Device;

class StatsController extends Controller
{
    public function index()
    {
        return response()->json([
            'total_users'      => Device::count(),
            'total_categories' => Category::count(),
            'total_bhajans'    => Bhajan::where('status', 'published')->count(),
            'announcement'     => Announcement::latest()->first(
                ['id', 'title', 'message', 'type', 'event_at', 'created_at']
            ),
        ]);
    }
}