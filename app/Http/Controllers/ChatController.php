<?php

namespace App\Http\Controllers;

use App\Models\Harvest;
use App\Models\Shipment;
use App\Services\AI\DecisionEngine;
use App\Services\EnvironmentalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        $userId = Auth::id();
        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        $model = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-2.5-flash');

        if (empty($apiKey)) {
            Log::error('GEMINI CHAT CONFIG ERROR: GEMINI_API_KEY kosong.');

            return response()->json([
                'reply' => 'Maaf, layanan asisten belum terkonfigurasi dengan benar. Pastikan GEMINI_API_KEY sudah diisi di .env.',
            ], 503);
        }

        // 1. Kumpulkan Konteks Data AgriFlow Khusus Milik Pengguna (Auth::id())
        $contextSummary = $this->buildUserAgriFlowContext($userId);

        // 2. System Instruction 100% Bahasa Indonesia
        $systemPrompt = <<<PROMPT
Kamu adalah Asisten AgriFlow, asisten percakapan khusus untuk platform AgriFlow AI.

AgriFlow AI adalah platform pendukung keputusan untuk pengelolaan hasil panen dan logistik pascapanen pertanian.

==================================================
ATURAN KEAKURATAN & DATA
==================================================
- Gunakan DATA SISTEM PENGGUNA di bawah ini sebagai sumber utama untuk menjawab pertanyaan tentang hasil panen, pengiriman, risiko, kualitas produk, rekomendasi, dan cuaca milik pengguna.
- JANGAN mengarang angka, suhu, risiko, atau data pengiriman yang tidak ada di DATA SISTEM PENGGUNA.
- Jika data yang ditanyakan pengguna belum tersedia, sampaikan dengan jujur bahwa data tersebut belum tersedia.
- Untuk pertanyaan umum seputar pertanian, pascapanen, penyimpanan, atau logistik, kamu boleh menjawab menggunakan pengetahuan umum yang relevan, tanpa mencampurkannya dengan data palsu.
- Jangan mengklaim AgriFlow menggunakan neural network, simulasi Monte Carlo, persentase akurasi model, sensor IoT real-time, atau GPS langsung.
- Jangan mengulang sapaan "Halo!" di setiap balasan jika percakapan sudah berjalan.

==================================================
DOMAIN YANG BOLEH DIJAWAB
==================================================
Fokus pada AgriFlow, hasil panen, logistik, pertanian, pascapanen, rantai pasok, pengiriman, kualitas & kesegaran produk, masa simpan, pengurangan food waste, emisi karbon, rute pengiriman, cuaca operasional, dan risiko operasional.
Jika pertanyaan benar-benar di luar topik tersebut, jawab singkat:
"Maaf ya, saya hanya fokus membantu seputar hasil panen, pengiriman, analisis risiko, dan logistik pertanian di AgriFlow."

==================================================
GAYA JAWABAN
==================================================
- WAJIB 100% Bahasa Indonesia yang ramah, natural, sederhana, dan mudah dipahami pengguna awam.
- Jangan mencampurkan Bahasa Inggris dan Bahasa Indonesia.
- Singkat, jelas, dan langsung ke inti (maksimal 3-4 kalimat, kecuali pengguna meminta penjelasan lebih rinci).
- Hindari jargon teknis yang rumit dan hindari format markdown berlebihan.

