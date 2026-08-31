<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeminiService
{
    /**
     * LLM explanation layer only.
     * Numerical risk/priority/readiness values MUST come from DecisionEngine.
     */
    public function generateShipmentExplanation(array $data): array
    {
        $recommendedAction =
            $data['recommended_action']
            ?? 'Monitor shipment';

        $fallbackReason =
            $data['recommendation_reason']
            ?? 'The recommendation is based on the current operational risk and shipment condition.';

        $operationalReadiness =
            $data['operational_readiness_score']
            ?? $data['sustainability_score']
            ?? 0;

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
Operational Readiness Index: {$operationalReadiness}/100
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
- Do not invent storage conditions.
- Only mention commodity-specific storage guidance when it is explicitly supplied by the deterministic engine or commodity profile data.
- Keep the language practical and suitable for a logistics operator.
- No markdown and no text outside JSON.
PROMPT;

        try {
            $response = Http::timeout(15)
                ->retry(1, 300)
                ->withHeaders([
                    'Authorization' =>
                        'Bearer ' . env('OPENROUTER_API_KEY'),

                    'Content-Type' =>
                        'application/json',
                ])
                ->post(
                    'https://openrouter.ai/api/v1/chat/completions',
                    [
                        'model' =>
                            'meta-llama/llama-3.1-8b-instruct',

                        'temperature' =>
                            0,

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

            if (!$response->successful()) {
                return $this->explanationFallback(
                    $recommendedAction,
                    $fallbackReason
                );
            }

            $content = $response->json(
                'choices.0.message.content'
            );

            if (!$content) {
                return $this->explanationFallback(
                    $recommendedAction,
                    $fallbackReason
                );
            }

            $content = trim(
                str_replace(
                    ['```json', '```'],
                    '',
                    $content
                )
            );

            $result = json_decode(
                $content,
                true
            );

            if (!is_array($result)) {
                return $this->explanationFallback(
                    $recommendedAction,
                    $fallbackReason
                );
            }

            return [
                'recommendation' =>
                    $result['recommendation']
                    ?? $recommendedAction,

                'decision_reason' =>
                    $result['decision_reason']
                    ?? $fallbackReason,

                'conclusion' =>
                    $result['conclusion']
                    ?? $fallbackReason,
            ];
        } catch (\Throwable $e) {
            report($e);

            return $this->explanationFallback(
                $recommendedAction,
                $fallbackReason
            );
        }
    }

    /**
     * Kept temporarily for backwards compatibility.
     * Do not use this method to calculate prediction scores.
     */
    public function analyzeShipment(array $data): array
    {
        return $this->generateShipmentExplanation([
            'commodity' =>
                $data['commodity']
                ?? 'Unknown',

            'origin' =>
                $data['origin']
                ?? '-',

            'destination' =>
                $data['destination']
                ?? '-',

            'status' =>
                $data['status']
                ?? '-',

            'remaining_days' =>
                $data['remaining_days']
                ?? 0,

            'distance' =>
                $data['distance']
                ?? 0,

            'risk_score' =>
                $data['risk_score']
                ?? 0,

            'priority_score' =>
                $data['priority_score']
                ?? 0,

            'operational_readiness_score' =>
                $data['operational_readiness_score']
                ?? $data['sustainability_score']
                ?? 0,

            'recommended_action' =>
                $data['recommended_action']
                ?? 'Monitor shipment',

            'recommendation_reason' =>
                $data['recommendation_reason']
                ?? 'No additional engine context was supplied.',
        ]);
    }

    public function getCachedInsight(array $data): array
    {
        return Cache::remember(
            'dashboard_insight',
            3600,
            function () use ($data) {
                return $this->generateDashboardInsight(
                    $data
                );
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
You are the explanation layer for AgriFlow's dashboard.

The values below are recorded or deterministic system outputs.

Do not recalculate them, invent new metrics, or imply statistical prediction.

Return ONLY valid JSON:
{
  "insight": "1-2 concise sentences describing the current operational state",
  "recommendation": "one concise operational recommendation"
}

Current system data:
Total Shipments: {$data['totalShipments']}
Delivered Shipments: {$data['delivered']}
High-Risk Analyses: {$data['highRisk']}
Average Operational Readiness: {$averageOperationalReadiness}/100

Rules:
- Do not claim food-waste reduction, carbon savings, or efficiency improvement unless explicitly provided.
- Do not call Operational Readiness an environmental, ESG, or lifecycle-impact metric.
- Do not output confidence or probability.
- Do not claim machine-learning accuracy.
- Do not claim real-time sensor or GPS data.
- Recommendations must remain operational and conservative.
- No markdown and no text outside JSON.
PROMPT;

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Authorization' =>
                        'Bearer ' . env('OPENROUTER_API_KEY'),

                    'Content-Type' =>
                        'application/json',
                ])
                ->post(
                    'https://openrouter.ai/api/v1/chat/completions',
                    [
                        'model' =>
                            'meta-llama/llama-3.1-8b-instruct',

                        'temperature' =>
                            0,

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

            if (!$response->successful()) {
                return $this->dashboardInsightFallback();
            }

            $content = $response->json(
                'choices.0.message.content'
            );

            if (!$content) {
                return $this->dashboardInsightFallback();
            }

            $content = trim(
                str_replace(
                    ['```json', '```'],
                    '',
                    $content
                )
            );

            $result = json_decode(
                $content,
                true
            );

            if (!is_array($result)) {
                return $this->dashboardInsightFallback();
            }

            return [
                'insight' =>
                    $result['insight']
                    ?? 'Current operational metrics are available for review.',

                'recommendation' =>
                    $result['recommendation']
                    ?? 'Review the current deterministic shipment metrics.',
            ];
        } catch (\Throwable $e) {
            report($e);

            return $this->dashboardInsightFallback();
        }
    }

    private function explanationFallback(
        string $action,
        string $reason
    ): array {
        return [
            'recommendation' =>
                $action,

            'decision_reason' =>
                $reason,

            'conclusion' =>
                $reason,
        ];
    }

    private function dashboardInsightFallback(): array
    {
        return [
            'insight' =>
                'Dashboard insight is temporarily unavailable.',

            'recommendation' =>
                'Review the current deterministic shipment metrics.',
        ];
    }
}