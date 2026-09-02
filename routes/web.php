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
    use App\Services\EnvironmentalService;
use App\Services\Sustainability\FreightCarbonEstimateService;
    use App\Http\Controllers\OperationalDigitalTwinController;
use App\Http\Controllers\CompletedShipmentController;


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

Route::get(
    '/digital-twin',
    [OperationalDigitalTwinController::class, 'index']
)
    ->middleware('auth')
    ->name('digital-twin.index');

Route::post(
    '/digital-twin/{shipment}/simulate',
    [OperationalDigitalTwinController::class, 'simulate']
)
    ->middleware('auth')
    ->name('digital-twin.simulate');

Route::post(
    '/digital-twin/{shipment}/scenarios',
    [OperationalDigitalTwinController::class, 'store']
)
    ->middleware('auth')
    ->name('digital-twin.scenarios.store');

Route::get(
    '/digital-twin/scenarios/history',
    [OperationalDigitalTwinController::class, 'history']
)
    ->middleware('auth')
    ->name('digital-twin.scenarios.history');

Route::get(
    '/digital-twin/scenarios/{scenario}',
    [OperationalDigitalTwinController::class, 'show']
)
    ->middleware('auth')
    ->name('digital-twin.scenarios.show');

Route::post(
    '/digital-twin/scenarios/{scenario}/prefer',
    [OperationalDigitalTwinController::class, 'prefer']
)
    ->middleware('auth')
    ->name('digital-twin.scenarios.prefer');

    // ==========================================
// STEP 6.2 — MULTI-SCENARIO COMPARISON
// ==========================================

Route::post(
    '/digital-twin/{shipment}/compare',
    [OperationalDigitalTwinController::class, 'compare']
)
    ->middleware('auth')
    ->name('digital-twin.compare');

Route::post(
    '/digital-twin/{shipment}/comparison-sets',
    [OperationalDigitalTwinController::class, 'storeComparison']
)
    ->middleware('auth')
    ->name('digital-twin.comparisons.store');

Route::get(
    '/digital-twin/comparisons/history',
    [OperationalDigitalTwinController::class, 'comparisonHistory']
)
    ->middleware('auth')
    ->name('digital-twin.comparisons.history');

Route::get(
    '/digital-twin/comparisons/{comparisonSet}',
    [OperationalDigitalTwinController::class, 'comparisonShow']
)
    ->middleware('auth')
    ->name('digital-twin.comparisons.show');

Route::get('/ai-optimizer/explain/{shipment}',
    [AIOptimizerController::class, 'explain']
)
    ->middleware('auth')
    ->name('ai.explain');

Route::get(
    '/ai-optimizer/route/{shipment}',
    [AIOptimizerController::class, 'routeGeometry']
)
    ->middleware('auth')
    ->name('ai-optimizer.route');

Route::get(
    '/ai-optimizer/freshness-routes/{shipment}',
    [AIOptimizerController::class, 'freshnessRoutes']
)
    ->middleware('auth')
    ->name('ai-optimizer.freshness-routes');

    Route::get('/ai-optimizer', [AIOptimizerController::class, 'index'])
    ->middleware('auth')
    ->name('ai-optimizer');

    Route::get('/ai-analysis/history/{id}', [AiAnalysisController::class, 'show'])
    ->middleware(['auth'])
    ->name('ai-analysis.show');

    Route::delete('/ai-analysis/bulk-destroy', [AiAnalysisController::class, 'bulkDestroy'])->name('ai-analysis.bulk-destroy');
    Route::delete('/ai-analysis/truncate', [AiAnalysisController::class, 'truncate'])->name('ai-analysis.truncate');

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

        // AI DATA
        $totalAnalyses = AiAnalysis::count();

        // 📊 RISK DATA (FIXED)
        $lowRisk = AiAnalysis::where('risk_level', 'Low')->count();
        $mediumRisk = AiAnalysis::where('risk_level', 'Medium')->count();
        $highRisk = AiAnalysis::where('risk_level', 'High')->count();

        $engine = app(DecisionEngine::class);

        $activeShipments = \App\Models\Shipment::with('harvest')
            ->whereIn('status', ['Harvested', 'Packed', 'In Transit'])
            ->get();

        $activeShipmentAnalyses = $activeShipments
            ->map(fn ($shipment) => $engine->analyze($shipment));

        // Operational Readiness is deliberately simple and transparent:
        // 100 - Operational Risk Index. It is not an ESG/LCA metric.
        $avgScore = $activeShipmentAnalyses->isNotEmpty()
            ? round($activeShipmentAnalyses->avg('operational_readiness_score'), 1)
            : 0;

        $greenImpactScore = round($avgScore, 0);

        // Source-backed activity estimate across shipment mass x distance.
        // This does not use the legacy distance-only carbon column values.
        $freightCarbon = app(FreightCarbonEstimateService::class);
        $currentCarbon = round(
            \App\Models\Shipment::with('harvest')
                ->get()
                ->sum(fn ($shipment) =>
                    $freightCarbon->estimateForShipment($shipment)['estimated_kg']
                    ?? 0
                ),
            1
        );

        $aiService = new GeminiService();

        $aiInsight = $aiService->generateDashboardInsight([
            'totalShipments' => $totalShipments,
            'delivered' => $deliveredShipments,
            'highRisk' => $highRisk,
            'avgOperationalReadiness' => $avgScore,
        ]);

