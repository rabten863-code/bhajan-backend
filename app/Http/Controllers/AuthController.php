<?php

namespace App\Http\Controllers;

use App\Models\LoginEvent;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function devoteeLogin(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'phone' => ['required', 'regex:/^(16|17|77)[0-9]{6}$/'],
        ], [
            'phone.regex' => 'Enter a valid Bhutan number: 8 digits starting with 16, 17 or 77.',
        ]);

        $user = User::firstOrCreate(
            ['phone' => $data['phone']],
            ['name' => $data['name'], 'role' => 'devotee']
        );

        // Phone-only login must never open an admin account
        if ($user->role !== 'devotee') {
            return response()->json(['message' => 'Not allowed'], 403);
        }

        LoginEvent::create(['user_id' => $user->id]);

        return response()->json([
            'token' => $user->createToken('devotee')->plainTextToken,
            'user'  => $user,
        ]);
    }

    public function me(Request $request)
    {
        return $request->user();
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}