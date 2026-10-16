<?php

namespace App\Http\Controllers;

use App\Models\LoginEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'number' => 'required|string|max:50',
        ]);

        $user = User::where('role', 'admin')
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($data['name'])])
            ->first();

        if (! $user || ! Hash::check($data['number'], $user->password)) {
            throw ValidationException::withMessages([
                'name' => ['Invalid name or number.'],
            ]);
        }

        LoginEvent::create(['user_id' => $user->id]);

        return response()->json([
            'token' => $user->createToken('admin')->plainTextToken,
            'user'  => $user,
        ]);
    }
}