==================================================
DATA SISTEM PENGGUNA
==================================================
{$contextSummary}
PROMPT;

        // 3. Riwayat Percakapan Per-Pengguna di Session (Format Gemini: user & model)
        $sessionKey = 'agriflow_chat_history_' . $userId;
        $history = session($sessionKey, []);
        $recentHistory = array_slice($history, -8);

        $contents = [];
        foreach ($recentHistory as $item) {
            if (!empty($item['role']) && !empty($item['content'])) {
                $geminiRole = $item['role'] === 'assistant' ? 'model' : 'user';
                $contents[] = [
                    'role' => $geminiRole,
                    'parts' => [['text' => $item['content']]],
                ];
            }
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $validated['message']]],
        ];

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $apiKey,
            ])
                ->withOptions([
                    'force_ip_resolve' => 'v4',
                ])
                ->connectTimeout(15)
                ->timeout(60)
                ->post($endpoint, [
                    'system_instruction' => [
                        'parts' => [['text' => $systemPrompt]],
                    ],
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.2,
                    ],
                ]);

            Log::info('GEMINI CHAT RESPONSE', [
                'status' => $response->status(),
                'user_id' => $userId,
            ]);

            if (!$response->successful()) {
                Log::error('GEMINI CHAT ERROR', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                $errorMessage = match ($response->status()) {
                    400, 401, 403 => 'Maaf, API key Gemini tidak valid atau belum memiliki akses. Silakan periksa konfigurasi.',
                    429, 503 => 'Maaf, server AI sedang ramai saat ini. Coba kirim ulang beberapa detik lagi ya.',
                    default => 'Maaf, layanan AI sedang mengalami gangguan. Silakan coba kembali beberapa saat lagi.',
                };

                return response()->json([
                    'reply' => $errorMessage,
                ], 502);
            }

            $answer = data_get($response->json(), 'candidates.0.content.parts.0.text');

            if (!$answer) {
                return response()->json([
                    'reply' => 'Maaf, asisten belum dapat menyiapkan jawaban untuk pertanyaan tersebut. Coba tanyakan dengan kalimat lain ya.',
                ], 502);
            }

            $cleanAnswer = trim($answer);

            // Simpan riwayat percakapan terbaru ke session pengguna
            $recentHistory[] = ['role' => 'user', 'content' => $validated['message']];
            $recentHistory[] = ['role' => 'assistant', 'content' => $cleanAnswer];
            session([$sessionKey => array_slice($recentHistory, -8)]);

            return response()->json([
                'reply' => $cleanAnswer,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('GEMINI CHAT CONNECTION EXCEPTION', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'reply' => 'Maaf, koneksi ke layanan asisten sedang terganggu. Periksa jaringan dan coba lagi sebentar ya.',
            ], 504);
        } catch (\Throwable $e) {
            Log::error('GEMINI CHAT EXCEPTION', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'reply' => 'Maaf, asisten sedang mengalami kendala. Silakan coba kembali beberapa saat lagi.',
            ], 502);
        }
    }

    private function buildUserAgriFlowContext(int $userId): string
    {
        $lines = [];

        // A. Data Hasil Panen Terbaru
        $harvests = Harvest::query()
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        $lines[] = '=== DATA HASIL PANEN ===';
        if ($harvests->isEmpty()) {
            $lines[] = 'Belum ada data hasil panen yang tercatat.';
        } else {
            foreach ($harvests as $h) {
                $harvestDate = $h->harvest_date ? \Carbon\Carbon::parse($h->harvest_date)->format('d-m-Y') : 'Belum diatur';
                $expiryDate = $h->expiry_date ? \Carbon\Carbon::parse($h->expiry_date)->format('d-m-Y') : 'Belum diatur';
                $lines[] = "- {$h->commodity} | Berat: {$h->weight} kg | Lokasi: {$h->location} | Panen: {$harvestDate} | Kedaluwarsa: {$expiryDate}";
            }
        }

        // B. Data Pengiriman & Analisis Risiko (DecisionEngine)
        $shipments = Shipment::query()
            ->with('harvest')
            ->whereHas('harvest', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->latest()
            ->take(5)
            ->get();

        $lines[] = '';
        $lines[] = '=== DATA PENGIRIMAN & ANALISIS RISIKO ===';

        if ($shipments->isEmpty()) {
            $lines[] = 'Belum ada data pengiriman yang tercatat.';
        } else {
            $engine = app(DecisionEngine::class);

            foreach ($shipments as $shipment) {
                $commodity = $shipment->harvest?->commodity ?? 'Tidak diketahui';
                $statusIndo = match ($shipment->status) {
                    'Harvested' => 'Baru Dipanen',
                    'Packed' => 'Siap Kirim (Dikemas)',
                    'In Transit' => 'Dalam Perjalanan',
                    'Delivered' => 'Telah Tiba',
                    default => $shipment->status,
                };
                $distance = $shipment->distance_km !== null ? round($shipment->distance_km, 1) . ' km' : 'Belum tersedia';
                $duration = $shipment->duration_hours !== null ? round($shipment->duration_hours, 1) . ' jam' : 'Belum tersedia';
                $temp = $shipment->recorded_temperature_c !== null ? $shipment->recorded_temperature_c . '°C' : 'Belum dicatat';
                $rh = $shipment->recorded_relative_humidity_percent !== null ? $shipment->recorded_relative_humidity_percent . '%' : 'Belum dicatat';

                $analysisText = '';
                try {
                    $analysis = $engine->analyze($shipment);
                    $riskScore = $analysis['risk_score'] ?? null;
                    $riskSeverity = match ($analysis['risk_severity'] ?? '') {
                        'High' => 'Tinggi',
                        'Moderate', 'Medium' => 'Sedang',
                        'Low' => 'Rendah',
                        default => 'Belum tersedia',
                    };
                    $quality = $analysis['quality_at_arrival'] ?? null;
                    $shelfLife = $analysis['predicted_remaining_shelf_life_days'] ?? null;
                    $recAction = $analysis['recommended_action'] ?? 'Belum tersedia';

                    $analysisText = sprintf(
                        ' | Risiko: %s (%s/100) | Kualitas Saat Tiba: %s | Sisa Masa Simpan: %s | Rekomendasi: %s',
                        $riskSeverity,
                        $riskScore ?? '-',
                        $quality !== null ? $quality . '/100' : 'Belum tersedia',
                        $shelfLife !== null ? round((float) $shelfLife, 1) . ' hari' : 'Belum tersedia',
                        $recAction
                    );
                } catch (\Throwable $e) {
                    Log::warning('CHAT CONTEXT DECISION ENGINE WARNING', ['shipment_id' => $shipment->id, 'message' => $e->getMessage()]);
                }

                $lines[] = "- #{$shipment->id} {$commodity} | {$shipment->origin} → {$shipment->destination} | Status: {$statusIndo} | Jarak: {$distance} | Estimasi: {$duration} | Suhu: {$temp} | Kelembapan: {$rh}{$analysisText}";
            }
        }

        // C. Data Cuaca Operasional (jika tersedia)
        $lines[] = '';
        $lines[] = '=== DATA CUACA TERKINI ===';
        try {
            $envService = new EnvironmentalService();
            $env = $envService->getEnvironment(null);
            if (!empty($env['weather'])) {
                $w = $env['weather'];
                $loc = $env['location'] ?? 'Area pantauan';
                $lines[] = "- Wilayah: {$loc} | Suhu lingkungan: " . round($w['temperature_2m'] ?? 0) . "°C | Kelembapan: " . ($w['relative_humidity_2m'] ?? '-') . "% | Curah hujan: " . ($w['rain'] ?? 0) . " mm | Angin: " . round($w['wind_speed_10m'] ?? 0) . " km/jam";
            } else {
                $lines[] = 'Data cuaca belum tersedia saat ini.';
            }
        } catch (\Throwable $e) {
            $lines[] = 'Data cuaca belum tersedia saat ini.';
        }

        return implode("\n", $lines);
    }
}