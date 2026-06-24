    <?php

    use App\Http\Controllers\ProfileController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\HarvestController;
    use App\Http\Controllers\ShipmentController;
    use App\Http\Controllers\AiAnalysisController;
    use App\Models\Shipment;
    use App\Services\GeminiService;
    use App\Models\AiAnalysis;

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/ai-analysis/history/{id}', [AiAnalysisController::class, 'show'])
    ->middleware(['auth'])
    ->name('ai-analysis.show');

Route::delete('/ai-analysis/{id}', [AiAnalysisController::class, 'destroy'])
    ->name('ai-analysis.delete');

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
