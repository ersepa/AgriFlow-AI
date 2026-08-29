<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeminiService
{
    /**
     * LLM explanation layer only.
     * Numerical risk/priority/sustainability values MUST come from DecisionEngine.
     */
    public function generateShipmentExplanation(array $data): array
    {
        $recommendedAction = $data['recommended_action'] ?? 'Monitor shipment';
        $fallbackReason = $data['recommendation_reason']
            ?? 'The recommendation is based on the current operational risk and shipment condition.';

        $prompt = <<<PROMPT
You are the explanation layer for AgriFlow, an agricultural logistics decision-support system.

IMPORTANT:
- The numerical scores below were already calculated by AgriFlow's Decision Engine.
- DO NOT recalculate, change, invent, or contradict the scores.
- Your job is only to explain the decision in clear operational language.

Shipment:
Commodity: {$data['commodity']}
Origin: {$data['origin']}
Destination: {$data['destination']}
Status: {$data['status']}
Remaining recorded shelf life: {$data['remaining_days']} days
Distance: {$data['distance']} km
Risk Index: {$data['risk_score']}/100
Priority Score: {$data['priority_score']}/100
Sustainability Score: {$data['sustainability_score']}/100
Engine Recommendation: {$recommendedAction}
Engine Reason: {$fallbackReason}

Return ONLY valid JSON in this exact structure:
{
  "recommendation": "short action",
  "decision_reason": "1-2 concise sentences explaining the existing engine decision",
  "conclusion": "one concise sentence"
}

Rules:
- Do not output a confidence percentage.
- Do not call Risk Index a probability.
- Do not claim a neural network, Monte Carlo model, or real-time sensor data.
- Do not prescribe an exact storage temperature because commodity-specific profiles are not active yet.
- Keep the language practical and suitable for a logistics operator.
- No markdown and no text outside JSON.
PROMPT;

        try {
            $response = Http::timeout(15)
                ->retry(1, 300)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                    'Content-Type' => 'application/json',
                ])
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => 'meta-llama/llama-3.1-8b-instruct',
                    'temperature' => 0,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if (!$response->successful()) {
                return $this->explanationFallback($recommendedAction, $fallbackReason);
            }

            $content = $response->json('choices.0.message.content');

            if (!$content) {
                return $this->explanationFallback($recommendedAction, $fallbackReason);
            }

            $content = trim(str_replace(['```json', '```'], '', $content));
            $result = json_decode($content, true);

            if (!is_array($result)) {
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
            'commodity' => $data['commodity'] ?? 'Unknown',
            'origin' => $data['origin'] ?? '-',
            'destination' => $data['destination'] ?? '-',
            'status' => $data['status'] ?? '-',
            'remaining_days' => $data['remaining_days'] ?? 0,
            'distance' => $data['distance'] ?? 0,
            'risk_score' => $data['risk_score'] ?? 0,
            'priority_score' => $data['priority_score'] ?? 0,
            'sustainability_score' => $data['sustainability_score'] ?? 0,
            'recommended_action' => $data['recommended_action'] ?? 'Monitor shipment',
            'recommendation_reason' => $data['recommendation_reason']
                ?? 'No additional engine context was supplied.',
        ]);
    }

    public function getCachedInsight(array $data)
    {
        return Cache::remember('dashboard_insight', 3600, function () use ($data) {
            return $this->generateDashboardInsight($data);
        });
    }

    public function generateDashboardInsight(array $data)
    {
        $prompt = <<<PROMPT
You are an agricultural logistics operations analyst.
Return ONLY JSON:
{
  "insight": "short 1-2 sentence system insight",
  "recommendation": "short actionable recommendation"
}

Data:
Total Shipments: {$data['totalShipments']}
Delivered: {$data['delivered']}
High Risk: {$data['highRisk']}
Average Score: {$data['avgScore']}

Do not invent additional metrics or claim model accuracy.
PROMPT;

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                    'Content-Type' => 'application/json',
                ])
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => 'meta-llama/llama-3.1-8b-instruct',
                    'temperature' => 0,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            return $response->json('choices.0.message.content') ?? 'No insight';
        } catch (\Throwable $e) {
            report($e);
            return 'No insight';
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
}
