<x-app-layout>
    {{-- Design System Tokens & APICTA Competition Micro-Interactions --}}
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .animate-card { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        .agri-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.04), 0 4px 12px -2px rgba(15, 23, 42, 0.02);
            border-radius: 1.5rem;
        }

        .agri-dark-card {
            background: #0f172a;
            border: 1px solid rgba(51, 65, 85, 0.7);
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.25);
            border-radius: 1.5rem;
        }

        .btn-animate {
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-animate:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.2);
        }
        .btn-animate:active {
            transform: translateY(0) scale(0.98);
        }

        /* Fix Leaflet overlay behind modal & smooth borders */
        .leaflet-container {
            z-index: 1 !important;
            font-family: inherit;
        }
        .leaflet-pane, .leaflet-control {
            z-index: 1 !important;
        }
        #aiModal {
            z-index: 9999 !important;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 1rem;
            padding: 4px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15);
        }
        .leaflet-popup-tip { display: none; }
        .leaflet-control-zoom { border: none !important; }
        .leaflet-control-zoom a {
            border-radius: 0.75rem !important;
            margin-bottom: 4px;
            color: #0f172a !important;
            border: 1px solid #e2e8f0 !important;
        }

        .route-marker {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25);
            border: 3px solid #ffffff;
        }
    </style>

    <div class="py-8 px-4 sm:px-6 lg:px-8 animate-card w-full"
         x-data="{ loaded: false }" 
         x-init="setTimeout(() => loaded = true, 300)">

        <div class="max-w-7xl mx-auto space-y-8">

{{-- HERO SECTION --}}
<section class="agri-card p-8 md:p-10 relative overflow-hidden bg-white shadow-sm border border-slate-200/80">
    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-teal-400 via-indigo-500 to-purple-500"></div>
    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="px-3.5 py-1 rounded-full bg-indigo-100/80 border border-indigo-200 text-indigo-800 text-[10px] font-black tracking-widest uppercase">
                    DEVELOPERDAY 2026 • ROAD TO APICTA
                </span>
            </div>
            <h1 class="text-3xl md:text-5xl font-black tracking-tight leading-none text-slate-900">
                Freshness-Aware <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-teal-500">Route Optimizer</span>
            </h1>
            <p class="mt-3 text-slate-600 text-sm md:text-base max-w-xl font-medium leading-relaxed">
                Rank planned routes by projected arrival quality, operational risk, transit margin, duration, and carbon exposure. AgriFlow does not invent route alternatives when the routing provider cannot supply them.
            </p>
        </div>
    </div>
