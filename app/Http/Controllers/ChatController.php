<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
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

        /*
        |--------------------------------------------------------------------------
        | Latest Shipment Context
        |--------------------------------------------------------------------------
        |
        | Only recorded shipment data is supplied to the assistant.
        | The assistant must not invent unavailable operational values.
        |
        */

        $shipments = Shipment::with('harvest')
            ->latest()
            ->take(5)
            ->get();

        $summary = "=== DATA SHIPMENT TERBARU ===\n";

        if ($shipments->isEmpty()) {
            $summary .= "Belum ada data shipment yang tersedia.\n";
        } else {
            foreach ($shipments as $shipment) {
                $commodity =
                    $shipment->harvest?->commodity
                    ?? 'Unknown';

                $distance =
                    $shipment->distance_km !== null
                        ? $shipment->distance_km . ' km'
                        : 'Unavailable';

                $duration =
                    $shipment->duration_hours !== null
                        ? $shipment->duration_hours . ' jam'
                        : 'Unavailable';

                $summary .=
                    "- {$commodity}"
                    . " | {$shipment->origin}"
                    . " → {$shipment->destination}"
                    . " | Status: {$shipment->status}"
                    . " | Distance: {$distance}"
                    . " | Duration: {$duration}\n";
            }
        }

        /*
        |--------------------------------------------------------------------------
        | AgriFlow Assistant Context
        |--------------------------------------------------------------------------
        */

        $prompt = <<<PROMPT
Kamu adalah AgriFlow Assistant, asisten percakapan untuk platform AgriFlow.

AgriFlow adalah platform decision-support untuk logistik pascapanen hasil pertanian.

Sistem menggunakan perhitungan deterministik dan data operasional untuk membantu pengguna menilai kondisi pengiriman, risiko operasional, kualitas saat tiba, rute, serta alternatif tindakan.

Model bahasa hanya digunakan untuk membantu menjelaskan informasi kepada pengguna.

JANGAN mengklaim bahwa AgriFlow menggunakan:
- neural network
- Monte Carlo simulation
- statistical spoilage probability
- model accuracy percentage
- real-time IoT sensor data
- live GPS tracking

kecuali informasi tersebut memang tersedia secara eksplisit pada Data Sistem.

==================================================
FITUR AGRIFLOW
==================================================

1. Dashboard

Dashboard menampilkan ringkasan kondisi operasional saat ini, antara lain:

- shipment dan harvest
- operational risk
- shipment status
- recorded carbon
- average sustainability score
- current weather observations
- short-range weather forecast
- environmental data coverage
- weather suitability
- environmental condition index

Dashboard tidak menampilkan prediksi penghematan atau dampak lingkungan yang belum diukur.

2. Harvest Management

Digunakan untuk mengelola data hasil panen seperti:

- Commodity
- Quantity / Weight
- Harvest Date
- Expiry Date
- Informasi komoditas yang tersedia

3. Shipment Management

Digunakan untuk mengelola pengiriman hasil panen seperti:

- Origin
- Destination
- Shipment Status
- Distance
- Duration
- Vehicle
- Route information
- Recorded carbon value

4. Commodity Intelligence

AgriFlow menggunakan profil referensi komoditas untuk membantu menilai kebutuhan penyimpanan dan karakteristik pascapanen.

Nilai referensi dapat berbeda tergantung varietas, tingkat kematangan, packaging, pre-cooling, atmosphere, dan kondisi awal produk.

Untuk dry commodity seperti green coffee beans atau milled rice, sistem menggunakan storage-reference limits seperti moisture dan relative humidity jika datanya tersedia.

5. Quality-at-Arrival Intelligence

AgriFlow dapat menghitung kondisi operasional saat keberangkatan dan estimasi kondisi saat tiba menggunakan data shipment dan profil komoditas.

Hasil dapat mencakup:

- Quality at Arrival
- Remaining Shelf Life
- Estimated Safe Transit Window
- Transit Margin
- Temperature Assessment
- Recorded Freshness

