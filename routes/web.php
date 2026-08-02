    <?php

    use App\Http\Controllers\ProfileController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\HarvestController;
    use App\Http\Controllers\ShipmentController;
    use App\Http\Controllers\AiAnalysisController;
    use App\Models\Shipment;
    use App\Services\GeminiService;
    use App\Models\AiAnalysis;
    use App\Http\Controllers\AIOptimizerController;
    use App\Services\AI\DecisionEngine;
    use App\Http\Controllers\SimulationController;
    use App\Services\EnvironmentalService;


Route::get('/test-environment', function () {

    $shipment = \App\Models\Shipment::whereNotNull('origin_lat')
        ->whereNotNull('origin_lng')
        ->latest()
        ->first();

    $service = new \App\Services\EnvironmentalService();

    return $service->getEnvironment($shipment);

});

    Route::get('/', function () {
        return view('welcome');
    });

    Route::middleware('auth')->group(function(){

    Route::get('/simulation',
        [SimulationController::class,'index']
    )->name('simulation.index');

});

Route::post(
    '/simulation/run',
    [SimulationController::class,'run']
)->name('simulation.run');  

Route::get('/ai-optimizer/explain/{shipment}', 
    [AIOptimizerController::class, 'explain']
)->name('ai.explain');

    Route::get('/ai-optimizer', [AIOptimizerController::class, 'index'])
    ->middleware('auth')
    ->name('ai-optimizer');

    Route::get('/ai-analysis/history/{id}', [AiAnalysisController::class, 'show'])
    ->middleware(['auth'])
    ->name('ai-analysis.show');

    Route::post('/chat', [App\Http\Controllers\ChatController::class, 'chat'])->middleware('auth');

Route::get('/ai-analysis/history', [AiAnalysisController::class, 'history'])
    ->middleware(['auth'])
    ->name('ai-analysis.history');

        // Tambahkan route ini di bawah route /ai-analysis GET lu
