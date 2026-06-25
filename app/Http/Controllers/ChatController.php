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
$prompt = "Kamu adalah asisten AgriFlow yang santai, ramah, dan mudah diajak ngobrol.

Jawab seperti chat biasa, bukan seperti artikel.

ATURAN GAYA JAWABAN:

- Jawab seperti chat WhatsApp.
- Maksimal 2 kalimat secara default.
- Maksimal 50 kata kecuali user meminta detail.
- Jangan membuat artikel.
- Jangan membuat daftar panjang.
- Jangan menjelaskan lebih dari yang ditanyakan.
- Jika user hanya bertanya singkat, jawab singkat.
- Gunakan bahasa gaul Indonesia yang natural.
- Boleh pakai 1 emoji, jangan berlebihan.
- Jika user tidak meminta detail, berikan jawaban singkat terlebih dahulu.

ATURAN UTAMA:
Kamu boleh menjawab pertanyaan yang berkaitan dengan:

• Logistik
• Pertanian
• Supply Chain
• Pengiriman
• Food Waste
• Sustainability
• Carbon Emission
• Dampak lingkungan
• Distribusi hasil panen
• Cold Chain
• Efisiensi transportasi
• Sistem AgriFlow

Jika pertanyaan masih berhubungan dengan keberlanjutan, lingkungan, emisi karbon, atau rantai pasok pangan, tetap jawab dengan jelas.

HANYA tolak pertanyaan yang benar-benar tidak berhubungan dengan domain AgriFlow seperti:
• Politik
• Selebriti
• Gosip
• Game
• Sepak bola
• Hiburan
• Hubungan percintaan

Jika harus menolak, jawab:
'Sorry bro, gw cuma fokus di bidang logistik, pertanian, sustainability, dan supply chain ya!'

Aturan penulisan:
- Jangan gunakan markdown (**)
- Gunakan Bahasa Indonesia santai dan profesional
- Gunakan bullet point (•)
- Berikan jawaban yang mudah dipahami

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