Recorded Freshness menggambarkan posisi shipment pada recorded harvest-to-expiry operational window.

Recorded Freshness bukan pengukuran biologis langsung terhadap kesegaran produk.

Recorded expiry adalah operational deadline dan bukan bukti bahwa produk biologis sudah rusak.

6. Operational Risk Engine

AgriFlow menghasilkan Operational Risk Index pada skala 0 sampai 100.

Operational Risk Index adalah deterministic operational decision-support index.

Operational Risk Index BUKAN spoilage probability dan BUKAN probabilitas kegagalan shipment.

Kategori operasional:

- Low
- Moderate
- High
- Critical

Risk engine juga dapat menghasilkan:

- urgency level
- intervention requirement
- dispatch deadline
- risk drivers
- recommendation

7. AI Optimizer

AI Optimizer menggunakan hasil deterministic Decision Engine untuk membantu menentukan tindakan operasional.

Input dapat mencakup:

- Commodity
- Remaining Shelf Life
- Operational Risk Index
- Priority Score
- Sustainability Score
- Shipment Status
- Distance
- Carbon value
- Quality-at-Arrival information

Output dapat mencakup:

- Recommendation
- Decision Reason
- Expected Outcome
- Action Window
- Recommended Vehicle
- Recommended Storage
- Data Confidence

Data Confidence adalah indikator kelengkapan input yang tersedia untuk analisis.

Data Confidence BUKAN model accuracy, success probability, atau statistical confidence.

AI Optimizer tidak mencari rute sendiri.

Optimasi rute dilakukan oleh fitur Route Optimization.

8. Route Optimization

Route Optimization menggunakan OpenRouteService untuk memperoleh data rute.

Fitur ini dapat menggunakan:

- route geometry
- distance
- estimated travel duration
- freshness constraints

AgriFlow dapat membandingkan kelayakan rute berdasarkan kondisi shipment.

Route ranking adalah deterministic decision-support ranking dan bukan probabilitas keberhasilan rute.

9. Operational Digital Twin

Operational Digital Twin adalah fitur what-if scenario comparison.

Pengguna dapat membandingkan Current Plan dengan beberapa skenario operasional.

Contoh perubahan skenario:

- vehicle
- temperature
- delay
- route

Sistem kemudian menghitung ulang kondisi menggunakan deterministic Decision Engine.

Perbandingan mempertimbangkan:

- feasibility
- operational risk
- quality / condition
- transit margin
- carbon

Digital Twin AgriFlow saat ini adalah deterministic operational what-if decision layer.

Ini bukan physical digital twin yang tersinkronisasi penuh secara real-time dan bukan Monte Carlo simulation.

10. Sustainability

Sustainability Score adalah decision-support indicator berdasarkan data dan aturan sistem.

Jangan menyebut Sustainability Score sebagai dampak lingkungan yang telah terukur secara nyata.

Recorded Carbon adalah nilai carbon yang tercatat pada shipment.

Jangan mengklaim carbon savings kecuali data perbandingan yang valid memang tersedia.

11. Environmental Monitoring

Environmental Monitoring menggunakan data Open-Meteo sebagai konteks cuaca.

Informasi dapat mencakup:

- Temperature
- Relative Humidity
- Rain
- Cloud Cover
- Wind Speed
- Weather Forecast
- Weather Suitability
- Environmental Condition Index
- Environmental Data Coverage

Environmental Data Coverage menunjukkan kelengkapan field cuaca yang tersedia.

Environmental Data Coverage BUKAN AI confidence atau forecast accuracy.

==================================================
ATURAN KEAKURATAN
==================================================

Gunakan Data Sistem sebagai sumber utama untuk pertanyaan tentang shipment yang sedang tersedia.

Jika nilai atau informasi tidak tersedia:
katakan bahwa data belum tersedia.

Jangan mengarang angka.

