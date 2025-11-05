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
        $roomName = 'demo-rom11';

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
                    'enable_screenshare' => true,
                    'enable_transcription_storage' => true,
                    'auto_transcription_settings' => [
                        'language' => 'pt-BR',
                        'model' => 'nova-3',
                        'punctuate' => true,
                        'profanity_filter' => false
                    ]
                ],
            ]);

        return $response->json();
    }


    public function startRecording(Request $request)
    {
        $roomName = 'demo-rom11';
        $apiKey = $request->input('apiKey') ?? env('DAILY_API_KEY');

        // Inicia a gravação
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
            // Inicia a transcrição em português do Brasil
            $transcriptionResponse = Http::withToken($apiKey)
                ->post("https://api.daily.co/v1/rooms/{$roomName}/transcription/start", [
                    'language' => 'pt-BR',
                    'model' => 'nova-3',
                    'punctuate' => true,
                    'profanity_filter' => false
                ]);

            $transcriptionData = $transcriptionResponse->json();

            return response()->json([
                'recordingId' => $data['recordingId'],
                'transcription' => $transcriptionData
            ]);
        }
        return response()->json(['error' => $data], $response->status());
    }

    public function stopRecording(Request $request)
    {
        $roomName = 'demo-rom11';
        $apiKey = $request->input('apiKey') ?? env('DAILY_API_KEY');

        // Para a gravação
        $response = Http::withToken($apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->withBody(json_encode((object) []), 'application/json')
            ->post("https://api.daily.co/v1/rooms/{$roomName}/recordings/stop");

        $data = $response->json();
        if ($response->successful()) {
            // Para a transcrição
            $transcriptionResponse = Http::withToken($apiKey)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->withBody(json_encode((object) []), 'application/json')
                ->post("https://api.daily.co/v1/rooms/{$roomName}/transcription/stop");

            $transcriptionData = $transcriptionResponse->json();

            // Aqui você pode salvar $data['id'] ou outros campos no banco, se quiser
            return response()->json([
                'recording' => $data,
                'transcription' => $transcriptionData
            ]);
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

    public function startTranscription(Request $request)
    {
        $roomName = 'demo-rom11';
        $apiKey = $request->input('apiKey') ?? env('DAILY_API_KEY');

        $response = Http::withToken($apiKey)
            ->post("https://api.daily.co/v1/rooms/{$roomName}/transcription/start", [
                'language' => 'pt-BR',
                'model' => 'nova-3',
                'punctuate' => true,
                'profanity_filter' => false
            ]);

        return $response->json();
    }

    public function stopTranscription(Request $request)
    {
        $roomName = 'demo-rom11';
        $apiKey = $request->input('apiKey') ?? env('DAILY_API_KEY');

        $response = Http::withToken($apiKey)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->withBody(json_encode((object) []), 'application/json')
            ->post("https://api.daily.co/v1/rooms/{$roomName}/transcription/stop");

        return $response->json();
    }

    public function createMeetingToken(Request $request)
    {
        $roomName = 'demo-rom11';
        $apiKey = $request->input('apiKey') ?? env('DAILY_API_KEY');

        $response = Http::withToken($apiKey)
            ->post('https://api.daily.co/v1/meeting-tokens', [
                'properties' => [
                    'room_name' => $roomName,
                    'is_owner' => true,
                    'auto_start_transcription' => true,
                    'user_name' => $request->input('user_name', 'Admin'),
                    'exp' => time() + (24 * 60 * 60), // Token válido por 24 horas
                ]
            ]);

        return $response->json();
    }

    public function listTranscripts(Request $request)
    {
        $apiKey = $request->input('apiKey') ?? env('DAILY_API_KEY');
        $response = Http::withToken($apiKey)
            ->get('https://api.daily.co/v1/transcript');

        if ($response->successful()) {
            return $response->json(); // Vai retornar uma lista com as transcrições
        }

        return response()->json([
            'error' => 'Unable to fetch transcripts',
            'status' => $response->status(),
        ], $response->status());
    }

    public function getTranscript(Request $request, $transcriptId)
    {
        $apiKey = $request->input('apiKey') ?? env('DAILY_API_KEY');
        $response = Http::withToken($apiKey)
            ->get("https://api.daily.co/v1/transcript/$transcriptId/access-link");

        return $response->json();
    }
}
