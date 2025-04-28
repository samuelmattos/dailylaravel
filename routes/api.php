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

