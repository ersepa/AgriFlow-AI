<x-app-layout>

    <div class="max-w-6xl mx-auto py-10 px-4 sm:px-6">

        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-cyan-400 transition-all hover:-translate-x-1 mb-8 font-bold text-xs uppercase tracking-widest">

            ← Back to Dashboard

        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-8">

                <div class="bg-slate-900 p-8 rounded-3xl shadow-xl border border-slate-800 relative overflow-hidden group">

                    <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl transition-all duration-700"></div>

                    <div class="relative z-10 flex justify-between items-start">

                        <div>

                            <h1 class="text-5xl font-black text-white capitalize tracking-wide drop-shadow-[0_2px_10px_rgba(255,255,255,0.1)]">

                                {{ $shipment->harvest->commodity }}

                            </h1>

                            <div class="mt-5 inline-block">

                                <span class="px-4 py-2 rounded-lg bg-indigo-500/10 text-indigo-300 border border-indigo-500/30 font-bold text-xs uppercase tracking-widest shadow-[0_0_15px_rgba(99,102,241,0.15)]">

                                    Shipment Detail

                                </span>

                            </div>

                        </div>

                    </div>

                    <div class="mt-10 bg-slate-800 p-6 rounded-2xl border border-slate-700/60 flex items-center justify-between gap-2">

                        <div class="text-left w-1/3">

                            <p class="text-[10px] uppercase font-bold text-cyan-400 tracking-widest mb-1">Origin</p>

                            <p class="font-black text-xl sm:text-2xl text-white capitalize tracking-wide break-words">{{ $shipment->origin }}</p>

                        </div>

                        <div class="flex-1 flex flex-col items-center justify-center relative py-4 min-w-[120px]">

                            <div class="w-full border-t-2 border-dashed border-slate-600 absolute top-1/2 -translate-y-1/2 z-0"></div>

                            <div class="relative bg-slate-800 border border-cyan-500 text-cyan-400 p-2 rounded-full shadow-[0_0_15px_rgba(6,182,212,0.4)] z-10">

                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />

                                </svg>

                            </div>

                            <p class="absolute top-full -mt-2 text-[10px] font-black text-slate-300 bg-slate-900 px-3 py-1 rounded-full border border-slate-700 uppercase tracking-widest whitespace-nowrap z-10">

                                {{ number_format($shipment->distance_km ?? 0, 0) }} KM

                            </p>

                        </div>

                        <div class="text-right w-1/3">

                            <p class="text-[10px] uppercase font-bold text-cyan-400 tracking-widest mb-1">Destination</p>

                            <p class="font-black text-xl sm:text-2xl text-white capitalize tracking-wide break-words">{{ $shipment->destination }}</p>

                        </div>

                    </div>

                </div>

                
                {{-- STEP 3: Freshness Prediction --}}
                @php
                    $qualityPrediction = $analysis['quality_prediction'] ?? [];
                    $arrivalQuality = $analysis['quality_at_arrival'] ?? null;
                    $departureQuality = $analysis['quality_at_departure'] ?? null;
                    $qualityStatus = $analysis['quality_status'] ?? 'Unavailable';
                    $remainingShelfLife = $analysis['predicted_remaining_shelf_life_days'] ?? null;
                    $safeTransitWindow = $analysis['safe_transit_window_hours'] ?? null;
                    $safeTransitStatus = $analysis['safe_transit_status'] ?? 'Unavailable';
                    $temperatureAssessment = $analysis['temperature_assessment'] ?? [];
                    $temperatureStatus = $temperatureAssessment['status'] ?? 'Not provided';
                    $temperatureC = $qualityPrediction['temperature_c'] ?? null;
                    $temperatureBasis = $qualityPrediction['temperature_basis'] ?? 'unknown';
                    $dataConfidence = $analysis['data_confidence'] ?? 0;
                    $commodityProfile = $analysis['commodity_profile'] ?? [];
                    $predictionAvailable = $qualityPrediction['prediction_available'] ?? false;
                    $expiryConstraintApplied = $qualityPrediction['expiry_constraint_applied'] ?? false;
                    $reconciliationStatus = $qualityPrediction['shelf_life_reconciliation_status'] ?? null;
                    $reconciliationMessage = $qualityPrediction['shelf_life_reconciliation_message'] ?? null;

                    $qualityTone = match (true) {
                        $arrivalQuality === null => 'slate',
                        $arrivalQuality >= 85 => 'emerald',
                        $arrivalQuality >= 70 => 'cyan',
                        $arrivalQuality >= 50 => 'amber',
                        default => 'rose',
                    };
                @endphp

                <div class="bg-slate-900 p-8 rounded-3xl shadow-xl border border-slate-800 relative overflow-hidden">
                    <div class="absolute -top-24 -right-20 w-56 h-56 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-5 border-b border-slate-800 pb-6">
                            <div>
                                <p class="text-[10px] uppercase font-black text-cyan-400 tracking-[0.24em]">
                                    Freshness Intelligence
                                </p>

                                <h2 class="text-xl font-black text-white mt-2">
                                    Predicted Arrival Quality
                                </h2>

                                <p class="text-sm text-slate-400 mt-2 max-w-xl leading-relaxed">
                                    Estimated from the commodity reference profile,
                                    harvest age, planned transit time, and available
                                    temperature conditions.
                                </p>
                            </div>

                            @if($predictionAvailable && $arrivalQuality !== null)
                                <div class="sm:text-right">
                                    <div class="flex sm:justify-end items-end gap-2">
                                        <span class="text-5xl font-black text-white leading-none">
                                            {{ number_format($arrivalQuality, 0) }}
                                        </span>

                                        <span class="text-sm font-black text-slate-500 mb-1">
                                            /100
                                        </span>
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
                            @else
                                <div class="sm:text-right">
                                    <span class="inline-flex px-3 py-2 rounded-lg bg-slate-800 text-slate-400 border border-slate-700 text-[10px] font-black uppercase tracking-widest">
                                        Prediction unavailable
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if($predictionAvailable)
                            @if($expiryConstraintApplied)
                                <div class="mt-6 bg-amber-500/10 border border-amber-500/25 rounded-2xl p-5">
                                    <p class="text-[10px] uppercase tracking-widest font-black text-amber-400">
                                        Shelf-Life Constraint
                                    </p>
                                    <p class="text-sm font-black text-white mt-2">
                                        {{ $reconciliationStatus }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                                        {{ $reconciliationMessage }}
                                    </p>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-6">
                                <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-5">
                                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                        Operational Remaining Shelf Life
                                    </p>

                                    <p class="text-2xl font-black text-white mt-3">
                                        {{ $remainingShelfLife !== null ? number_format($remainingShelfLife, 1) : '—' }}
                                        <span class="text-xs text-slate-500">
                                            days
                                        </span>
                                    </p>
                                </div>

                                <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-5">
                                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                        Safe Transit Window
                                    </p>

                                    <p class="text-2xl font-black text-white mt-3">
                                        {{ $safeTransitWindow !== null ? number_format($safeTransitWindow, 1) : '—' }}
                                        <span class="text-xs text-slate-500">
                                            hours
                                        </span>
                                    </p>

                                    <p class="text-[10px] uppercase font-bold text-slate-500 mt-2">
                                        {{ $safeTransitStatus }}
                                    </p>
                                </div>

                                <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-5">
                                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                        Temperature
                                    </p>

                                    <p class="text-lg font-black mt-3
                                        {{ $temperatureStatus === 'Optimal' ? 'text-emerald-400' : '' }}
                                        {{ $temperatureStatus === 'Chilling risk' ? 'text-rose-400' : '' }}
                                        {{ !in_array($temperatureStatus, ['Optimal', 'Chilling risk']) ? 'text-amber-400' : '' }}
                                    ">
                                        {{ $temperatureStatus }}
                                    </p>

                                    <p class="text-xs text-slate-500 mt-2">
                                        @if($temperatureC !== null)
                                            {{ number_format($temperatureC, 1) }}°C ·
                                        @endif
                                        {{ str_replace('_', ' ', $temperatureBasis) }}
                                    </p>
                                </div>

                                <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-5">
                                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                        Data Confidence
                                    </p>

                                    <p class="text-2xl font-black text-white mt-3">
                                        {{ number_format($dataConfidence, 0) }}%
                                    </p>

                                    <div class="w-full h-1.5 bg-slate-700 rounded-full mt-3 overflow-hidden">
                                        <div
                                            class="h-full bg-cyan-400 rounded-full"
                                            style="width: {{ max(0, min(100, $dataConfidence)) }}%"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-slate-950/40 border border-slate-800 rounded-2xl p-5">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                                Departure Quality
                                            </p>

                                            <p class="text-xl font-black text-white mt-2">
                                                {{ $departureQuality !== null ? number_format($departureQuality, 0) : '—' }}/100
                                            </p>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                                Transit Quality Loss
                                            </p>

                                            <p class="text-xl font-black text-rose-400 mt-2">
                                                -{{ number_format($analysis['quality_loss_during_transit'] ?? 0, 0) }}
                                                <span class="text-xs">pts</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-slate-950/40 border border-slate-800 rounded-2xl p-5">
                                    <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                        Commodity Reference
                                    </p>

                                    <p class="text-sm font-black text-white mt-2">
                                        {{ $commodityProfile['local_name'] ?? $commodityProfile['name'] ?? $shipment->harvest->commodity }}
                                    </p>

                                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                                        {{ $commodityProfile['storage_recommendation'] ?? 'Validated storage profile unavailable.' }}
                                    </p>
                                </div>
                            </div>

                            <details class="mt-5 group">
                                <summary class="cursor-pointer list-none flex items-center justify-between gap-4 bg-slate-800/50 hover:bg-slate-800 border border-slate-700 rounded-xl px-5 py-4 transition-colors">
                                    <span class="text-xs font-black text-slate-300 uppercase tracking-widest">
                                        Prediction Details
                                    </span>

                                    <span class="text-cyan-400 text-sm group-open:rotate-45 transition-transform">
                                        +
                                    </span>
                                </summary>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-5 px-5 pt-5">
                                    <div>
                                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                            Baseline Shelf Life
                                        </p>

                                        <p class="text-sm font-bold text-slate-200 mt-1">
                                            {{ isset($qualityPrediction['baseline_shelf_life_days'])
                                                ? number_format($qualityPrediction['baseline_shelf_life_days'], 1) . ' days'
                                                : 'Unavailable'
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                            Harvest Age
                                        </p>

                                        <p class="text-sm font-bold text-slate-200 mt-1">
                                            {{ isset($qualityPrediction['harvest_age_days'])
                                                ? number_format($qualityPrediction['harvest_age_days'], 1) . ' days'
                                                : 'Unavailable'
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                            Effective Transit Age
                                        </p>

                                        <p class="text-sm font-bold text-slate-200 mt-1">
                                            {{ isset($qualityPrediction['effective_transit_age_days'])
                                                ? number_format($qualityPrediction['effective_transit_age_days'], 2) . ' days'
                                                : 'Unavailable'
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                            Temperature Factor
                                        </p>

                                        <p class="text-sm font-bold text-slate-200 mt-1">
                                            {{ isset($qualityPrediction['temperature_deterioration_factor'])
                                                ? number_format($qualityPrediction['temperature_deterioration_factor'], 2) . '×'
                                                : 'Unavailable'
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                            Reference Remaining Life
                                        </p>
                                        <p class="text-sm font-bold text-slate-200 mt-1">
                                            {{ isset($qualityPrediction['reference_remaining_shelf_life_at_arrival_days'])
                                                ? number_format($qualityPrediction['reference_remaining_shelf_life_at_arrival_days'], 2) . ' days'
                                                : 'Unavailable'
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                            Recorded Expiry Remaining
                                        </p>
                                        <p class="text-sm font-bold text-slate-200 mt-1">
                                            {{ isset($qualityPrediction['recorded_remaining_at_arrival_days'])
                                                ? number_format($qualityPrediction['recorded_remaining_at_arrival_days'], 2) . ' days'
                                                : 'Not recorded'
                                            }}
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                            Reference Source
                                        </p>

                                        @if(!empty($commodityProfile['source_url']))
                                            <a
                                                href="{{ $commodityProfile['source_url'] }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex text-sm font-bold text-cyan-400 hover:text-cyan-300 mt-1"
                                            >
                                                {{ $commodityProfile['source_name'] ?? 'View source' }}
                                            </a>
                                        @else
                                            <p class="text-sm font-bold text-slate-400 mt-1">
                                                Unavailable
                                            </p>
                                        @endif
                                    </div>

                                    <div>
                                        <p class="text-[10px] uppercase font-black text-slate-500 tracking-widest">
                                            Prediction Basis
                                        </p>

                                        <p class="text-sm font-bold text-slate-200 mt-1">
                                            Commodity profile + harvest age + transit conditions
                                        </p>
                                    </div>
                                </div>
                            </details>
                        @else
                            <div class="mt-6 bg-amber-500/5 border border-amber-500/20 rounded-2xl p-5">
                                <p class="text-sm font-bold text-amber-300">
                                    Freshness prediction is unavailable for this shipment.
                                </p>

                                <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                                    A validated commodity profile and usable harvest data are required before AgriFlow calculates arrival quality.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- STEP 4.3: Current Operational Risk + Intervention Plan --}}
                @include('shared.step43-risk-summary', ['step43Analysis' => $analysis])

                @include('shared.step43-intervention-plan', ['step43Analysis' => $analysis])

<div class="bg-slate-900 p-8 rounded-3xl shadow-xl border border-slate-800">

                    <div class="flex items-center gap-4 mb-8 border-b border-slate-800 pb-5">

                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-[0_0_15px_rgba(79,70,229,0.4)]">

                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>

                            </svg>

                        </div>

                        <h3 class="text-base font-black text-white uppercase tracking-widest">AI Safety Logs</h3>

                    </div>

                    <div class="space-y-4">

@forelse($shipment->aiAnalyses as $analysis)

<div class="bg-slate-800/90 p-6 rounded-2xl border border-slate-700 flex gap-5 hover:border-indigo-500 transition-all duration-300">

    <div class="flex-shrink-0 mt-1.5">

        <div class="w-3 h-3 rounded-full shadow-[0_0_10px_currentColor] 

            {{ $analysis->risk_level == 'High' ? 'bg-rose-500 text-rose-500' : ($analysis->risk_level == 'Medium' ? 'bg-amber-400 text-amber-400' : 'bg-emerald-400 text-emerald-400') }}">

        </div>

    </div>

    <div class="flex-1 space-y-4">

        <div class="flex justify-between items-center border-b border-slate-700/50 pb-2">

            <span class="font-black text-sm uppercase tracking-wider {{ $analysis->risk_level == 'High' ? 'text-rose-400' : ($analysis->risk_level == 'Medium' ? 'text-amber-400' : 'text-emerald-400') }}">

                {{ $analysis->risk_level }} Risk

            </span>

            <span class="text-xs text-slate-400 font-bold uppercase tracking-widest">

                {{ $analysis->created_at->diffForHumans() }}

            </span>

        </div>

        @php

            // Pisahkan antara bagian Rekomendasi dan Eksplanasi

            $rawText = $analysis->recommendations;

            $parts = explode('Explanation:', $rawText);

            // Ambil rekomendasi lalu pecah berdasarkan tanda strip (-) jadi array

            $recommendationsList = isset($parts[0]) ? array_filter(array_map('trim', explode('-', str_replace('Recommendations:', '', $parts[0])))) : [];

            $explanation = isset($parts[1]) ? trim($parts[1]) : '';

        @endphp

        @if(!empty($recommendationsList))

        <div>

            <h4 class="text-xs font-bold uppercase text-indigo-400 tracking-wider mb-2">💡 Key Recommendations:</h4>

            <ul class="space-y-2">

                @foreach($recommendationsList as $item)

                    <li class="text-slate-200 text-sm leading-relaxed flex items-start gap-2">

                        <span class="text-indigo-500 mt-1">•</span>

                        <span>{{ $item }}</span>

                    </li>

                @endforeach

            </ul>

        </div>

        @endif

        @if($explanation)

        <div class="pt-2 border-t border-slate-700/30">

            <h4 class="text-xs font-bold uppercase text-cyan-400 tracking-wider mb-1">📝 Analysis Explanation:</h4>

            <p class="text-slate-400 text-sm leading-relaxed italic">

                {{ $explanation }}

            </p>

        </div>

        @endif

    </div>

</div>

@empty

<div class="text-center py-10 bg-slate-800/40 rounded-2xl border border-dashed border-slate-700">

    <p class="text-slate-400 font-bold text-sm tracking-widest uppercase">No Data Available</p>

</div>

@endforelse

                    </div>

                </div>

            </div>

            <div class="space-y-8">

                <div class="bg-slate-900 p-8 rounded-3xl shadow-xl border border-slate-800 text-center relative overflow-hidden">

                    <p class="text-xs uppercase font-bold text-indigo-400 tracking-widest">Sustainability Score</p>

                    <p class="text-7xl font-black text-white mt-4 drop-shadow-md">

                        {{ number_format($shipment->aiAnalyses->avg('sustainability_score') ?? 0, 0) }}

                    </p>

                    <div class="w-full bg-slate-800 h-2 rounded-full mt-8 overflow-hidden border border-slate-700">

                        <div class="bg-gradient-to-r from-indigo-500 to-cyan-400 h-full rounded-full transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(6,182,212,0.5)]" 

                             style="width: {{ $shipment->aiAnalyses->avg('sustainability_score') ?? 0 }}%"></div>

                    </div>

                </div>

                <div class="bg-slate-900 text-white p-8 rounded-3xl shadow-xl border border-slate-800">

                    <div class="flex items-center justify-between border-b border-slate-800 pb-5 mb-6">

                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Technical Data</h3>

                        <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shadow-[0_0_8px_rgba(52,211,153,0.8)]"></div>

                    </div>

                    <div class="space-y-5">

                        <div class="flex justify-between items-center group">

                            <span class="text-slate-400 text-sm">Status</span>

                            <span class="font-black text-emerald-400 uppercase tracking-widest text-[11px] bg-emerald-500/10 px-3 py-1 rounded-md border border-emerald-500/20">

                                {{ $shipment->status }}

                            </span>

                        </div>

                        <div class="flex justify-between items-center">

                            <span class="text-slate-400 text-sm">Weight</span>

                            <span class="font-bold text-slate-200">{{ number_format($shipment->harvest->weight, 0) }} <span class="text-slate-500 text-xs">KG</span></span>

                        </div>

                        <div class="flex justify-between items-center">

                            <span class="text-slate-400 text-sm">Distance</span>

                            <span class="font-bold text-cyan-400">{{ number_format($shipment->distance_km ?? 0, 0) }} <span class="text-cyan-700 text-xs">KM</span></span>

                        </div>

                        <div class="flex justify-between items-center pt-4 border-t border-slate-800">

                            <span class="text-slate-400 text-sm">Harvest Date</span>

                            <span class="font-medium text-slate-300 text-sm tracking-wide">{{ $shipment->harvest->created_at->format('d M Y') }}</span>

                        </div>

                        <div class="flex justify-between items-center">

                            <span class="text-slate-400 text-sm">Expiry Date</span>

                            <span class="font-bold text-rose-400 text-sm tracking-wide">

                                {{ $shipment->harvest->expiry_date ? \Carbon\Carbon::parse($shipment->harvest->expiry_date)->format('d M Y') : 'Not Set' }}

                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>