$aiInsightText =
    $aiInsight['insight']
    ?? 'No insight available';

$aiRecommendation =
    $aiInsight['recommendation']
    ?? '';

    $latestHighRisk = \App\Models\AiAnalysis::where('risk_level', 'High')
        ->latest()
        ->with('shipment.harvest')
        ->first();
        // ==============================
// AI Executive Summary
// ==============================

$highRiskAnalyses =
    \App\Models\AiAnalysis::where(
        'risk_level',
        'High'
    )->count();

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

$engine = app(DecisionEngine::class);

$highestPriorityShipment = \App\Models\Shipment::with('harvest')
    ->whereIn('status', ['Harvested','Packed','In Transit'])
    ->get()
    ->sortByDesc(function($shipment) use ($engine){
        return $engine->analyze($shipment)['priority_score'];
    })
    ->first();

$operationalRecommendation = null;

if ($highestPriorityShipment) {
    $highestPriorityAnalysis = $engine->analyze($highestPriorityShipment);

    $operationalRecommendation = [
        'action' => $highestPriorityAnalysis['recommended_action'],
        'reason' => $highestPriorityAnalysis['recommendation_reason'],
        'dispatch_deadline' => $highestPriorityAnalysis['dispatch_deadline'],
        'recommended_vehicle' => $highestPriorityAnalysis['recommended_vehicle'],
        'recommended_storage' => $highestPriorityAnalysis['recommended_storage'],
        'risk_score' => $highestPriorityAnalysis['risk_score'],
        'priority_score' => $highestPriorityAnalysis['priority_score'],
    ];
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

/*
|--------------------------------------------------------------------------
| Environmental Intelligence
|--------------------------------------------------------------------------
*/

$environmentService = new \App\Services\EnvironmentalService();

$environment = $environmentService->getEnvironment(null);

$engine = app(DecisionEngine::class);

$highRiskShare = $totalAnalyses > 0
    ? round(
        ($highRisk / $totalAnalyses) * 100
    )
    : 0;

// ==========================================
// Observed Operational Metrics
// ==========================================

// Share of persisted analyses currently
// classified as High risk.
$highRiskShare = $totalAnalyses > 0
    ? round(
        ($highRisk / $totalAnalyses) * 100
    )
    : 0;

// Observed delivery completion rate.
$currentEfficiency = $totalShipments > 0
    ? round(
        (
            $deliveredShipments
            / $totalShipments
        ) * 100
    )
    : 0;

// Current operational risk uses the live active-shipment analyses above.
$averageOperationalRisk =
    $activeShipmentAnalyses->isNotEmpty()
        ? round(
            $activeShipmentAnalyses
                ->avg('risk_score')
        )
        : 0;

$criticalOperationalCount =
    $activeShipmentAnalyses
        ->filter(
            fn ($analysis) =>
                (
                    $analysis[
                        'risk_severity'
                    ]
                    ?? null
                ) === 'Critical'
        )
        ->count(); 

$forecast = $environment['forecast'] ?? [];
$weatherTrend = [];

if (
    $environment &&
    isset($environment['weather']) &&
    isset($forecast['time'])
) {

    $now = now();
    $startIndex = 0;

    // Cari index waktu yang paling dekat dengan waktu sekarang
    foreach ($forecast['time'] as $index => $time) {

        if (\Carbon\Carbon::parse($time)->greaterThanOrEqualTo($now)) {
            $startIndex = $index;
            break;
        }
    }

    // Ambil 6 jam ke depan
    for (
        $i = $startIndex;
        $i < min($startIndex + 6, count($forecast['time']));
        $i++
    ) {

        $weatherTrend[] = [

            'time' => \Carbon\Carbon::parse($forecast['time'][$i])->format('H:i'),

            'temp' => $forecast['temperature_2m'][$i] ?? 0,

            'humidity' => $forecast['relative_humidity_2m'][$i] ?? 0,

            'wind' => $forecast['wind_speed_10m'][$i] ?? 0,

            'rain' => $forecast['precipitation_probability'][$i] ?? 0,

            'cloud' => $forecast['cloud_cover'][$i] ?? 0,

        ];
    }
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

    'optimizeRoute',
    'shipImmediately',

    'operationalRecommendation',
    'dashboardShipments',

    'statusHarvested',
    'statusPacked',
    'statusTransit',
    'statusDelivered',

    'highRiskShare',
    'averageOperationalRisk',
    'criticalOperationalCount',

    'currentCarbon',
    'currentEfficiency',

    'environment',
    'weatherTrend',
));

    })->middleware(['auth'])->name('dashboard');
        Route::resource('harvests', HarvestController::class)
        ->middleware(['auth']);

        Route::get('/completed-shipments', [CompletedShipmentController::class, 'index'])
        ->middleware(['auth'])
        ->name('completed-shipments.index');

        Route::get('/completed-shipments/{shipment}', [CompletedShipmentController::class, 'show'])
        ->middleware(['auth'])
        ->name('completed-shipments.show');

        Route::patch('/shipments/{shipment}/conditions', [ShipmentController::class, 'updateConditions'])
        ->middleware(['auth'])
        ->name('shipments.conditions.update');

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
