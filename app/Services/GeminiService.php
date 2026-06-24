<?php



namespace App\Services;



use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Cache;



class GeminiService

{

public function analyzeShipment(array $data)
{
    $response = \Illuminate\Support\Facades\Http::withHeaders([
        'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
        'Content-Type' => 'application/json',
    ])->post('https://openrouter.ai/api/v1/chat/completions', [
        'model' => 'meta-llama/llama-3.1-8b-instruct',
        // 🔥 Tambahkan ini agar konsisten
        'temperature' => 0.0, 
        'messages' => [
            [
                'role' => 'user',
                'content' => "
You are a logistics sustainability expert.
Do NOT automatically classify fruits, vegetables,
or fresh products as highly perishable.

Use the shipment data provided to determine the
actual spoilage risk during transportation.

Analyze the shipment and provide:

Commodity Perishability: High / Medium / Low

Recommendations:
- point 1
- point 2
- point 3

Explanation:
short explanation (1-2 sentences)

Shipment Data:
Commodity: {$data['commodity']}
Origin: {$data['origin']}
Destination: {$data['destination']}
Status: {$data['status']}
Remaining Days: {$data['remaining_days']}
Distance: {$data['distance']} km
Duration: {$data['duration']} hours
Carbon Emission: {$data['carbon_emission']} kg CO2
Route Score: {$data['route_score']}/100

Return ONLY in this format:

Commodity Perishability:
Choose ONLY one:
High
Medium
Low

Recommendations:
- point 1
- point 2
- point 3

Explanation:
text

STRICT: Output the format above exactly. No conversational text.
"
            ]
        ]
    ]);

    $json = json_decode($response->body(), true);
    return $json;
}

public function getCachedInsight(array $data)

    {

        // Kunci cache 'dashboard_insight' bisa lu ganti kalau mau lebih spesifik

        return Cache::remember('dashboard_insight', 3600, function () use ($data) {

            return $this->generateDashboardInsight($data);

        });

    }

public function generateDashboardInsight(array $data)

{

$prompt = "

You are an AI logistics analyst.



Return ONLY clean JSON format like this:



{

  \"insight\": \"short 1-2 sentence system insight\",

  \"recommendation\": \"short actionable recommendation\"

}



Data:

Total Shipments: {$data['totalShipments']}

Delivered: {$data['delivered']}

High Risk: {$data['highRisk']}

Average Score: {$data['avgScore']}

";



    $response = Http::withHeaders([

        'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),

        'Content-Type' => 'application/json',

    ])->post('https://openrouter.ai/api/v1/chat/completions', [

        'model' => 'meta-llama/llama-3.1-8b-instruct',

        'messages' => [

            [

                'role' => 'user',

                'content' => $prompt

            ]

        ]

    ]);



    return $response->json()['choices'][0]['message']['content'] ?? 'No insight';

}

}