<?php

    use App\Http\Controllers\ProfileController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\HarvestController;
    use App\Http\Controllers\ShipmentController;
    use App\Http\Controllers\AiAnalysisController;
    use App\Models\Shipment;
    use App\Models\AiAnalysis;
    use App\Services\GeminiService;
    use App\Http\Controllers\AIOptimizerController;
    use App\Services\AI\DecisionEngine;
    use App\Services\EnvironmentalService;
    use App\Services\Sustainability\FreightCarbonEstimateService;
    use App\Http\Controllers\OperationalDigitalTwinController;
    use App\Http\Controllers\CompletedShipmentController;
    use Illuminate\Support\Facades\Auth;

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

Route::delete(
    '/ai-analysis/bulk-destroy',
    [AiAnalysisController::class, 'bulkDestroy']
)
    ->middleware('auth')
    ->name('ai-analysis.bulk-destroy');

Route::delete(
    '/ai-analysis/truncate',
    [AiAnalysisController::class, 'truncate']
)
    ->middleware('auth')
    ->name('ai-analysis.truncate');

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
    

Route::get('/dashboard', function () {
    $userId = Auth::id();

    /*
     * Every dashboard query must be scoped to the authenticated user.
     * Shipment ownership is inherited through its harvest.
     */
    $ownedHarvests = function () use ($userId) {
        return \App\Models\Harvest::query()
            ->where('user_id', $userId);
    };

    $ownedShipments = function () use ($userId) {
        return \App\Models\Shipment::query()
            ->whereHas('harvest', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
    };

    $ownedAnalyses = function () use ($userId) {
        return AiAnalysis::query()
            ->whereHas('shipment.harvest', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
    };

    $totalHarvests = $ownedHarvests()->count();
    $totalWeight = $ownedHarvests()->sum('weight');

    $totalShipments = $ownedShipments()->count();

    $deliveredShipments = $ownedShipments()
        ->where('status', 'Delivered')
        ->count();

    $totalAnalyses = $ownedAnalyses()->count();

    $engine = app(DecisionEngine::class);

    // Dashboard operational KPIs are calculated from CURRENT ACTIVE
    // shipments only. Historical AiAnalysis rows remain audit snapshots.
    $activeShipments = $ownedShipments()
        ->with('harvest')
        ->whereIn('status', ['Harvested', 'Packed', 'In Transit'])
        ->get();

    $activeOperationalRows = $activeShipments
        ->map(function ($shipment) use ($engine) {
            return [
                'shipment' => $shipment,
                'analysis' => $engine->analyze($shipment),
            ];
        });

    $activeShipmentAnalyses = $activeOperationalRows->pluck('analysis');
    $activeShipmentCount = $activeOperationalRows->count();

    // Final competition risk semantics: exactly three levels.
    // Low 0-29, Moderate 30-59, High 60-100.
    $lowRisk = $activeShipmentAnalyses
        ->where('risk_severity', 'Low')
        ->count();

    $mediumRisk = $activeShipmentAnalyses
        ->where('risk_severity', 'Moderate')
        ->count();

    $highRisk = $activeShipmentAnalyses
        ->where('risk_severity', 'High')
        ->count();

    // Urgency is intentionally separate from risk level. A High-risk
    // shipment can require Immediate review without introducing a fourth
    // risk category.
    $immediateAttentionCount = $activeShipmentAnalyses
        ->where('urgency_level', 'Immediate')
        ->count();

    $avgScore = $activeShipmentAnalyses->isNotEmpty()
        ? round($activeShipmentAnalyses->avg('operational_readiness_score'), 1)
        : 0;

    $greenImpactScore = round($avgScore, 0);

    $averageOperationalRisk = $activeShipmentAnalyses->isNotEmpty()
        ? round($activeShipmentAnalyses->avg('risk_score'))
        : 0;

    $highRiskShare = $activeShipmentCount > 0
        ? round(($highRisk / $activeShipmentCount) * 100)
        : 0;

    // Source-backed activity estimate across shipment mass x distance.
    $freightCarbon = app(FreightCarbonEstimateService::class);
$currentCarbon = round(
    $ownedShipments()
        ->with('harvest')
        ->get()
        ->sum(fn ($shipment) =>
            $freightCarbon->estimateForShipment($shipment)['estimated_kg'] ?? 0
        ),
    1
);

    $dashboardShipments = $activeOperationalRows
        ->map(function ($row) {
            $shipment = $row['shipment'];
            $analysis = $row['analysis'];

            return [
                'shipment_id' => $shipment->id,
                'commodity' => $shipment->harvest?->commodity ?? 'Tidak diketahui',
                'origin' => $shipment->origin,
                'destination' => $shipment->destination,
                'origin_lat' => $shipment->origin_lat,
                'origin_lng' => $shipment->origin_lng,
                'destination_lat' => $shipment->destination_lat,
                'destination_lng' => $shipment->destination_lng,
                'status' => $shipment->status,
                'distance' => round($shipment->distance_km ?? 0),
                'priority' => $analysis['priority_score'] ?? 0,
                'priority_level' => $analysis['priority_level'] ?? 'Unknown',
                'risk' => $analysis['risk_score'] ?? 0,
                'risk_severity' => $analysis['risk_severity'] ?? 'Unknown',
                'urgency_level' => $analysis['urgency_level'] ?? 'Routine',
                'recommended_action' => $analysis['recommended_action']
                    ?? 'Tinjau kondisi pengiriman saat ini',
            ];
        })
        ->values();

        $highestActiveRiskScore = $dashboardShipments->isNotEmpty()
    ? $dashboardShipments->max('risk')
    : null;

$operationalFocusShipments = $highestActiveRiskScore !== null
    ? $dashboardShipments
        ->filter(
            fn ($shipment) =>
                (int) ($shipment['risk'] ?? 0)
                === (int) $highestActiveRiskScore
        )
        ->values()
    : collect();

$operationalFocusCount =
    $operationalFocusShipments->count();

$operationalFocusUrgency =
    $highestActiveRiskScore !== null
        ? (
            $highestActiveRiskScore >= 80
                ? 'Immediate'
                : (
                    $highestActiveRiskScore >= 60
                        ? 'High'
                        : (
                            $highestActiveRiskScore >= 30
                                ? 'Elevated'
                                : 'Normal'
                        )
                )
        )
        : null;

    $priorityActionShipments = $dashboardShipments
        ->where('risk_severity', 'High')
        ->sortByDesc('risk')
        ->values();

    $topHighRiskShipment = $priorityActionShipments->first();

    // Counts shown in the dashboard insight are deterministic. LLM output is
    // not allowed to redefine or summarize operational counts differently.
$aiInsightText = sprintf(
    'AgriFlow sedang memantau %d pengiriman aktif: %d berisiko tinggi, %d berisiko sedang, dan %d berisiko rendah. Sebanyak %d pengiriman telah menyelesaikan seluruh proses operasional.',
    $activeShipmentCount,
    $highRisk,
    $mediumRisk,
    $lowRisk,
    $deliveredShipments
);

$statusHarvested = $ownedShipments()
    ->where('status', 'Harvested')
    ->count();

$statusPacked = $ownedShipments()
    ->where('status', 'Packed')
    ->count();

$statusTransit = $ownedShipments()
    ->where('status', 'In Transit')
    ->count();

$statusDelivered = $deliveredShipments;

    $currentEfficiency = $totalShipments > 0
        ? round(($deliveredShipments / $totalShipments) * 100)
        : 0;

    /*
    |--------------------------------------------------------------------------
    | Environmental Intelligence
    |--------------------------------------------------------------------------
    */
    $environmentService = new \App\Services\EnvironmentalService();
    $environment = $environmentService->getEnvironment(null);

    $forecast = $environment['forecast'] ?? [];
    $weatherTrend = [];

    if (
        $environment
        && isset($environment['weather'])
        && isset($forecast['time'])
    ) {
        $now = now();
        $startIndex = 0;

        foreach ($forecast['time'] as $index => $time) {
            if (\Carbon\Carbon::parse($time)->greaterThanOrEqualTo($now)) {
                $startIndex = $index;
                break;
            }
        }

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
        'activeShipmentCount',
        'totalAnalyses',
        'avgScore',
        'lowRisk',
        'mediumRisk',
        'highRisk',
        'immediateAttentionCount',
        'greenImpactScore',
        'dashboardShipments',
        'priorityActionShipments',
        'topHighRiskShipment',
        'statusHarvested',
        'statusPacked',
        'statusTransit',
        'statusDelivered',
        'highRiskShare',
        'averageOperationalRisk',
        'currentCarbon',
        'currentEfficiency',
        'aiInsightText',
        'environment',
        'weatherTrend',
        'highestActiveRiskScore',
        'operationalFocusShipments',
        'operationalFocusCount',
        'operationalFocusUrgency',
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