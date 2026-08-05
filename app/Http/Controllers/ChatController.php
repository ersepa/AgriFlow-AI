<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
public function chat(Request $request)
{
    $prompt = "Jawab pertanyaan berikut dalam Bahasa Indonesia:\n\n" . $request->message;

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
        'Content-Type' => 'application/json',
    ])->post('https://openrouter.ai/api/v1/chat/completions', [
        'model' => 'meta-llama/llama-3.1-8b-instruct',
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt,
            ]
        ]
    ]);

    return response()->json($response->json());
}
}