Route::delete('/ai-analysis/history/{id}', [AiAnalysisController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('ai-analysis.destroy');

    Route::post('/ai-analysis/{shipment}', [AiAnalysisController::class, 'analyze'])
        ->middleware(['auth'])
        ->name('ai.analysis.run');

    Route::get('/test-ai', function () {

        $shipment = Shipment::with('harvest')->first();

        $service = new GeminiService();

        return $service->analyzeShipment([
            'commodity' => $shipment->harvest->commodity,
            'origin' => $shipment->origin,
            'destination' => $shipment->destination,
            'status' => $shipment->status,
        ]);

    });
    

    Route::get('/dashboard', function () {

        $totalHarvests = \App\Models\Harvest::count();
        $totalWeight = \App\Models\Harvest::sum('weight');
        
        $totalShipments = \App\Models\Shipment::count();
        $deliveredShipments = \App\Models\Shipment::where('status', 'Delivered')->count();
        

        $mostRisky = \App\Models\AiAnalysis::select('shipment_id')
        ->selectRaw('COUNT(*) as total')
        ->groupBy('shipment_id')
        ->orderByDesc('total')
        ->first();

    $mostRiskyShipment = null;

    if ($mostRisky) {
        $mostRiskyShipment = \App\Models\Shipment::find($mostRisky->shipment_id);
    }

        // 🤖 AI DATA
        $totalAnalyses = AiAnalysis::count();
    $avgScore = AiAnalysis::avg('sustainability_score') ?? 0;

        // 📊 RISK DATA (FIXED)
        $lowRisk = AiAnalysis::where('risk_level', 'Low')->count();
        $mediumRisk = AiAnalysis::where('risk_level', 'Medium')->count();
        $highRisk = AiAnalysis::where('risk_level', 'High')->count();

        $aiService = new GeminiService();

    $aiInsight = $aiService->generateDashboardInsight([
        'totalShipments' => $totalShipments,
        'delivered' => $deliveredShipments,
        'highRisk' => $highRisk,
        'avgScore' => $avgScore,
    ]);

    // Asumsi: Waste Prevented adalah (Total Weight * (avgScore/100))
        $greenImpactScore = round($avgScore, 0); 
        $totalWaste = round($totalWeight * ($avgScore / 100), 1);

    $decoded = json_decode($aiInsight, true);

    $aiInsightText = $decoded['insight'] ?? 'No insight available';
    $aiRecommendation = $decoded['recommendation'] ?? '';

    $latestHighRisk = \App\Models\AiAnalysis::where('risk_level', 'High')
        ->latest()
        ->with('shipment.harvest')
        ->first();
        // ==============================
// AI Executive Summary
// ==============================

$criticalShipments = \App\Models\AiAnalysis::where('risk_level', 'High')->count();

$optimizeRoute = \App\Models\AiAnalysis::where(
    'recommendations',
    'like',
    '%Optimize route%'
)->count();

$shipImmediately = \App\Models\AiAnalysis::where(
    'recommendations',
    'like',
    '%Ship immediately%'
)->count();

$estimatedWasteReduction = min(
    100,
    round($greenImpactScore * 0.35)
);

$engine = new DecisionEngine();

$highestPriorityShipment = \App\Models\Shipment::with('harvest')
    ->whereIn('status', ['Harvested','Packed','In Transit'])
    ->get()
    ->sortByDesc(function($shipment) use ($engine){
        return $engine->analyze($shipment)['priority_score'];
    })
    ->first();

$operationalRecommendation = null;

if($highestPriorityShipment){
    $operationalRecommendation =
        $engine->generateOperationalRecommendation($highestPriorityShipment);
}

$dashboardShipments = \App\Models\Shipment::with('harvest')
    ->whereIn('status', [
        'Harvested',
        'Packed',
        'In Transit'
    ])
    ->get()
    ->map(function ($shipment) use ($engine) {

        $analysis = $engine->analyze($shipment);

        return [

            'commodity' => $shipment->harvest->commodity,

            'origin' => $shipment->origin,
            'destination' => $shipment->destination,

            'origin_lat' => $shipment->origin_lat,
            'origin_lng' => $shipment->origin_lng,

            'destination_lat' => $shipment->destination_lat,
            'destination_lng' => $shipment->destination_lng,

            'status' => $shipment->status,

            'distance' => round($shipment->distance_km ?? 0),

            'priority' => $analysis['priority_score'],

            'risk' => $analysis['risk_score'],

            'priority_level' => $analysis['priority_level'],

        ];
    });
    $statusHarvested = \App\Models\Shipment::where('status','Harvested')->count();

$statusPacked = \App\Models\Shipment::where('status','Packed')->count();

$statusTransit = \App\Models\Shipment::where('status','In Transit')->count();

$statusDelivered = \App\Models\Shipment::where('status','Delivered')->count();

$predictionTrend = [];

/*
|--------------------------------------------------------------------------
| Live Environmental Intelligence
|--------------------------------------------------------------------------
*/

$environment = null;

$latestShipment = \App\Models\Shipment::whereNotNull('origin_lat')
    ->whereNotNull('origin_lng')
    ->latest()
    ->first();

if ($latestShipment) {

    $environmentService = new EnvironmentalService();

    $environment = $environmentService->getEnvironment($latestShipment);

}

$engine = new DecisionEngine();

foreach(range(1,7) as $day){

    $riskAverage = 0;

    $count = 0;

    foreach(
        \App\Models\Shipment::with('harvest')
        ->whereIn('status',['Harvested','Packed','In Transit'])
        ->get()
        as $shipment
    ){

        $analysis = $engine->analyze($shipment);

        $riskAverage += $analysis['risk_score'];

        $count++;

    }

    $predictionTrend[] = max(
        5,
        round(($riskAverage / max($count,1)) - ($day*3))
    );

}
      $currentRisk = $totalAnalyses > 0
    ? round(($highRisk / $totalAnalyses) * 100)
    : 0;

// Proyeksi setelah semua rekomendasi AI diterapkan
$projectedRisk = max(0, round($currentRisk * 0.65));

$currentWaste = round($totalWaste, 1);
$projectedWaste = round($currentWaste * 0.68, 1);

$currentCarbon = round(
    \App\Models\Shipment::sum('carbon_emission'),
    1
);

$projectedCarbon = round($currentCarbon * 0.82, 1);

$currentEfficiency = $totalShipments > 0
    ? round(($deliveredShipments / $totalShipments) * 100)
    : 0;

$projectedEfficiency = min(
    100,
    $currentEfficiency + 22
);

// Confidence dihitung dari kualitas data
$projectionConfidence = min(
    98,
    80 + floor($totalAnalyses * 0.8)
);

$riskReduction = $currentRisk - $projectedRisk;
$wasteSaved = $currentWaste - $projectedWaste;
$carbonSaved = $currentCarbon - $projectedCarbon;
$efficiencyGain = $projectedEfficiency - $currentEfficiency;  

$forecast = $environment['forecast'] ?? [];
$currentHour = now()->hour;
$weatherTrend = [];

if (
    $environment &&
    isset($environment['weather']) &&
    isset($forecast['time'])
) {

    // Current weather
    $weatherTrend[] = [
        'time' => now()->format('H:i'),
        'temp' => $environment['weather']['temperature_2m'] ?? null,
        'humidity' => $environment['weather']['relative_humidity_2m'] ?? null,
        'wind' => $environment['weather']['wind_speed_10m'] ?? null,
        'rain' => $forecast['precipitation_probability'][$currentHour] ?? 0,
        'cloud' => $environment['weather']['cloud_cover'] ?? null,
    ];

    // Forecast 5 jam
    for (
        $i = $currentHour + 1;
        $i < min($currentHour + 6, count($forecast['time']));
        $i++
    ) {
        $weatherTrend[] = [
            'time' => \Carbon\Carbon::parse($forecast['time'][$i])->format('H:i'),
            'temp' => $forecast['temperature_2m'][$i] ?? null,
            'humidity' => $forecast['relative_humidity_2m'][$i] ?? null,
            'wind' => $forecast['wind_speed_10m'][$i] ?? null,
            'rain' => $forecast['precipitation_probability'][$i] ?? 0,
            'cloud' => $forecast['cloud_cover'][$i] ?? null,
        ];
    }
}

// sebelum return view()
if (!$environment) {
    $environment = [
        'location' => 'Unknown',
        'updated_at' => now(),
        'temperature' => 0,
        'humidity' => 0,
        'rain' => 0,
        'cloud_cover' => 0,
        'wind_speed' => 0,
    ];
}

    return view('dashboard', compact(
        'totalHarvests',
        'totalWeight',
        'totalShipments',
        'deliveredShipments',
        'totalAnalyses',
        'avgScore',
        'lowRisk',
        'mediumRisk',
        'highRisk',
        'mostRiskyShipment',
        'aiInsight',
        'aiInsightText',
        'aiRecommendation',
        'latestHighRisk',
        'greenImpactScore', 
        'totalWaste',
        'criticalShipments',
'optimizeRoute',
'shipImmediately',
'estimatedWasteReduction',
'operationalRecommendation',
'dashboardShipments',
'statusHarvested',
'statusPacked',
'statusTransit',
'statusDelivered',
'predictionTrend',
'currentRisk',
'projectedRisk',

'currentWaste',
'projectedWaste',

'currentCarbon',
'projectedCarbon',

'currentEfficiency',
'projectedEfficiency',

'projectionConfidence',

'riskReduction',
'wasteSaved',
'carbonSaved',
'efficiencyGain',
'environment',
'weatherTrend',
    ));

    })->middleware(['auth'])->name('dashboard');
        Route::resource('harvests', HarvestController::class)
        ->middleware(['auth']);

        Route::resource('shipments', ShipmentController::class)
        ->middleware(['auth']);

Route::get('/ai-analysis', [AiAnalysisController::class, 'index'])
    ->middleware(['auth'])
    ->name('ai-analysis.index');
        

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';
