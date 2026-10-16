<?php

namespace App\Http\Controllers;

use App\Models\FeedbackMessage;
use App\Models\Rating;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function rate(Request $request)
    {
        $data = $request->validate(['stars' => 'required|integer|min:1|max:5']);
        Rating::create($data);

        return response()->json(['message' => 'Thank you'], 201);
    }

    public function feedback(Request $request)
    {
        $data = $request->validate([
            'name'    => 'nullable|string|max:100',
            'message' => 'required|string|min:5|max:1000',
        ]);
        FeedbackMessage::create($data);

        return response()->json(['message' => 'Thank you'], 201);
    }
}