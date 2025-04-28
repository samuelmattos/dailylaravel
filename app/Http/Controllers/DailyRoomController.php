<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DailyRoomController extends Controller
{
    public function createRoom(Request $request)
    {
        $apiKey = $request->input('apiKey') ?? env('DAILY_API_KEY');
        $roomName = 'demo-rom10';

        $check = Http::withToken($apiKey)
        ->get("https://api.daily.co/v1/rooms/{$roomName}");

        if ($check->status() === 200) {
            return $check->json(); // Reutiliza a sala
        }

        $response = Http::withToken($apiKey)
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


    public function startRecording(Request $request)
    {
        $roomName = 'demo-rom10';
        $apiKey = $request->input('apiKey') ?? env('DAILY_API_KEY');
        $response = Http::withToken($apiKey)
            ->post("https://api.daily.co/v1/rooms/{$roomName}/recordings/start", [
                'width' => 854,
                'height' => 480,
                'fps' => 24,
                'videoBitrate' => 1000,
                'audioBitrate' => 64,
                'layout' => [
                    'preset' => 'default'
                ]
            ]);
        $data = $response->json();
        if ($response->successful()) {
            // Aqui você pode salvar $data['id'] ou outros campos no banco, se quiser
            return response()->json($data);
        }
        return response()->json(['error' => $data], $response->status());
    }

    public function stopRecording(Request $request)
    {
        $roomName = 'demo-rom10';
        $apiKey = $request->input('apiKey') ?? env('DAILY_API_KEY');
        $response = Http::withToken($apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->withBody(json_encode((object) []), 'application/json')
            ->post("https://api.daily.co/v1/rooms/{$roomName}/recordings/stop");

        $data = $response->json();
        if ($response->successful()) {

            // Aqui você pode salvar $data['id'] ou outros campos no banco, se quiser
            return response()->json($data);
        }

        return response()->json(['error' => $data], $response->status());
    }

    public function listRecordings(Request $request)
    {
        $apiKey = $request->input('apiKey') ?? env('DAILY_API_KEY');
        $response = Http::withToken($apiKey)
            ->get('https://api.daily.co/v1/recordings');

        if ($response->successful()) {
            return $response->json(); // Vai retornar uma lista com as gravações
        }

        return response()->json([
            'error' => 'Unable to fetch recordings',
            'status' => $response->status(),
        ], $response->status());
    }

    public function getRecording(Request $request, $meetingId)
    {
        $apiKey = $request->input('apiKey') ?? env('DAILY_API_KEY');
        $response = Http::withToken($apiKey)
            ->get("https://api.daily.co/v1/recordings/$meetingId/access-link");

        return $response->json();
    }
}
