<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate(['uuid' => 'required|uuid']);

        Device::firstOrCreate(['uuid' => $data['uuid']]);

        return response()->json(['ok' => true]);
    }
}