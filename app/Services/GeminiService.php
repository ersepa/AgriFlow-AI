<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Cache;
class GeminiService
{
public function generateShipmentExplanation(array $data)
{

$prompt = <<<PROMPT
You are an AI logistics analyst.

Analyze this shipment:

Commodity: {$data['commodity']}
Origin: {$data['origin']}
Destination: {$data['destination']}
Status: {$data['status']}
Remaining Days: {$data['remaining_days']}
Distance: {$data['distance']} km

Risk Score: {$data['risk_score']}
Priority Score: {$data['priority_score']}
Sustainability Score: {$data['sustainability_score']}

Generate operational logistics advice based on shipment conditions.

Generate a UNIQUE recommendation for this shipment.

Base your recommendation on the COMBINATION of:
- Remaining shelf life
- Risk score
- Transportation distance
- Shipment status
- Sustainability score
- Commodity characteristics

Different shipment conditions should produce different recommendations and explanations.

Examples:
- High risk + very short shelf life -> Ship immediately
- High risk + long distance -> Optimize route
- Medium risk + good shelf life -> Monitor shipment
- Low sustainability score -> Improve storage condition
- High priority score but moderate risk -> Prioritize shipment

Do NOT always recommend "Ship immediately".

The explanation must explicitly reference the shipment data.

Recommendation must be one of:

- Ship immediately
- Prioritize shipment
- Monitor shipment
- Improve storage condition
- Optimize route

Explain the reason based on actual shipment data.

IMPORTANT RULES:
- Do not mention raw decimal numbers.
- Never mention days with decimals.
- Use simple human language.
- If remaining days is negative, say "shipment has exceeded shelf life".
- If remaining days is positive, say "remaining shelf life is X days".
- Do not suggest holding shipment unless explicitly required.

The recommendation must be operational logistics advice.

Allowed recommendations:
- Ship immediately
- Prioritize shipment
- Monitor shipment
- Improve storage condition
- Optimize route

Do not recommend unrealistic actions such as holding shipment for many days.


Return ONLY valid JSON.

{
  "recommendation": "One short operational recommendation.",
  "decision_reason": "Explain WHY using the shipment conditions.",
  "conclusion": "One concise summary sentence.",
  "confidence": 90
}

Rules:
- confidence must be an integer between 85 and 99.
- recommendation maximum 6 words.
- decision_reason maximum 2 sentences.
- conclusion maximum 1 sentence.
- No markdown.
- No extra text.

PROMPT;


$response = Http::withHeaders([

'Authorization'=>'Bearer '.env('OPENROUTER_API_KEY'),

'Content-Type'=>'application/json'

])->post(
'https://openrouter.ai/api/v1/chat/completions',
[

'model'=>'meta-llama/llama-3.1-8b-instruct',

'temperature'=>0,

'messages'=>[

[
'role'=>'user',
'content'=>$prompt
]

]

]);



$content = 
$response->json()['choices'][0]['message']['content']
?? null;


if(!$content){
    return [
        'recommendation'=>'AI response unavailable',
        'decision_reason'=>'No explanation generated',
        'conclusion'=>'Please try again',
        'confidence'=>0
    ];
}


// bersihkan markdown
$content = str_replace(
    ['```json','```'],
    '',
    $content
);


$content = trim($content);


$result = json_decode($content,true);


// kalau JSON gagal
if(json_last_error() !== JSON_ERROR_NONE){

    return [
        'recommendation'=>'AI formatting error',
        'decision_reason'=>$content,
        'conclusion'=>'Unable to parse AI response',
        'confidence'=>0
    ];

}


return $result;

}
    

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