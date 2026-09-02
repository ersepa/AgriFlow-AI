<x-app-layout>
    @php
        $final = $finalAnalysis ?? [];
        $snapshotShipment = $completionSnapshot['shipment'] ?? [];
        $condition = $final['condition_assessment']
            ?? ($final['quality_prediction']['condition_assessment'] ?? []);
        $conditionStatus = $condition['overall_status'] ?? null;
        $risk = $final['risk_score'] ?? null;
        $riskLevel = $final['risk_severity'] ?? $final['risk_level'] ?? null;
        $riskLabel = $riskLevel === 'Medium' ? 'Moderate' : $riskLevel;
        $readiness = $final['operational_readiness_score'] ?? null;
        $carbon = $final['carbon_kg'] ?? $shipment->carbon_emission;
        $routeScore = $routeDecision['route_score'] ?? null;
        $routeFeasibility = $routeDecision['freshness_feasibility'] ?? null;
        $quality = $final['quality_at_arrival'] ?? null;
        $modelType = $final['commodity_profile']['quality_model_type'] ?? null;
        $isDry = $modelType === 'storage_stability';
    @endphp

    <div class="max-w-6xl mx-auto py-10 px-4 sm:px-6">
        <a href="{{ route('completed-shipments.index') }}"
           class="inline-flex items-center gap-2 text-slate-500 hover:text-emerald-700 text-xs font-black uppercase tracking-widest mb-7 transition-colors">
            ← Completed Shipments
        </a>

        @foreach(['success' => 'emerald', 'warning' => 'amber', 'info' => 'cyan'] as $flash => $tone)
            @if(session($flash))
                <div class="mb-6 rounded-2xl border px-5 py-4 text-sm font-bold
                    {{ $tone === 'emerald' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : '' }}
                    {{ $tone === 'amber' ? 'border-amber-200 bg-amber-50 text-amber-800' : '' }}
                    {{ $tone === 'cyan' ? 'border-cyan-200 bg-cyan-50 text-cyan-800' : '' }}">
                    {{ session($flash) }}
                </div>
            @endif
        @endforeach

        <section class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-6 sm:p-8 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-5">
                <div>
                    <p class="text-[10px] uppercase font-black tracking-[0.22em] text-emerald-700">Shipment Completed</p>
                    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 capitalize mt-2">{{ $shipment->harvest?->commodity ?? 'Unknown commodity' }}</h1>
                    <p class="text-sm text-slate-600 mt-2">{{ $shipment->origin }} → {{ $shipment->destination }}</p>
                </div>
                <div class="sm:text-right">
                    <span class="inline-flex rounded-xl border border-emerald-200 bg-emerald-100 px-4 py-2 text-xs font-black uppercase tracking-widest text-emerald-800">Delivered</span>
                    <p class="text-xs font-bold text-slate-500 mt-3">{{ $shipment->delivered_at ? $shipment->delivered_at->format('d M Y · H:i') : 'Delivery time not recorded' }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-emerald-200/70 bg-white/80 p-5">
                <h2 class="text-sm font-black text-slate-900">Operational decision cycle closed.</h2>
                <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                    AgriFlow no longer generates new dispatch, route-optimization, condition-update, intervention, or Digital Twin actions for this shipment. Historical evidence remains available below.
                </p>
            </div>
        </section>

        @if($isLegacyCompletion)
            <div class="mt-6 rounded-3xl border border-amber-200 bg-amber-50 p-6">
                <p class="text-[10px] uppercase font-black tracking-widest text-amber-700">Legacy Completion</p>
                <h2 class="text-lg font-black text-slate-900 mt-2">No immutable final snapshot is available for this older delivered record.</h2>
                <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                    AgriFlow does not recompute a historical completion using today's shipment state. Persisted analysis history and recorded shipment data remain available below.
                </p>
            </div>
        @else
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-[9px] uppercase font-black tracking-widest text-slate-400">Final Operational Risk</p>
                    <p class="text-2xl font-black text-slate-900 mt-2">{{ $risk !== null ? $risk : '—' }}<span class="text-xs text-slate-400">/100</span></p>
                    <p class="text-xs font-bold text-slate-500 mt-1">{{ $riskLabel ?? 'Unavailable' }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-[9px] uppercase font-black tracking-widest text-slate-400">Final Readiness</p>
                    <p class="text-2xl font-black text-slate-900 mt-2">{{ $readiness !== null ? $readiness : '—' }}<span class="text-xs text-slate-400">/100</span></p>
                    <p class="text-xs text-slate-500 mt-1">Final active-state snapshot</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-[9px] uppercase font-black tracking-widest text-slate-400">Route Record</p>
                    <p class="text-2xl font-black text-slate-900 mt-2">{{ $routeScore !== null ? $routeScore : '—' }}<span class="text-xs text-slate-400">/100</span></p>
                    <p class="text-xs font-bold text-slate-500 mt-1">{{ $routeFeasibility ?? 'Unavailable' }}</p>
                </div>
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-[9px] uppercase font-black tracking-widest text-slate-400">Road-Freight CO₂e</p>
                    <p class="text-2xl font-black text-slate-900 mt-2">{{ $carbon !== null ? number_format((float) $carbon, 2) : '—' }}</p>
                    <p class="text-xs text-slate-500 mt-1">kg CO₂e estimate</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-[10px] uppercase font-black tracking-[0.2em] text-indigo-600">Final Condition Record</p>
                    <h2 class="text-xl font-black text-slate-900 mt-2">{{ $isDry ? 'Storage-Stability Condition' : 'Fresh-Produce Condition' }}</h2>
                    <p class="text-sm font-bold text-slate-700 mt-4">{{ $conditionStatus ?? 'Condition evidence unavailable' }}</p>

                    <dl class="mt-5 space-y-3 text-sm">
                        @if($isDry)
                            <div class="flex justify-between gap-5"><dt class="text-slate-500">Recorded moisture</dt><dd class="font-black text-slate-800">{{ $shipment->recorded_moisture_percent !== null ? number_format($shipment->recorded_moisture_percent, 1) . '%' : '—' }}</dd></div>
                        @else
                            <div class="flex justify-between gap-5"><dt class="text-slate-500">Recorded temperature</dt><dd class="font-black text-slate-800">{{ $shipment->recorded_temperature_c !== null ? number_format($shipment->recorded_temperature_c, 1) . '°C' : '—' }}</dd></div>
                        @endif
                        <div class="flex justify-between gap-5"><dt class="text-slate-500">Recorded RH</dt><dd class="font-black text-slate-800">{{ $shipment->recorded_relative_humidity_percent !== null ? number_format($shipment->recorded_relative_humidity_percent, 1) . '% RH' : '—' }}</dd></div>
                        <div class="flex justify-between gap-5"><dt class="text-slate-500">Evidence source</dt><dd class="font-black text-slate-800">{{ $shipment->condition_source === 'manual_entry' ? 'Manual Entry' : ($shipment->condition_source ?? 'Not recorded') }}</dd></div>
                    </dl>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-[10px] uppercase font-black tracking-[0.2em] text-cyan-600">Final Arrival / Storage Context</p>
                    @if($isDry)
                        <h2 class="text-xl font-black text-slate-900 mt-2">Quality score intentionally not generated</h2>
                        <p class="text-sm text-slate-600 mt-3 leading-relaxed">This dry-commodity model preserves the storage-stability assessment rather than inventing a fresh-produce Quality-at-Arrival score.</p>
                    @else
                        <h2 class="text-xl font-black text-slate-900 mt-2">Arrival Quality</h2>
                        <p class="text-3xl font-black text-slate-900 mt-4">{{ $quality !== null ? $quality . '/100' : 'Not estimated' }}</p>
                        <p class="text-sm text-slate-500 mt-2">{{ $final['quality_status'] ?? 'Unavailable' }}</p>
                    @endif
                </div>
            </div>
        @endif

        <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="flex items-end justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <p class="text-[10px] uppercase font-black tracking-[0.2em] text-slate-400">Audit Trail</p>
                    <h2 class="text-xl font-black text-slate-900 mt-2">Analysis History</h2>
                </div>
                <a href="{{ route('ai-analysis.history') }}" class="text-xs font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-700">View all history →</a>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($shipment->aiAnalyses as $record)
                    <a href="{{ route('ai-analysis.show', $record) }}" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4 hover:border-indigo-200 hover:bg-indigo-50/40 transition-colors">
                        <div>
                            <p class="text-sm font-black text-slate-900">{{ $record->risk_level === 'Medium' ? 'Moderate' : $record->risk_level }} Risk · {{ $record->waste_probability }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $record->created_at?->format('d M Y · H:i') }}</p>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600">View snapshot →</span>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No persisted analysis history is available for this shipment.</p>
                @endforelse
            </div>
        </section>

        <section class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-[10px] uppercase font-black tracking-[0.2em] text-slate-400">Technical Record</p>
            <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-5 text-sm">
                <div><dt class="text-slate-500">Weight</dt><dd class="font-black text-slate-900 mt-1">{{ $shipment->harvest?->weight !== null ? number_format($shipment->harvest->weight, 0) . ' kg' : 'Unavailable' }}</dd></div>
                <div><dt class="text-slate-500">Distance</dt><dd class="font-black text-slate-900 mt-1">{{ $shipment->distance_km !== null ? number_format($shipment->distance_km, 1) . ' km' : 'Unavailable' }}</dd></div>
                <div><dt class="text-slate-500">Transit Duration</dt><dd class="font-black text-slate-900 mt-1">{{ $shipment->duration_hours !== null ? number_format($shipment->duration_hours, 1) . ' h' : 'Unavailable' }}</dd></div>
                <div><dt class="text-slate-500">Snapshot Version</dt><dd class="font-black text-slate-900 mt-1">{{ $completionSnapshot['snapshot_version'] ?? 'Legacy' }}</dd></div>
            </dl>
        </section>
    </div>
</x-app-layout>
