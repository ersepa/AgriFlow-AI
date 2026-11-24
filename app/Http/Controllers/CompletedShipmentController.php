<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class CompletedShipmentController extends Controller
{
    public function index(): View
    {
        $baseQuery = Shipment::query()
            ->whereHas('harvest', fn ($query) => $query->where('user_id', Auth::id()))
            ->where('status', 'Delivered');

        $allSnapshots = (clone $baseQuery)
            ->whereNotNull('completion_snapshot')
            ->pluck('completion_snapshot');

        $riskScores = [];
        $readinessScores = [];

        foreach ($allSnapshots as $snapshot) {
            $analysis = $snapshot['analysis'] ?? [];

            if (isset($analysis['risk_score']) && is_numeric($analysis['risk_score'])) {
                $riskScores[] = (float) $analysis['risk_score'];
            }
            if (isset($analysis['operational_readiness_score']) && is_numeric($analysis['operational_readiness_score'])) {
                $readinessScores[] = (float) $analysis['operational_readiness_score'];
            }
        }

        $completedSummary = [
            'total' => (clone $baseQuery)->count(),
            'avg_risk' => count($riskScores) > 0 ? round(array_sum($riskScores) / count($riskScores), 1) : null,
            'avg_readiness' => count($readinessScores) > 0 ? round(array_sum($readinessScores) / count($readinessScores), 1) : null,
            'snapshot_count' => max(count($riskScores), count($readinessScores)),
        ];

        $shipments = $baseQuery
            ->with('harvest')
            ->latest('delivered_at')
            ->paginate(12);

        return view('completed-shipments.index', compact('shipments', 'completedSummary'));
    }

    public function show(Shipment $shipment): View|RedirectResponse
    {
        abort_unless(
            $shipment->harvest()
                ->where('user_id', Auth::id())
                ->exists(),
            404
        );

        if (!$shipment->isDelivered()) {
            return redirect()
                ->route('shipments.show', $shipment)
                ->with(
                    'info',
                    'Pengiriman ini masih aktif dan berada dalam alur operasional.'
                );
        }

        $shipment->load([
            'harvest',
            'aiAnalyses' => fn ($query) => $query->latest(),
        ]);

        $completionSnapshot = $shipment->completion_snapshot ?? [];
        $finalAnalysis = $completionSnapshot['analysis'] ?? null;
        $routeDecision = $completionSnapshot['route_decision'] ?? null;
        $latestAnalysisRecord = $shipment->aiAnalyses->first();
        $isLegacyCompletion = empty($completionSnapshot);

        return view(
            'completed-shipments.show',
            compact(
                'shipment',
                'completionSnapshot',
                'finalAnalysis',
                'routeDecision',
                'latestAnalysisRecord',
                'isLegacyCompletion'
            )
        );
    }
}