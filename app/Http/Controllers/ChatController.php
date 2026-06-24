<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        // 1. Ambil data konteks
        $data = [
            'shipments' => \App\Models\Shipment::with('harvest')->latest()->take(10)->get(),
            'ai_insights' => \App\Models\AiAnalysis::latest()->take(5)->get()
        ];

        // 2. Buat prompt
// Di dalam ChatController.php
$prompt = "Kamu adalah asisten logistik yang santai. Jawab pertanyaan user dengan format yang sangat rapi.

ATURAN UTAMA:
Kamu adalah asisten logistik pertanian. KAMU HANYA BOLEH MENJAWAB pertanyaan seputar logistik, pertanian, data pengiriman, atau sistem aplikasi ini. 
Jika user bertanya tentang hal lain (seperti politik, tokoh, presiden, atau topik umum lainnya), jawab dengan kalimat: 'Sorry bro, gw gabisa jawab soal itu. Gw cuma bisa bahas soal logistik dan pertanian di sistem ini ya!'

Aturan penulisan:
- JANGAN gunakan label seperti 'Nama Rekomendasi' atau 'Saran Tindakan'.
- JANGAN gunakan huruf tebal (bold/asterisk **).
- JANGAN gunakan tanda kurung siku [ ].
- Gunakan Bahasa Indonesia yang gaul, santai, dan solutif.
- Gunakan simbol bullet point (•) untuk poin-poin.
- Langsung masuk ke pembahasan saja.

Format jawaban:
[Kalimat pembuka yang santai]

• Nama Rekomendasi: Penjelasan singkat kenapa ini penting + cara mudah menerapkannya.

Saran tindakan:
• Poin 1
• Poin 2
• Poin 3

Data sistem: " . json_encode($data) . 
"User bertanya: " . $request->message;

        // 3. Panggil API OpenRouter
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'meta-llama/llama-3.1-8b-instruct',
            'messages' => [['role' => 'user', 'content' => $prompt]]
        ]);

        // 4. Ambil jawaban
        $answer = $response->json()['choices'][0]['message']['content'] ?? 'Maaf, saya sedang tidak bisa menjawab.';

        // 5. Kembalikan ke frontend (JSON response)
        return response()->json(['reply' => $answer]);
    }
}
