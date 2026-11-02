<?php

namespace App\Http\Controllers;

use App\Models\FirebaseDevice;
use Illuminate\Http\Request;

class FirebaseDeviceController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:512'],
        ]);

        FirebaseDevice::query()->updateOrCreate(
            ['token' => $data['token']],
            ['user_id' => $request->user()->id],
        );

        return response()->json(['message' => 'This browser is registered for push notifications.']);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:512'],
        ]);

        $request->user()->firebaseDevices()->where('token', $data['token'])->delete();

        return response()->json(['message' => 'Push notifications disabled for this browser.']);
    }
}
