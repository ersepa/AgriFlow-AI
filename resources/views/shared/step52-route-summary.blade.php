@php
    $routeDecision =
        $routeDecision
        ?? $decisionAnalysis['route_decision']
        ?? null;

    $isStorageRoute =
        $isStorageStability
        ?? (
            (
                $routeDecision['analysis']['commodity_profile']['quality_model_type']
                ?? null
            ) === 'storage_stability'
            || !empty(
                $routeDecision['analysis']['quality_prediction']['storage_stability_reference_available']
                ?? false
            )
        );
@endphp

@if($routeDecision)
    @php
        $routeScore =
            $routeDecision['route_score']
            ?? null;

        $feasibility =
            $routeDecision[
                'freshness_feasibility'
            ] ?? 'Unavailable';

        $delayTolerance =
            $routeDecision[
                'delay_tolerance_hours'
            ] ?? null;

        $transitMargin =
            $routeDecision[
                'transit_margin_hours'
            ] ?? null;

        $quality =
            $routeDecision[
                'projected_arrival_quality'
            ] ?? null;

        $risk =
            $routeDecision[
                'projected_risk_score'
            ] ?? null;

        $reason =
            $routeDecision[
                'recommendation_reason'
            ] ?? null;

            $routeDecisionLabel =
    $isStorageRoute
        ? 'Storage-Aware Route Decision'
        : 'Freshness-Aware Route Decision';

$routeDecisionDescription =
    $isStorageRoute
        ? "Evaluates whether the planned route remains compatible with the shipment's operational storage window."
        : "Evaluates whether the planned route preserves the shipment's operational freshness window.";

$safeRouteAction =
    $isStorageRoute
        ? 'Maintain current route and monitor storage-condition margin.'
        : 'Maintain current route and monitor freshness margin.';

$displayReason = $reason;

if ($isStorageRoute && $displayReason) {
    $displayReason = str_ireplace(
        [
            'freshness feasibility',
            'freshness-safe route',
            'operational freshness window',
            'freshness window',
        ],
        [
            'storage-condition feasibility',
            'storage-compatible route',
            'operational storage window',
            'storage-condition window',
        ],
        $displayReason
    );
}
    @endphp

    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-5 pb-5 border-b border-slate-800">
            <div>
                <p class="text-[10px] uppercase font-black tracking-[0.22em] text-teal-400">
                    {{ $routeDecisionLabel }}
                </p>
                <h3 class="text-xl font-black text-white mt-2">
                    Current Route Assessment
                </h3>
                <p class="text-xs text-slate-400 mt-2 max-w-xl leading-relaxed">
                    {{ $routeDecisionDescription }}
                </p>
            </div>

            <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest
                {{ $feasibility === 'Safe' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}
                {{ $feasibility === 'Tight' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '' }}
                {{ $feasibility === 'Breach' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : '' }}
            ">
                {{ $feasibility }}
            </span>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
            <div class="bg-slate-800/70 rounded-2xl border border-slate-700 p-4">
                <p class="text-[9px] uppercase font-black text-slate-500 tracking-widest">
                    Route Score
                </p>
                <p class="text-2xl font-black text-white mt-2">
                    {{ $routeScore !== null ? $routeScore : '—' }}
                    <span class="text-xs text-slate-500">/100</span>
                </p>
            </div>

            <div class="bg-slate-800/70 rounded-2xl border border-slate-700 p-4">
                <p class="text-[9px] uppercase font-black text-slate-500 tracking-widest">
                    Arrival Quality
                </p>
                @if($quality !== null)
                    <p class="text-2xl font-black text-white mt-2">
                        {{ $quality }}
                        <span class="text-xs text-slate-500">/100</span>
                    </p>
                @else
                    <p class="text-sm font-black text-slate-300 mt-2">
                        Not estimated
                    </p>
                    <p class="text-[10px] text-slate-500 mt-1 leading-relaxed">
                        No validated fresh-produce quality curve for this commodity model.
                    </p>
                @endif
            </div>

            <div class="bg-slate-800/70 rounded-2xl border border-slate-700 p-4">
                <p class="text-[9px] uppercase font-black text-slate-500 tracking-widest">
                    Operational Risk
                </p>
                <p class="text-2xl font-black text-rose-400 mt-2">
                    {{ $risk !== null ? $risk : '—' }}
                    <span class="text-xs text-slate-500">/100</span>
                </p>
            </div>

            <div class="bg-slate-800/70 rounded-2xl border border-slate-700 p-4">
                <p class="text-[9px] uppercase font-black text-slate-500 tracking-widest">
                    Delay Tolerance
                </p>
                <p class="text-2xl font-black text-white mt-2">
                    {{ $delayTolerance !== null ? number_format($delayTolerance, 1) : '—' }}
                    <span class="text-xs text-slate-500">h</span>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="bg-slate-950/40 rounded-2xl border border-slate-800 p-4">
                <p class="text-[9px] uppercase font-black text-slate-500 tracking-widest">
                    Transit Margin
                </p>
                <p class="text-lg font-black text-white mt-1">
                    {{ $transitMargin !== null ? number_format($transitMargin, 1) . ' h' : 'Unavailable' }}
                </p>
            </div>

            <div class="bg-slate-950/40 rounded-2xl border border-slate-800 p-4">
                <p class="text-[9px] uppercase font-black text-slate-500 tracking-widest">
                    Route Action
                </p>
                <p class="text-sm font-bold mt-1
                    {{ $feasibility === 'Safe' ? 'text-emerald-400' : '' }}
                    {{ $feasibility === 'Tight' ? 'text-amber-400' : '' }}
                    {{ $feasibility === 'Breach' ? 'text-rose-400' : '' }}
                ">
                    @if($feasibility === 'Safe')
                        {{ $safeRouteAction }}
                    @elseif($feasibility === 'Tight')
                        Prioritize dispatch and avoid additional delay.
                    @elseif($feasibility === 'Breach')
                        Routing alone is insufficient; operational intervention is required.
                    @else
                        Route assessment unavailable.
                    @endif
                </p>
            </div>
        </div>

@if($displayReason)
    <p class="text-xs text-slate-400 leading-relaxed mt-5 pt-5 border-t border-slate-800">
        {{ $displayReason }}
    </p>
@endif
    </div>
@endif
