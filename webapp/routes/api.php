<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

// Chat routes
Route::post('/messages', [ChatController::class, 'send']);

// Fetch messages for a specific chat
Route::get('/chats/{chatId}/messages', [ChatController::class, 'messages']);