</section>

            {{-- SUMMARY CARDS --}}
            <section class="grid grid-cols-2 md:grid-cols-4 gap-5">
                @php
                    $statConfigs = [
                        ['Shipments', $totalShipments, null, 'bg-indigo-50/60 border-indigo-200/80 text-indigo-900'],
                        ['Freshness Safe', $freshnessSafeCount, null, 'bg-emerald-50/60 border-emerald-200/80 text-emerald-900'],
                        ['High/Critical Risk', $highCount + $criticalCount, null, 'bg-rose-50/60 border-rose-200/80 text-rose-900'],
                        ['Avg Route Score', $averageRouteScore . '/100', null, 'bg-cyan-50/60 border-cyan-200/80 text-cyan-900'],
                    ];
                @endphp

                @foreach($statConfigs as $stat)
                <div class="agri-card p-5 sm:p-6 {{ $stat[3] }} transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-2xl">{{ $stat[2] }}</span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Telemetry</span>
                    </div>
                    <p class="text-[11px] font-bold uppercase tracking-widest text-slate-500">{{ $stat[0] }}</p>
                    <h2 class="text-2xl sm:text-3xl font-black mt-0.5 tracking-tight text-slate-900">{{ $stat[1] }}</h2>
                </div>
                @endforeach
            </section>

            {{-- MAP SECTION --}}
            <section class="agri-card p-6 sm:p-8 relative overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🗺️</span>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                                Live Shipment Route
                            </h2>
                        </div>
                        <p class="text-slate-500 font-medium text-xs sm:text-sm mt-0.5">
                            AI Autonomous Navigation & Interactive GIS Logistics Telemetry
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="px-3.5 py-1.5 rounded-full bg-slate-100 border border-slate-200/80 text-slate-700 text-xs font-bold shadow-sm">
                            🚚 {{ $totalShipments }} Shipments Active
                        </span>

                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-100/80 border border-emerald-200 text-emerald-800 text-xs font-bold shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>GIS LIVE</span>
                        </span>
                    </div>
                </div>

                {{-- MAP CONTAINER --}}
                <div class="rounded-2xl overflow-hidden border border-slate-200/90 shadow-inner">
                    <div id="map" class="h-[480px] w-full bg-slate-100"></div>
                </div>
            </section>

            {{-- LIST SECTION --}}
            <section class="space-y-6">
                @foreach($results as $index => $result)
                <div x-data="{ show: false }"
                     x-init="setTimeout(() => show = true, {{ $index * 150 }})"
                     class="shipment-card agri-card p-6 sm:p-8 relative overflow-hidden transition-all duration-300 hover:shadow-xl hover:border-indigo-300"
                     data-shipment-id="{{ $result['shipment']->id }}"
                     data-origin-lat="{{ $result['origin_lat'] }}"
                     data-origin-lng="{{ $result['origin_lng'] }}"
                     data-dest-lat="{{ $result['destination_lat'] }}"
                     data-dest-lng="{{ $result['destination_lng'] }}">
{{-- Accent Color Header Bar --}}
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-500 via-teal-400 to-emerald-500"></div>

                    {{-- Card Header --}}
                    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-6">
                        <div>
                            <div class="flex items-center gap-2.5 mb-1.5">
                                <span class="text-[10px] font-black text-indigo-700 bg-indigo-100/80 border border-indigo-200 px-3 py-0.5 rounded-full uppercase tracking-wider">
                                    Priority #{{ $index + 1 }}
                                </span>
                                <span class="text-xs text-slate-400 font-bold">•</span>
                                <span class="px-3 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border shadow-sm
                                    @if(str_contains(strtolower($result['priority_level']), 'high')) bg-rose-100 text-rose-800 border-rose-200
                                    @elseif(str_contains(strtolower($result['priority_level']), 'medium')) bg-amber-100 text-amber-800 border-amber-200
                                    @else bg-emerald-100 text-emerald-800 border-emerald-200 @endif">
                                    {{ $result['priority_level'] }}
                                </span>
                            </div>
                            <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ ucfirst($result['commodity']) }}</h2>
                            <p class="text-slate-500 font-bold text-xs sm:text-sm mt-0.5 flex items-center gap-2">
                                <span class="text-indigo-600">📍 {{ $result['origin'] }}</span>
                                <span class="text-slate-300">➔</span>
                                <span class="text-emerald-600">🏁 {{ $result['destination'] }}</span>
                            </p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-3">
                            <button onclick="updateMap(this.closest('.shipment-card'), this)"
                                    class="route-btn btn-animate px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-wider shadow-md shadow-indigo-600/20">
                                <span class="route-text flex items-center gap-1.5">
                                    🚚 <span>Route</span>
                                </span>
                            </button>

                            <button onclick="showFreshnessRoutes({{ $result['shipment']->id }})"
                                    class="btn-animate px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-black uppercase tracking-wider shadow-md shadow-teal-600/20 flex items-center gap-1.5">
                                <span>Freshness Routes</span>
                            </button>

                            <button onclick="showAIExplain({{ $result['shipment']->id }})"
                                    class="btn-animate px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-black uppercase tracking-wider shadow-md shadow-purple-600/20 flex items-center gap-1.5">
                                <span>Explain</span>
                            </button>
                        </div>
                    </div>

                    {{-- Step 5 Freshness-Aware Route Metrics --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 bg-slate-50/70 p-5 rounded-2xl border border-slate-200/80">
                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider">
                                Freshness Route Score
                            </p>
                            <p class="text-2xl font-black text-indigo-700 mt-1">
                                {{ $result['freshness_route_score'] }}
                                <span class="text-xs text-slate-400">/100</span>
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider">
                                Arrival Quality
                            </p>
                            <p class="text-2xl font-black text-slate-900 mt-1">
                                {{ $result['quality_at_arrival'] ?? '—' }}
                                <span class="text-xs text-slate-400">/100</span>
                            </p>
                            <p class="text-[10px] text-slate-500 mt-1">
                                {{ $result['quality_status'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider">
                                Operational Risk
                            </p>
                            <p class="text-2xl font-black text-rose-600 mt-1">
                                {{ $result['risk_score'] }}
                                <span class="text-xs text-slate-400">/100</span>
                            </p>
                            <p class="text-[10px] text-slate-500 mt-1 uppercase font-bold">
                                {{ $result['risk_severity'] }}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] font-black text-slate-500 uppercase tracking-wider">
                                Freshness Feasibility
                            </p>
                            <p class="text-lg font-black mt-2
                                {{ $result['freshness_feasibility'] === 'Safe' ? 'text-emerald-600' : '' }}
                                {{ $result['freshness_feasibility'] === 'Tight' ? 'text-amber-600' : '' }}
                                {{ $result['freshness_feasibility'] === 'Breach' ? 'text-rose-600' : '' }}
                            ">
                                {{ $result['freshness_feasibility'] }}
                            </p>
                            <p class="text-[10px] text-slate-500 mt-1">
                                Delay tolerance:
                                {{ $result['delay_tolerance_hours'] !== null ? number_format($result['delay_tolerance_hours'], 1) . ' h' : 'Unavailable' }}
                            </p>
                        </div>
                    </div>

                    {{-- Step 5 Route Decision Context --}}
                    <div class="grid lg:grid-cols-2 gap-5 mt-6">
                        <div class="rounded-2xl bg-teal-50/70 border border-teal-200/80 p-5 text-slate-800">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-black text-xs uppercase tracking-wider text-teal-900">
                                    Current Route Assessment
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-md bg-teal-100 text-teal-800 text-[10px] font-extrabold uppercase">
                                    Step 5
                                </span>
                            </div>

                            <p class="text-xs text-slate-700 leading-relaxed font-medium">
                                {{ $result['route_assessment']['recommendation_reason'] }}
                            </p>

                            <div class="grid grid-cols-2 gap-3 mt-4">
                                <div class="bg-white/70 border border-teal-100 rounded-xl p-3">
                                    <p class="text-[9px] uppercase font-black text-slate-400">
                                        Transit Margin
                                    </p>
                                    <p class="text-sm font-black text-slate-900 mt-1">
                                        {{ $result['transit_margin_hours'] !== null ? number_format($result['transit_margin_hours'], 1) . ' h' : 'Unavailable' }}
                                    </p>
                                </div>

                                <div class="bg-white/70 border border-teal-100 rounded-xl p-3">
                                    <p class="text-[9px] uppercase font-black text-slate-400">
                                        Safe Transit
                                    </p>
                                    <p class="text-sm font-black text-slate-900 mt-1">
                                        {{ $result['safe_transit_status'] }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-indigo-50/70 border border-indigo-200/80 p-5 text-slate-800">
                            <h3 class="font-black text-xs uppercase tracking-wider text-indigo-900 mb-3">
                                Freshness-Aware Optimization
                            </h3>

                            <p class="text-xs text-slate-600 leading-relaxed font-medium">
                                Use <strong class="text-indigo-900">Freshness Routes</strong>
                                to compare real route candidates when the routing provider supports them.
                                Routes are ranked by freshness preservation, risk protection, transit margin,
                                duration, and carbon exposure.
                            </p>

                            <p class="text-[10px] text-slate-500 mt-4 leading-relaxed">
                                AgriFlow does not claim live traffic, does not fabricate route alternatives, and will explicitly report when no freshness-safe route exists.
                            </p>
                        </div>
                    </div>

                </div>
                @endforeach

                {{-- PAGINATION CONTAINER --}}
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-slate-200/80">
                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider">
                        Page <span class="text-slate-900 font-black">{{ $results->currentPage() }}</span> of <span class="text-slate-900 font-black">{{ $results->lastPage() }}</span>
                        • Showing <span class="text-indigo-600 font-black">{{ $results->firstItem() }}</span>-<span>{{ $results->lastItem() }}</span> of <span class="text-indigo-600 font-black">{{ $results->total() }}</span> Shipments
                    </p>

                    <div class="custom-pagination">
                        {{ $results->links() }}
                    </div>
                </div>
            </section>

        </div>
    </div>

    {{-- MODAL AI EXPLAINABILITY (Redesigned Enterprise Dark Card Modal) --}}
    <div id="aiModal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-md z-[9999] flex items-center justify-center p-4">
        <div class="agri-dark-card max-w-xl w-full p-6 sm:p-8 text-white relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-purple-500 via-indigo-500 to-teal-400"></div>

            <div class="flex justify-between items-center mb-6 border-b border-slate-800 pb-4">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl">🧠</span>
                    <h2 class="text-xl font-black text-white tracking-tight">Decision Explanation</h2>
                </div>
                <button onclick="closeAIExplain()" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center font-bold text-sm transition-colors">
                    ✕
                </button>
            </div>

            {{-- Loading State --}}
            <div id="aiLoading" class="py-8 text-center">
                <div class="flex justify-center mb-4">
                    <div class="w-10 h-10 border-4 border-indigo-900 border-t-indigo-400 rounded-full animate-spin"></div>
                </div>
                <p class="text-white font-bold text-sm">Evaluating shipment telemetry...</p>
                <p class="text-slate-400 text-xs mt-1">Explaining deterministic risk, freshness, and route context.</p>
            </div>

            {{-- Result View --}}
            <div id="aiResult" class="hidden space-y-5">
                <div class="bg-slate-800/60 p-4 rounded-2xl border border-slate-700/80">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">AI Operational Recommendation</p>
                    <p id="aiRecommendation" class="font-bold text-sm text-white mt-1 leading-relaxed">-</p>
                </div>

                <div class="bg-slate-800/60 p-4 rounded-2xl border border-slate-700/80">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Decision Factors</p>
                    <p id="aiReason" class="text-xs text-slate-300 mt-1 leading-relaxed">-</p>
                </div>

                <div class="bg-slate-800/60 p-4 rounded-2xl border border-slate-700/80">
                    <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Explanation Summary</p>
                    <p id="aiConclusion" class="text-xs text-slate-300 mt-1 leading-relaxed">-</p>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 5 FRESHNESS ROUTE COMPARISON MODAL --}}
    <div id="freshnessRouteModal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-md z-[10000] flex items-center justify-center p-4">
        <div class="agri-dark-card max-w-5xl w-full max-h-[88vh] overflow-y-auto p-6 sm:p-8 text-white relative">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-teal-400 via-cyan-400 to-indigo-500"></div>

            <div class="flex justify-between items-start gap-4 mb-6 border-b border-slate-800 pb-5">
                <div>
                    <p class="text-[10px] uppercase font-black tracking-[0.24em] text-cyan-400">
                        Freshness-Aware Routing · Step 5.1
                    </p>
                    <h2 id="freshnessDecisionHeading" class="text-2xl font-black text-white mt-1">
                        Route Candidate Comparison
                    </h2>
                    <p class="text-xs text-slate-400 mt-2 max-w-2xl">
                        Deterministic ranking based on projected arrival quality, operational risk,
                        transit margin, planned duration, and carbon exposure.
                    </p>
                </div>

                <button onclick="closeFreshnessRoutes()"
                        class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center font-bold">
                    ✕
                </button>
            </div>

            <div id="freshnessRouteLoading" class="py-14 text-center">
                <div class="w-10 h-10 border-4 border-cyan-900 border-t-cyan-400 rounded-full animate-spin mx-auto"></div>
                <p class="text-sm font-bold text-white mt-4">Evaluating route candidates...</p>
                <p class="text-xs text-slate-400 mt-1">
                    No synthetic alternative will be shown if the provider cannot return one.
                </p>
            </div>

            <div id="freshnessRouteResult" class="hidden">
                <div class="bg-slate-800/60 border border-slate-700 rounded-2xl p-5 mb-6">
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <p id="freshnessRecommendedLabelCaption" class="text-[9px] uppercase font-black text-slate-500 tracking-widest">
                                Recommended Route
                            </p>
                            <p id="freshnessRecommendedLabel" class="text-xl font-black text-white mt-1">-</p>
                        </div>
                        <div>
                            <p class="text-[9px] uppercase font-black text-slate-500 tracking-widest">
                                Route Score
                            </p>
                            <p id="freshnessRecommendedScore" class="text-xl font-black text-cyan-400 mt-1">-</p>
                        </div>
                        <div>
                            <p class="text-[9px] uppercase font-black text-slate-500 tracking-widest">
                                Feasibility
                            </p>
                            <p id="freshnessRecommendedFeasibility" class="text-xl font-black text-emerald-400 mt-1">-</p>
                        </div>
                    </div>

                    <p id="freshnessRouteReason" class="text-xs text-slate-300 leading-relaxed mt-4 pt-4 border-t border-slate-700"></p>

                    <div id="freshnessRequiredAction"
                         class="hidden mt-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-xs font-bold text-rose-200 leading-relaxed">
                    </div>
                </div>

                <div id="freshnessRouteNotice" class="hidden mb-6 rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4 text-xs text-amber-200 leading-relaxed"></div>

                <div id="freshnessRouteCandidates" class="grid grid-cols-1 lg:grid-cols-3 gap-4"></div>

                <div class="mt-6 rounded-2xl bg-slate-950/40 border border-slate-800 p-5">
                    <p class="text-[9px] uppercase font-black text-slate-500 tracking-widest">
                        Ranking Weights
                    </p>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mt-3 text-xs">
                        <div>Freshness <strong class="text-white">40%</strong></div>
                        <div>Risk Protection <strong class="text-white">25%</strong></div>
                        <div>Transit Margin <strong class="text-white">15%</strong></div>
                        <div>Duration <strong class="text-white">10%</strong></div>
                        <div>Carbon <strong class="text-white">10%</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>

{{-- LEAFLET GIS MAP ASSETS & SCRIPTS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map;
let markerOrigin;
let markerDestination;
let truck;
let routeLine;
let animationId = null;

document.addEventListener("DOMContentLoaded", () => {

    map = L.map('map', {
        zoomControl: false,
        attributionControl: false
    }).setView([-6.2, 106.8], 10);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        maxZoom: 19
    }).addTo(map);

    L.control.zoom({
        position: 'bottomright'
    }).addTo(map);

    markerOrigin = L.marker([0,0], {
        icon: createMarker("🌱", "#10b981")
    }).addTo(map);

    markerDestination = L.marker([0,0], {
        icon: createMarker("🏭", "#ef4444")
    }).addTo(map);

    truck = L.marker([0,0], {
        icon: createMarker("🚚", "#6366f1")
    }).addTo(map);

    routeLine = L.polyline([], {
        color: '#6366f1',
        weight: 3,
        opacity: 0.9,
        lineCap: 'round',
        lineJoin: 'round',
        dashArray: '12 8'
    }).addTo(map);

    let dashOffset = 0;
    setInterval(() => {
        dashOffset--;
        routeLine.setStyle({
            dashOffset: dashOffset
        });
    }, 40);

    const firstCard = document.querySelector('.shipment-card');
    if(firstCard){
        updateMap(firstCard);
    }
});

async function updateMap(element, button = null){
    try {
        if(button){
            setRouteLoading(button, true);
        }

        const oLat = parseFloat(element.getAttribute('data-origin-lat'));
        const oLng = parseFloat(element.getAttribute('data-origin-lng'));
        const dLat = parseFloat(element.getAttribute('data-dest-lat'));
        const dLng = parseFloat(element.getAttribute('data-dest-lng'));

        const shipmentId = element.getAttribute('data-shipment-id');

        const newRoute = await getRoadRoute(
            shipmentId
        );

        markerOrigin.setLatLng([oLat, oLng]);
        markerDestination.setLatLng([dLat, dLng]);
        routeLine.setLatLngs(newRoute);

        map.fitBounds(routeLine.getBounds(), {
            padding: [50, 50]
        });

        resetTruck(newRoute);

    } catch(error){
        console.error(error);
        alert("Failed to load route telemetry");
    } finally {
        if(button){
            setRouteLoading(button, false);
        }
    }
}

function resetTruck(route) {
    if (animationId) {
        cancelAnimationFrame(animationId);
    }

    if (!route || route.length < 2) return;

    let segment = 0;
    let progress = 0;
    const speed = 0.005;

    function animate() {
        if (segment >= route.length - 1) {
            segment = 0;
        }

        const start = route[segment];
        const end = route[segment + 1];

        progress += speed;

        if (progress >= 1) {
            progress = 0;
            segment++;
        }

        if (segment < route.length - 1) {
            const lat = start[0] + (end[0] - start[0]) * progress;
            const lng = start[1] + (end[1] - start[1]) * progress;
            truck.setLatLng([lat, lng]);
            animationId = requestAnimationFrame(animate);
        }
    }

    truck.setLatLng(route[0]);
    animationId = requestAnimationFrame(animate);
}

function createMarker(icon, color) {
    return L.divIcon({ 
        className: "", 
        html: `<div class="route-marker" style="background:${color}">${icon}</div>` 
    });
}

async function getRoadRoute(shipmentId){
    const response = await fetch(
        `/ai-optimizer/route/${shipmentId}`,
        {
            headers: {
                "Accept": "application/json"
            }
        }
    );

    if(!response.ok){
        throw new Error("Route provider unavailable");
    }

    const data = await response.json();
    const coordinates = data.geometry ?? [];

    return coordinates.map(
        c => [c[1], c[0]]
    );
}

async function showAIExplain(id) {
    const modal = document.getElementById('aiModal');
    const loading = document.getElementById('aiLoading');
    const result = document.getElementById('aiResult');

    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    result.classList.add('hidden');

    try {
        const response = await fetch(`/ai-optimizer/explain/${id}`);
        const data = await response.json();

        document.getElementById('aiRecommendation').innerText = data.recommendation ?? '-';
        document.getElementById('aiReason').innerText = data.decision_reason ?? '-';
        document.getElementById('aiConclusion').innerText = data.conclusion ?? '-';

        loading.classList.add('hidden');
        result.classList.remove('hidden');
    } catch (e) {
        console.error(e);
        loading.classList.add('hidden');
    }
}

function closeAIExplain() {
    document.getElementById('aiModal').classList.add('hidden');
}

document.getElementById('aiModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

function setRouteLoading(button, loading){
    const text = button.querySelector('.route-text');

    if(loading){
        text.innerHTML = `
            <span class="flex items-center gap-2">
                <span class="w-3 h-3 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                <span>Loading...</span>
            </span>
        `;
        button.disabled = true;
        button.classList.add('opacity-70');
    } else {
        text.innerHTML = "🚚 Route";
        button.disabled = false;
        button.classList.remove('opacity-70');
    }
}

let latestFreshnessRouteCandidates = [];

async function showFreshnessRoutes(id) {
    const modal = document.getElementById('freshnessRouteModal');
    const loading = document.getElementById('freshnessRouteLoading');
    const result = document.getElementById('freshnessRouteResult');
    const notice = document.getElementById('freshnessRouteNotice');
    const container = document.getElementById('freshnessRouteCandidates');

    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    result.classList.add('hidden');
    notice.classList.add('hidden');
    container.innerHTML = '';

    try {
        const response = await fetch(
            `/ai-optimizer/freshness-routes/${id}`,
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        );

        if(!response.ok){
            throw new Error('Freshness route optimization failed');
        }

        const data = await response.json();
        latestFreshnessRouteCandidates = data.candidates ?? [];

        const best =
            data.recommended_route
            ?? data.best_available_route
            ?? null;

        const isBestAvailable =
            data.decision_type === 'best_available';

        document.getElementById('freshnessDecisionHeading').innerText =
            data.decision_status
            ?? 'Route Decision';

        document.getElementById('freshnessRecommendedLabelCaption').innerText =
            isBestAvailable
                ? 'Best Available Route'
                : 'Recommended Route';

        document.getElementById('freshnessRecommendedLabel').innerText =
            best?.label ?? '-';

        document.getElementById('freshnessRecommendedScore').innerText =
            best ? `${best.route_score}/100` : '-';

        document.getElementById('freshnessRecommendedFeasibility').innerText =
            best?.freshness_feasibility ?? '-';

        const actionBox =
            document.getElementById('freshnessRequiredAction');

        if(data.required_action){
            actionBox.innerText = data.required_action;
            actionBox.classList.remove('hidden');
        } else {
            actionBox.classList.add('hidden');
        }

        document.getElementById('freshnessRouteReason').innerText =
            data.recommendation_reason ?? '';

        if(data.alternative_note){
            notice.innerText = data.alternative_note;
            notice.classList.remove('hidden');
        }

        latestFreshnessRouteCandidates.forEach((candidate, index) => {
            const recommended =
                candidate.recommended === true;

            const bestAvailable =
                candidate.best_available === true;

            const feasibilityClass =
                candidate.freshness_feasibility === 'Safe'
                    ? 'text-emerald-400'
                    : (
                        candidate.freshness_feasibility === 'Tight'
                            ? 'text-amber-400'
                            : 'text-rose-400'
                    );

            const card = document.createElement('div');
            card.className =
                `rounded-2xl border p-5 ${
                    (recommended || bestAvailable)
                        ? (
                            bestAvailable
                                ? 'bg-rose-500/10 border-rose-500/30'
                                : 'bg-cyan-500/10 border-cyan-500/30'
                        )
                        : 'bg-slate-800/60 border-slate-700'
                }`;

            card.innerHTML = `
                <div class="flex justify-between items-start gap-3">
                    <div>
                        <p class="text-[9px] uppercase font-black tracking-widest ${
                            recommended ? 'text-cyan-400' : 'text-slate-500'
                        }">
                            ${
                                recommended
                                    ? 'Recommended'
                                    : (
                                        bestAvailable
                                            ? 'Best Available'
                                            : `Rank #${candidate.rank}`
                                    )
                            }
                        </p>
                        <h3 class="text-lg font-black text-white mt-1">
                            ${candidate.label}
                        </h3>
                    </div>

                    <div class="text-right">
                        <p class="text-3xl font-black text-white">
                            ${candidate.route_score}
                        </p>
                        <p class="text-[9px] uppercase font-black text-slate-500">
                            /100
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-5 text-xs">
                    <div class="bg-slate-950/30 rounded-xl p-3">
                        <p class="text-[9px] uppercase font-black text-slate-500">ETA</p>
                        <p class="font-black text-white mt-1">${candidate.duration_hours} h</p>
                    </div>

                    <div class="bg-slate-950/30 rounded-xl p-3">
                        <p class="text-[9px] uppercase font-black text-slate-500">Distance</p>
                        <p class="font-black text-white mt-1">${candidate.distance_km} km</p>
                    </div>

                    <div class="bg-slate-950/30 rounded-xl p-3">
                        <p class="text-[9px] uppercase font-black text-slate-500">Arrival Quality</p>
                        <p class="font-black text-white mt-1">${candidate.projected_arrival_quality}/100</p>
                    </div>

                    <div class="bg-slate-950/30 rounded-xl p-3">
                        <p class="text-[9px] uppercase font-black text-slate-500">Risk</p>
                        <p class="font-black text-white mt-1">${candidate.projected_risk_score}/100</p>
                    </div>

                    <div class="bg-slate-950/30 rounded-xl p-3">
                        <p class="text-[9px] uppercase font-black text-slate-500">Transit Margin</p>
                        <p class="font-black text-white mt-1">${
                            candidate.transit_margin_hours === null
                                ? 'Unavailable'
                                : Number(candidate.transit_margin_hours).toFixed(1) + ' h'
                        }</p>
                    </div>

                    <div class="bg-slate-950/30 rounded-xl p-3">
                        <p class="text-[9px] uppercase font-black text-slate-500">Feasibility</p>
                        <p class="font-black mt-1 ${feasibilityClass}">
                            ${candidate.freshness_feasibility}
                        </p>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-700 flex items-center justify-between gap-3">
                    <p class="text-[10px] text-slate-500">
                        Carbon: ${candidate.carbon_kg} kg CO₂
                    </p>

                    ${
                        (candidate.geometry ?? []).length > 1
                            ? `<button onclick="previewFreshnessRoute(${index})"
                                       class="px-3 py-2 rounded-lg bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-300 text-[10px] uppercase font-black">
                                   Preview
                               </button>`
                            : ''
                    }
                </div>
            `;

            container.appendChild(card);
        });

        loading.classList.add('hidden');
        result.classList.remove('hidden');

    } catch(error) {
        console.error(error);
        loading.innerHTML = `
            <p class="text-rose-400 font-black">Unable to evaluate route candidates.</p>
            <p class="text-slate-400 text-xs mt-2">
                Check the routing provider configuration and server logs.
            </p>
        `;
    }
}

function previewFreshnessRoute(index) {
    const candidate = latestFreshnessRouteCandidates[index];

    if(!candidate || !candidate.geometry || candidate.geometry.length < 2){
        return;
    }

    const latlngs = candidate.geometry.map(
        c => [c[1], c[0]]
    );

    routeLine.setLatLngs(latlngs);
    map.fitBounds(
        routeLine.getBounds(),
        { padding: [50, 50] }
    );

    resetTruck(latlngs);
    closeFreshnessRoutes();
}

function closeFreshnessRoutes() {
    document.getElementById('freshnessRouteModal')
        .classList.add('hidden');
}

document.getElementById('freshnessRouteModal')
    .addEventListener('click', function(e) {
        if(e.target === this){
            closeFreshnessRoutes();
        }
    });

</script>