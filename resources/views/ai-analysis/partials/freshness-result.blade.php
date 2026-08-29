@php
    $qualityPrediction = session('quality_prediction', []);
    $predictionAvailable = $qualityPrediction['prediction_available'] ?? false;

    $qualityAtDeparture = session('quality_at_departure');
    $qualityAtArrival = session('quality_at_arrival');
    $qualityStatus = session('quality_status', 'Unavailable');
    $qualityLoss = session('quality_loss_during_transit');
    $remainingShelfLife = session('predicted_remaining_shelf_life_days');
    $safeTransitWindow = session('safe_transit_window_hours');
    $safeTransitStatus = session('safe_transit_status', 'Unavailable');
    $temperatureAssessment = session('temperature_assessment', []);
    $temperatureStatus = $temperatureAssessment['status'] ?? 'Not provided';
    $dataConfidence = session('data_confidence', 0);

    $expiryConstraintApplied = $qualityPrediction['expiry_constraint_applied'] ?? false;
    $reconciliationStatus = $qualityPrediction['shelf_life_reconciliation_status'] ?? null;
    $reconciliationMessage = $qualityPrediction['shelf_life_reconciliation_message'] ?? null;

    $referenceQuality = $qualityPrediction['reference_quality_at_arrival'] ?? null;
    $recordedFreshness = $qualityPrediction['recorded_freshness_index_at_arrival'] ?? null;
    $referenceRemaining = $qualityPrediction['reference_remaining_shelf_life_at_arrival_days'] ?? null;
    $recordedRemainingArrival = $qualityPrediction['recorded_remaining_at_arrival_days'] ?? null;
    $recordedRemainingDepartureHours = $qualityPrediction['recorded_remaining_hours'] ?? null;
    $recordedRemainingArrivalHours = $qualityPrediction['recorded_remaining_at_arrival_hours'] ?? null;
    $plannedTransitHours = $qualityPrediction['planned_transit_hours'] ?? null;
    $transitMarginHours = $qualityPrediction['transit_margin_hours'] ?? null;

    $qualityTone = match (true) {
        $qualityAtArrival === null => 'slate',
        $qualityAtArrival >= 85 => 'emerald',
        $qualityAtArrival >= 70 => 'cyan',
        $qualityAtArrival >= 50 => 'amber',
        default => 'rose',
    };
@endphp

