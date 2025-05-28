<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        return inertia('Chat');
    }

    public function send(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|integer',
            'username' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'chat_id' => $request->chat_id,
            'username' => $request->username,
            'body' => $request->body,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }

    public function messages(int $chatId)
    {
        return Message::where('chat_id', $chatId)->orderBy('created_at')->get();
    }
}
