<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DailyRoomController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// routes/api.php
Route::post('/create-room', [DailyRoomController::class, 'createRoom']);
Route::get('/recording/{meetingId}', [DailyRoomController::class, 'getRecording']);
Route::get('/recording', [DailyRoomController::class, 'listRecordings']);
Route::post('/recording/start', [DailyRoomController::class, 'startRecording']);
Route::post('/recording/stop', [DailyRoomController::class, 'stopRecording']);

// Rotas específicas para transcrição
Route::post('/transcription/start', [DailyRoomController::class, 'startTranscription']);
Route::post('/transcription/stop', [DailyRoomController::class, 'stopTranscription']);
Route::get('/transcripts', [DailyRoomController::class, 'listTranscripts']);
Route::get('/transcript/{transcriptId}', [DailyRoomController::class, 'getTranscript']);

// Rota para criar meeting token com auto-transcrição
Route::post('/meeting-token', [DailyRoomController::class, 'createMeetingToken']);