@if(!empty($qualityPrediction))
    <div class="mb-8 bg-slate-800/60 border border-slate-700/80 rounded-3xl p-6 sm:p-8 relative overflow-hidden">
        <div class="absolute -top-20 -right-20 w-52 h-52 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-5 mb-6 pb-5 border-b border-slate-700">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.25em] text-cyan-400 font-black">
                        Freshness Intelligence · Step 3.2
                    </p>
                    <h2 class="text-2xl font-black text-white mt-1">
                        Operational Condition at Arrival
                    </h2>
                    <p class="text-slate-400 text-xs mt-2 max-w-2xl leading-relaxed">
                        Conservative result combining commodity reference life, recorded expiry,
                        harvest age, planned transit, and available temperature conditions.
                    </p>
                </div>

                @if($predictionAvailable && $qualityAtArrival !== null)
                    <div class="sm:text-right">
                        <div class="flex sm:justify-end items-end gap-2">
                            <span class="text-5xl font-black text-white leading-none">
                                {{ number_format($qualityAtArrival, 0) }}
                            </span>
                            <span class="text-sm font-black text-slate-500 mb-1">/100</span>
                        </div>
                        <span class="inline-flex mt-3 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest
                            {{ $qualityTone === 'emerald' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}
                            {{ $qualityTone === 'cyan' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : '' }}
                            {{ $qualityTone === 'amber' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '' }}
                            {{ $qualityTone === 'rose' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : '' }}
                        ">
                            {{ $qualityStatus }}
                        </span>
                    </div>
                @endif
            </div>

            @if($predictionAvailable)
                @if($expiryConstraintApplied)
                    <div class="mb-5 bg-amber-500/10 border border-amber-500/25 rounded-2xl p-5">
                        <p class="text-[10px] uppercase tracking-widest font-black text-amber-400">
                            Active Shelf-Life Constraint
                        </p>
                        <p class="text-sm font-black text-white mt-2">{{ $reconciliationStatus }}</p>
                        <p class="text-xs text-slate-400 mt-2 leading-relaxed">{{ $reconciliationMessage }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="bg-slate-900/60 p-5 rounded-2xl border border-slate-700">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Operational Arrival Quality</p>
                        <p class="mt-2 text-2xl font-black text-white">
                            {{ $qualityAtArrival !== null ? number_format($qualityAtArrival, 0) : '—' }}
                            <span class="text-xs text-slate-500">/100</span>
                        </p>
                    </div>

                    <div class="bg-slate-900/60 p-5 rounded-2xl border border-slate-700">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Operational Remaining Life at Arrival</p>
                        <p class="mt-2 text-2xl font-black text-white">
                            {{ $remainingShelfLife !== null ? number_format($remainingShelfLife, 2) : '—' }}
                            <span class="text-xs text-slate-500">days</span>
                        </p>
                        @if($recordedRemainingArrivalHours !== null)
                            <p class="text-[10px] uppercase font-bold text-slate-500 mt-2">
                                ≈ {{ number_format($recordedRemainingArrivalHours, 1) }} h recorded time left
                            </p>
                        @endif
                    </div>

                    <div class="bg-slate-900/60 p-5 rounded-2xl border border-slate-700">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Maximum Transit Window From Now</p>
                        <p class="mt-2 text-2xl font-black text-white">
                            {{ $safeTransitWindow !== null ? number_format($safeTransitWindow, 1) : '—' }}
                            <span class="text-xs text-slate-500">hours</span>
                        </p>
                        <p class="text-[10px] uppercase font-bold text-slate-500 mt-2">{{ $safeTransitStatus }}</p>
                    </div>

                    <div class="bg-slate-900/60 p-5 rounded-2xl border border-slate-700">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Transit Margin</p>
                        <p class="mt-2 text-2xl font-black {{ ($transitMarginHours ?? 0) < 0 ? 'text-rose-400' : 'text-cyan-400' }}">
                            {{ $transitMarginHours !== null ? number_format($transitMarginHours, 1) : '—' }}
                            <span class="text-xs opacity-70">hours</span>
                        </p>
                        <p class="text-[10px] uppercase font-bold text-slate-500 mt-2">
                            Planned transit: {{ $plannedTransitHours !== null ? number_format($plannedTransitHours, 1) : '—' }} h
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-4">
                    <div class="bg-slate-900/40 border border-slate-700 rounded-2xl p-5">
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Reference Quality</p>
                        <p class="text-xl font-black text-white mt-2">
                            {{ $referenceQuality !== null ? number_format($referenceQuality, 0) : '—' }}/100
                        </p>
                    </div>

                    <div class="bg-slate-900/40 border border-slate-700 rounded-2xl p-5">
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Recorded Freshness</p>
                        <p class="text-xl font-black text-white mt-2">
                            {{ $recordedFreshness !== null ? number_format($recordedFreshness, 0) : '—' }}/100
                        </p>
                    </div>

                    <div class="bg-slate-900/40 border border-slate-700 rounded-2xl p-5">
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Temperature</p>
                        <p class="text-lg font-black text-amber-400 mt-2">{{ $temperatureStatus }}</p>
                        <p class="text-xs text-slate-500 mt-2">
                            {{ str_replace('_', ' ', $qualityPrediction['temperature_basis'] ?? 'unknown') }}
                        </p>
                    </div>

                    <div class="bg-slate-900/40 border border-slate-700 rounded-2xl p-5">
                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Data Confidence</p>
                        <p class="text-xl font-black text-white mt-2">{{ number_format($dataConfidence, 0) }}%</p>
                        <p class="text-[10px] text-slate-500 mt-2">Input completeness, not model accuracy.</p>
                    </div>
                </div>

                <details class="mt-5 group">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 bg-slate-900/40 hover:bg-slate-900/60 border border-slate-700 rounded-xl px-5 py-4 transition-colors">
                        <span class="text-xs font-black text-slate-300 uppercase tracking-widest">Prediction Details</span>
                        <span class="text-cyan-400 text-sm group-open:rotate-45 transition-transform">+</span>
                    </summary>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 px-5 pt-5">
                        <div>
                            <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Baseline Reference Life</p>
                            <p class="text-sm font-bold text-slate-200 mt-1">
                                {{ isset($qualityPrediction['baseline_shelf_life_days']) ? number_format($qualityPrediction['baseline_shelf_life_days'], 1) . ' days' : 'Unavailable' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Reference Remaining at Arrival</p>
                            <p class="text-sm font-bold text-slate-200 mt-1">
                                {{ $referenceRemaining !== null ? number_format($referenceRemaining, 2) . ' days' : 'Unavailable' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Recorded Remaining Before Dispatch</p>
                            <p class="text-sm font-bold text-slate-200 mt-1">
                                {{ $recordedRemainingDepartureHours !== null ? number_format($recordedRemainingDepartureHours, 1) . ' hours' : 'Unavailable' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Recorded Remaining at Arrival</p>
                            <p class="text-sm font-bold text-slate-200 mt-1">
                                {{ $recordedRemainingArrival !== null ? number_format($recordedRemainingArrival, 2) . ' days' : 'Unavailable' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Harvest Age</p>
                            <p class="text-sm font-bold text-slate-200 mt-1">
                                {{ isset($qualityPrediction['harvest_age_days']) ? number_format($qualityPrediction['harvest_age_days'], 2) . ' days' : 'Unavailable' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Effective Transit Age</p>
                            <p class="text-sm font-bold text-slate-200 mt-1">
                                {{ isset($qualityPrediction['effective_transit_age_days']) ? number_format($qualityPrediction['effective_transit_age_days'], 2) . ' days' : 'Unavailable' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Departure Quality</p>
                            <p class="text-sm font-bold text-slate-200 mt-1">
                                {{ $qualityAtDeparture !== null ? number_format($qualityAtDeparture, 0) . '/100' : 'Unavailable' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">Transit Quality Loss</p>
                            <p class="text-sm font-bold text-slate-200 mt-1">
                                {{ $qualityLoss !== null ? number_format($qualityLoss, 0) . ' pts' : 'Unavailable' }}
                            </p>
                        </div>
                    </div>
                </details>
            @endif
        </div>
    </div>
@endif
