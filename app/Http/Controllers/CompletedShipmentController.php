<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompletedShipmentController extends Controller
{
    public function index(): View
    {
        $shipments = Shipment::with('harvest')
            ->where('status', 'Delivered')
            ->orderByDesc('delivered_at')
            ->orderByDesc('id')
            ->paginate(12);

        return view(
            'completed-shipments.index',
            compact('shipments')
        );
    }

    public function show(Shipment $shipment): View|RedirectResponse
    {
        if (!$shipment->isDelivered()) {
            return redirect()
                ->route('shipments.show', $shipment)
                ->with(
                    'info',
                    'This shipment is still active and remains in the operational workflow.'
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
