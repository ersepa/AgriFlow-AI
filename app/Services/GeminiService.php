<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeminiService
{
    private function callGeminiJson(string $prompt): ?array
    {
        $apiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        $model = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-2.5-flash');

        if (empty($apiKey)) {
            return null;
        }

        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $apiKey,
            ])
            ->withOptions([
                'force_ip_resolve' => 'v4',
            ])
            ->connectTimeout(15)
            ->timeout(45)
            ->post($endpoint, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [['text' => $prompt]],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if (!$response->successful()) {
            return null;
        }

        $content = data_get($response->json(), 'candidates.0.content.parts.0.text');
        if (!$content) {
            return null;
        }

        $content = trim(str_replace(['```json', '```'], '', $content));
        $result = json_decode($content, true);

        return is_array($result) ? $result : null;
    }

    /**
     * LLM explanation layer only.
     * Numerical risk/priority/readiness values MUST come from DecisionEngine.
     */
    public function generateShipmentExplanation(array $data): array
    {
        $recommendedAction =
            $data['recommended_action']
            ?? 'Pantau kondisi pengiriman';

        $fallbackReason =
            $data['recommendation_reason']
            ?? 'Rekomendasi disusun berdasarkan tingkat risiko operasional dan kondisi pengiriman saat ini.';

        $operationalReadiness =
            $data['operational_readiness_score']
            ?? $data['sustainability_score']
            ?? 0;

        $prompt = <<<PROMPT
Kamu adalah lapisan penjelasan untuk AgriFlow, sistem pendukung keputusan logistik pertanian.

PENTING:
- Skor numerik di bawah ini sudah dihitung oleh Decision Engine AgriFlow.
- JANGAN menghitung ulang, mengubah, mengarang, atau membantah skor tersebut.
- Tugasmu hanya menjelaskan keputusan dalam Bahasa Indonesia yang jelas dan praktis.

Data Pengiriman:
Komoditas: {$data['commodity']}
Asal: {$data['origin']}
Tujuan: {$data['destination']}
Status: {$data['status']}
Sisa masa simpan tercatat: {$data['remaining_days']} hari
Jarak: {$data['distance']} km
Indeks Risiko: {$data['risk_score']}/100
Skor Prioritas: {$data['priority_score']}/100
Indeks Kesiapan Operasional: {$operationalReadiness}/100
Rekomendasi Mesin: {$recommendedAction}
Alasan Mesin: {$fallbackReason}

Kembalikan HANYA JSON valid dengan struktur persis berikut:
{
  "recommendation": "tindakan singkat dalam Bahasa Indonesia",
  "decision_reason": "1-2 kalimat ringkas menjelaskan keputusan mesin dalam Bahasa Indonesia",
  "conclusion": "satu kalimat kesimpulan singkat dalam Bahasa Indonesia"
}

Aturan:
- Gunakan 100% Bahasa Indonesia yang praktis untuk operator logistik.
- Jangan tampilkan persentase keyakinan atau menyebut Indeks Risiko sebagai probabilitas.
- Jangan mengklaim neural network, model Monte Carlo, atau data sensor real-time.
- Jangan mengarang kondisi penyimpanan.
- Tanpa markdown dan tanpa teks di luar JSON.
PROMPT;

        try {
            $result = $this->callGeminiJson($prompt);

            if (!$result) {
                return $this->explanationFallback($recommendedAction, $fallbackReason);
            }

            return [
                'recommendation' => $result['recommendation'] ?? $recommendedAction,
                'decision_reason' => $result['decision_reason'] ?? $fallbackReason,
                'conclusion' => $result['conclusion'] ?? $fallbackReason,
            ];
        } catch (\Throwable $e) {
            report($e);

            return $this->explanationFallback($recommendedAction, $fallbackReason);
        }
    }

    /**
     * Kept temporarily for backwards compatibility.
     * Do not use this method to calculate prediction scores.
     */
    public function analyzeShipment(array $data): array
    {
        return $this->generateShipmentExplanation([
            'commodity' => $data['commodity'] ?? 'Tidak diketahui',
            'origin' => $data['origin'] ?? '-',
            'destination' => $data['destination'] ?? '-',
            'status' => $data['status'] ?? '-',
            'remaining_days' => $data['remaining_days'] ?? 0,
            'distance' => $data['distance'] ?? 0,
            'risk_score' => $data['risk_score'] ?? 0,
            'priority_score' => $data['priority_score'] ?? 0,
            'operational_readiness_score' => $data['operational_readiness_score'] ?? $data['sustainability_score'] ?? 0,
            'recommended_action' => $data['recommended_action'] ?? 'Pantau kondisi pengiriman',
            'recommendation_reason' => $data['recommendation_reason'] ?? 'Tidak ada konteks tambahan dari mesin.',
        ]);
    }

    public function getCachedInsight(array $data): array
    {
        return Cache::remember(
            'dashboard_insight',
            3600,
            function () use ($data) {
                return $this->generateDashboardInsight($data);
            }
        );
    }

    public function generateDashboardInsight(array $data): array
    {
        $averageOperationalReadiness =
            $data['avgOperationalReadiness']
            ?? $data['avgScore']
            ?? 0;

        $prompt = <<<PROMPT
Kamu adalah lapisan penjelasan untuk dashboard AgriFlow.
Nilai di bawah ini adalah output deterministik sistem. Jangan menghitung ulang atau mengarang metrik baru.

Kembalikan HANYA JSON valid dalam Bahasa Indonesia:
{
  "insight": "1-2 kalimat ringkas tentang kondisi operasional saat ini",
  "recommendation": "satu rekomendasi operasional singkat"
}

Data sistem saat ini:
Total Pengiriman Tercatat: {$data['totalShipments']}
Pengiriman Aktif: {$data['activeShipments']}
Pengiriman Selesai: {$data['delivered']}
Pengiriman Aktif Risiko Tinggi: {$data['activeHighRisk']}
Pengiriman Aktif Kritis: {$data['activeCritical']}
Rata-rata Kesiapan Operasional Aktif: {$averageOperationalReadiness}/100

Aturan:
- Gunakan 100% Bahasa Indonesia.
- Jangan mengklaim persentase akurasi machine learning, sensor real-time, atau GPS langsung.
- Tanpa markdown dan tanpa teks di luar JSON.
PROMPT;

        try {
            $result = $this->callGeminiJson($prompt);

            if (!$result) {
                return $this->dashboardInsightFallback();
            }

            return [
                'insight' => $result['insight'] ?? 'Metrik operasional saat ini tersedia untuk ditinjau.',
                'recommendation' => $result['recommendation'] ?? 'Tinjau metrik pengiriman aktif saat ini.',
            ];
        } catch (\Throwable $e) {
            report($e);

            return $this->dashboardInsightFallback();
        }
    }

    private function explanationFallback(string $action, string $reason): array
    {
        return [
            'recommendation' => $action,
            'decision_reason' => $reason,
            'conclusion' => $reason,
        ];
    }

    private function dashboardInsightFallback(): array
    {
        return [
            'insight' => 'Ringkasan dashboard sementara belum tersedia.',
            'recommendation' => 'Tinjau metrik pengiriman aktif saat ini.',
        ];
    }
}