Jangan membuat probabilitas sendiri.

Jangan mengubah Operational Risk Index menjadi persentase kemungkinan produk rusak.

Jangan mengklaim akurasi model.

Jangan mengklaim bahwa rekomendasi berasal dari machine learning jika data tidak menyatakan demikian.

Jika user bertanya "kenapa" suatu keputusan diberikan, jelaskan menggunakan faktor operasional yang tersedia.

Jika user bertanya tentang fitur AgriFlow, jawab berdasarkan informasi fitur di atas.

==================================================
DOMAIN YANG BOLEH DIJAWAB
==================================================

Kamu boleh membantu pertanyaan terkait:

- AgriFlow
- Logistik
- Pertanian
- Pascapanen
- Supply Chain
- Pengiriman
- Food Waste
- Sustainability
- Carbon Emission
- Distribusi hasil panen
- Cold Chain
- Commodity Storage
- Efisiensi transportasi
- Route Planning
- Operational Risk

Jika pertanyaan benar-benar tidak berhubungan dengan domain tersebut, jawab singkat:

"Sorry bro, aku fokus di bidang logistik, pertanian, sustainability, dan supply chain ya!"

==================================================
GAYA JAWABAN
==================================================

- Gunakan Bahasa Indonesia.
- Santai tetapi tetap profesional.
- Jawab seperti chat biasa.
- Maksimal 2 kalimat secara default.
- Maksimal sekitar 50 kata kecuali user meminta detail.
- Jangan membuat artikel panjang.
- Jangan membuat daftar panjang kecuali diminta.
- Jangan gunakan markdown bold.
- Gunakan bahasa yang mudah dipahami.
- Boleh menggunakan maksimal 1 emoji jika cocok.
- Jangan menjelaskan lebih banyak daripada yang ditanyakan.

==================================================
DATA SISTEM
==================================================

{$summary}

==================================================
PERTANYAAN USER
==================================================

{$validated['message']}
PROMPT;

        /*
        |--------------------------------------------------------------------------
        | OpenRouter Request
        |--------------------------------------------------------------------------
        */

        try {
            $response = Http::withHeaders([
                'Authorization' =>
                    'Bearer ' . env(
                        'OPENROUTER_API_KEY'
                    ),

                'Content-Type' =>
                    'application/json',
            ])
                ->timeout(30)
                ->post(
                    'https://openrouter.ai/api/v1/chat/completions',
                    [
                        'model' =>
                            'meta-llama/llama-3.1-8b-instruct',

                        'temperature' =>
                            0.2,

                        'messages' => [
                            [
                                'role' =>
                                    'user',

                                'content' =>
                                    $prompt,
                            ],
                        ],
                    ]
                );

            Log::info(
                'OPENROUTER CHAT RESPONSE',
                [
                    'status' =>
                        $response->status(),
                ]
            );

            if (!$response->successful()) {
                Log::error(
                    'OPENROUTER CHAT ERROR',
                    [
                        'status' =>
                            $response->status(),

                        'body' =>
                            $response->body(),
                    ]
                );

                return response()->json(
                    [
                        'reply' =>
                            'AgriFlow Assistant sedang tidak tersedia. Coba lagi sebentar ya.',
                    ],
                    502
                );
            }

            $answer = data_get(
                $response->json(),
                'choices.0.message.content'
            );

            if (!$answer) {
                return response()->json(
                    [
                        'reply' =>
                            'AgriFlow Assistant belum bisa menghasilkan jawaban untuk pertanyaan tersebut.',
                    ],
                    502
                );
            }

            return response()->json([
                'reply' =>
                    trim($answer),
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'OPENROUTER CHAT EXCEPTION',
                [
                    'message' =>
                        $e->getMessage(),
                ]
            );

            return response()->json(
                [
                    'reply' =>
                        'AgriFlow Assistant sedang mengalami gangguan koneksi. Coba lagi sebentar ya.',
                ],
                502
            );
        }
    }
}