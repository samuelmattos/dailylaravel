<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DailyRoomController extends Controller
{
    public function createRoom()
    {
        $roomName = 'demo-rom10';

        $check = Http::withToken(env('DAILY_API_KEY'))
        ->get("https://api.daily.co/v1/rooms/{$roomName}");

        if ($check->status() === 200) {
            return $check->json(); // Reutiliza a sala
        }

        $response = Http::withToken(env('DAILY_API_KEY'))
            ->post('https://api.daily.co/v1/rooms', [
                'name' => $roomName,
                'properties' => [
                    'enable_recording' => 'cloud',
                    'max_participants' => 2,
                    'start_video_off' => false,
                    'start_audio_off' => false,
                    'enable_chat' => true,
                    'enable_screenshare' => true
                ],
            ]);

        return $response->json();
    }

    public function listRecordings()
    {
        $response = Http::withToken(env('DAILY_API_KEY'))
            ->get('https://api.daily.co/v1/recordings');

        if ($response->successful()) {
            return $response->json(); // Vai retornar uma lista com as gravações
        }

        return response()->json([
            'error' => 'Unable to fetch recordings',
            'status' => $response->status(),
        ], $response->status());
    }

    public function getRecording($meetingId)
    {
        $response = Http::withToken(env('DAILY_API_KEY'))
            ->get("https://api.daily.co/v1/recordings/$meetingId/access-link");

        return $response->json();
    }
}
