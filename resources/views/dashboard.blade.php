<x-app-layout>
    {{-- Custom Styles & Light Theme Design System derived from Homepage --}}
    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes softPulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.03); opacity: 0.9; } }
        @keyframes floating { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-8px); } }
        @keyframes earthRotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .animate-card { animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
        .delay-1 { animation-delay: 0.08s; } 
        .delay-2 { animation-delay: 0.16s; } 
        .delay-3 { animation-delay: 0.24s; }

        [x-cloak] { display: none !important; }

        /* Light modern card elevation & glass effect */
        .agri-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.04), 0 4px 12px -2px rgba(15, 23, 42, 0.02);
            border-radius: 1.5rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .agri-card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 35px -8px rgba(15, 23, 42, 0.08), 0 6px 16px -4px rgba(15, 23, 42, 0.03);
            border-color: rgba(16, 185, 129, 0.3);
        }

        .agri-glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        /* Scrollbar styles */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Animated Orb / Earth */
        .earth { animation: floating 6s ease-in-out infinite, earthRotate 35s linear infinite; }

        /* Weather Tabs */
        .weather-tab {
            padding: 10px 20px;
            border-radius: 9999px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #64748b;
            transition: all 0.25s ease;
            font-weight: 700;
            font-size: 0.875rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }

        .weather-tab:hover {
            transform: translateY(-2px);
            border-color: #0d9488;
            color: #0f172a;
        }

        .weather-tab.active {
            background: linear-gradient(135deg, #059669 0%, #0d9488 50%, #2563eb 100%);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 8px 20px -4px rgba(13, 148, 136, 0.4);
        }

        /* Background grid overlay */
        .bg-grid-pattern {
            background-image: radial-gradient(rgba(15, 23, 42, 0.05) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>

    <div class="min-h-screen bg-[#f8fafc] bg-grid-pattern text-slate-800 relative overflow-hidden pb-16">
        
        {{-- Background Soft Mesh Gradients inspired by Homepage --}}
        <div class="absolute -top-40 -left-40 w-[600px] h-[600px] bg-emerald-200/40 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute top-1/4 -right-40 w-[550px] h-[550px] bg-indigo-200/35 rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute bottom-10 left-1/3 w-[500px] h-[500px] bg-teal-200/30 rounded-full blur-[130px] pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 relative z-10">

            {{-- HEADER SECTION: Dashboard Intro with Layered AI Feature Module --}}
            <div class="mb-10 animate-card">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    {{-- Left Title & Welcome --}}
                    <div class="lg:col-span-7">
                        <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-bold tracking-wide mb-4 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>System v2.0 Live • Intelligence Center</span>
                        </div>
                        <h1 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight leading-[1.15]">
                            Dashboard Agriculture and Logistics System <br class="hidden sm:inline" />
                            <span class="bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-600">
                                Artificial Intelligence
                            </span>
                        </h1>
                        <p class="text-slate-600 mt-3 text-base sm:text-lg font-normal max-w-2xl leading-relaxed">
                            Welcome back. Here is your current supply chain overview, environmental conditions, and deterministic logistics decision support.
                        </p>
                    </div>

{{-- Right Homepage-Inspired Layered AI Metric Spotlight Module --}}
<div class="lg:col-span-5 relative py-2">
    <div class="relative mx-auto max-w-md lg:max-w-none">
        
        {{-- Main Center Card Container --}}
        <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 border border-slate-200/80 shadow-[0_15px_35px_-10px_rgba(15,23,42,0.08)] flex flex-col justify-between">
            
            {{-- Top Header Row: Status & Active Sync --}}
            <div class="flex items-center justify-between gap-3 mb-5">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">AI Supply Chain Status</p>
                        <h3 class="text-base font-black text-slate-900 mt-0.5">
                            @if($highRisk > 0)
                                <span class="text-amber-600">Attention Required</span>
                            @else
                                <span class="text-emerald-600">Optimal Performance</span>
                            @endif
                        </h3>
                    </div>
                </div>
                
                <span class="text-[10px] font-extrabold px-3 py-1.5 bg-emerald-100/80 text-emerald-800 rounded-full border border-emerald-200/50 shrink-0">
                    Active Sync
                </span>
            </div>

            {{-- Middle Section: Progress Bar Route Health Score --}}
            <div class="space-y-2 mb-6">
                <div class="flex justify-between text-xs font-bold">
                    <span class="text-slate-500">Route Health Score</span>
                    <span class="text-slate-900 font-extrabold">{{ round($avgScore) }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden p-0.5 border border-slate-200/50">
                    <div class="bg-gradient-to-r from-emerald-500 via-teal-500 to-indigo-600 h-2 rounded-full transition-all duration-1000" style="width: {{ $avgScore }}%"></div>
                </div>
            </div>

            {{-- Bottom Section: Integrated Spotlight Cards (Insight Score & Total Harvest) --}}
            <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-100">
                
                {{-- Total Harvest Card --}}
                <div class="bg-slate-50/80 rounded-2xl p-3 border border-slate-200/70 flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                        ⚖️
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Total Harvest</p>
                        <p class="text-xs font-black text-slate-900 truncate mt-0.5">
                            {{ number_format($totalWeight, 0, ',', '.') }} <span class="text-[10px] font-bold text-slate-500">KG</span>
                        </p>
                    </div>
                </div>

                {{-- Insight Score Badge --}}
                <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 text-white rounded-2xl p-3 shadow-md shadow-indigo-500/20 flex items-center justify-between">
                    <div>
                        <p class="text-[9px] font-extrabold uppercase tracking-widest text-indigo-200">Insight Score</p>
                        <p class="text-sm font-black leading-none mt-1">{{ number_format($avgScore, 1) }}</p>
                    </div>
                    <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center text-xs">
                        ⚡
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>

                </div>
            </div>


            {{-- OPERATIONAL KPI GRID --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-10 animate-card delay-1">
                @php
                    $items = [ 
                        ['Total Harvest', $totalHarvests, 'text-slate-900', 'bg-emerald-50 text-emerald-600', '🌱'], 
                        ['Total Weight', number_format($totalWeight, 0, ',', '.') . ' KG', 'text-slate-900', 'bg-teal-50 text-teal-600', '📦'], 
                        ['Shipments', $totalShipments, 'text-slate-900', 'bg-blue-50 text-blue-600', '🚚'], 
                        ['Delivered', $deliveredShipments, 'text-emerald-600', 'bg-emerald-100 text-emerald-700', '✅'], 
                        ['AI Analyses', $totalAnalyses, 'text-indigo-600', 'bg-indigo-50 text-indigo-600', '🧠'], 
                        ['High Risk', $highRisk, 'text-rose-600', 'bg-rose-50 text-rose-600', '🚨'] 
                    ];
                @endphp

                @foreach($items as $i => $item)
                    @php
                        $content = "Detail informasi untuk " . $item[0] . ".";
                        $wrapperStart = "<div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; color: #1e293b;'>";
                        $wrapperEnd = "</div>";
                        $emptyMsg = "<div style='color: #64748b; font-style: italic; text-align: center;'>Data tidak tersedia saat ini.</div>";

                        switch ($item[0]) {
                            case 'Total Harvest':
                                $shipments = \App\Models\Shipment::with('harvest')->get();
                                if ($shipments->isEmpty()) {
                                    $content = $wrapperStart . $emptyMsg . $wrapperEnd;
                                } else {
                                    $list = $shipments->pluck('harvest.commodity')->filter()->unique()->implode(', ');
                                    $content = $wrapperStart . "Terdapat total <strong>" . $totalHarvests . "</strong> data panen.<br><br>Komoditas: <strong>" . ($list ?: 'N/A') . "</strong>" . $wrapperEnd;
                                }
                                break;

                            case 'Total Weight':
                                $weightDetails = \App\Models\Harvest::select('commodity')->selectRaw('SUM(weight) as total_weight')->groupBy('commodity')->get();
                                if ($weightDetails->isEmpty()) {
                                    $content = $wrapperStart . $emptyMsg . $wrapperEnd;
                                } else {
                                    $inner = "<p style='margin-bottom:8px'>Detail berat per komoditas:</p><ul style='list-style: disc; margin-left: 20px;'>";
                                    foreach ($weightDetails as $w) { $inner .= "<li><strong>" . $w->commodity . "</strong>: " . number_format($w->total_weight, 0) . " KG</li>"; }
                                    $inner .= "</ul>";
                                    $content = $wrapperStart . $inner . $wrapperEnd;
                                }
                                break;

                            case 'Shipments':
                                $recentShipments = \App\Models\Shipment::with('harvest')->latest()->take(20)->get();
                                if ($recentShipments->isEmpty()) {
                                    $content = $wrapperStart . $emptyMsg . $wrapperEnd;
                                } else {
                                    $inner = "<p style='margin-bottom:12px;font-weight:bold'>Recent Shipments</p><div class='custom-scrollbar' style='max-height:320px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:12px;padding:12px;background:#fff;'><ul style='list-style:none;padding:0;margin:0'>";
                                    foreach ($recentShipments as $s) {
                                        $statusColor = match($s->status){
                                            'Harvested' => '#f59e0b',
                                            'Packed' => '#3b82f6',
                                            'In Transit' => '#8b5cf6',
                                            'Delivered' => '#10b981',
                                            default => '#64748b',
                                        };
                                        $commodityName = $s->harvest->commodity ?? 'N/A';
                                        $inner .= "<li style='padding:10px;margin-bottom:8px;border:1px solid #f1f5f9;border-radius:8px;'><strong>{$commodityName}</strong><br><span style='font-size:12px;color:#64748b'>{$s->origin} ➜ {$s->destination}</span><br><span style='display:inline-block;background:{$statusColor};color:white;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:bold;margin-top:4px;'>{$s->status}</span></li>";
                                    }
                                    $inner .= "</ul><div style='margin-top:10px;text-align:center;font-size:12px;color:#64748b;'>Showing latest 20 shipments</div></div>";
                                    $content = $wrapperStart . $inner . $wrapperEnd;
                                }
                                break;

                            case 'Delivered':
                                $deliveredList = \App\Models\Shipment::where('status', 'Delivered')->with('harvest')->latest()->take(5)->get();
                                if ($deliveredList->isEmpty()) {
                                    $content = $wrapperStart . $emptyMsg . $wrapperEnd;
                                } else {
                                    $inner = "<p style='margin-bottom:8px'>5 Pengiriman berhasil:</p><ul style='list-style: disc; margin-left: 20px;'>";
                                    foreach ($deliveredList as $s) { $inner .= "<li><strong>" . ($s->harvest->commodity ?? 'N/A') . "</strong></li>"; }
                                    $inner .= "</ul>";
                                    $content = $wrapperStart . $inner . $wrapperEnd;
                                }
                                break;

                            case 'AI Analyses':
                                $recentAnalyses = \App\Models\AiAnalysis::with('shipment.harvest')->latest()->take(20)->get();
                                if ($recentAnalyses->isEmpty()) {
                                    $content = $wrapperStart . $emptyMsg . $wrapperEnd;
                                } else {
                                    $inner = "<p style='margin-bottom:8px'>Recent AI Analyses</p><div class='custom-scrollbar' style='max-height:280px;overflow-y:auto;padding-right:8px;border:1px solid #e2e8f0;border-radius:12px;padding:12px;background:#fff;'><ul style='list-style:none;padding:0'>";
                                    foreach ($recentAnalyses as $a) {
                                        $commodity = $a->shipment->harvest->commodity ?? 'Unknown';
                                        $riskColor = $a->risk_level === 'High' ? '#ef4444' : ($a->risk_level === 'Medium' ? '#f59e0b' : '#10b981');
                                        $inner .= "<li style='margin-bottom:8px;padding:8px;border-radius:8px;border:1px solid #f1f5f9;'><strong>Commodity:</strong> {$commodity}<br><strong>Risk:</strong> <span style='color:{$riskColor};font-weight:bold;'>{$a->risk_level}</span> | <strong>Score:</strong> {$a->sustainability_score}</li>";
                                    }
                                    $inner .= "</ul><div style='margin-top:12px; text-align:center;'><a href='" . route('ai-analysis.history') . "' style='display:inline-block;padding:6px 16px;background:#4f46e5;color:white;border-radius:8px;font-weight:bold;font-size:12px;'>View All Analyses →</a></div></div>";
                                    $content = $wrapperStart . $inner . $wrapperEnd;
                                }
                                break;

                            case 'High Risk':
                                $highRiskItems = \App\Models\AiAnalysis::where('risk_level', 'High')->with('shipment.harvest')->take(5)->get();
                                if ($highRiskItems->isEmpty()) {
                                    $content = $wrapperStart . $emptyMsg . $wrapperEnd;
                                } else {
                                    $inner = "<p style='margin-bottom:8px'>Daftar risiko tinggi:</p><ul style='list-style: disc; margin-left: 20px;'>";
                                    foreach ($highRiskItems as $h) { $inner .= "<li>Komoditas: <strong>" . ($h->shipment->harvest->commodity ?? 'N/A') . "</strong></li>"; }
                                    $inner .= "</ul>";
                                    $content = $wrapperStart . $inner . $wrapperEnd;
                                }
                                break;
                        }
                    @endphp

                    <div onclick="openModal(this)"
                         data-title="{{ $item[0] }}"
                         data-content='{!! htmlspecialchars($content) !!}'
                         class="agri-card p-5 cursor-pointer agri-card-hover flex flex-col justify-between">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">{{ $item[0] }}</span>
                            <span class="p-1.5 rounded-lg {{ $item[3] }} text-xs">{{ $item[4] }}</span>
                        </div>
                        <p class="text-2xl font-black {{ $item[2] }} tracking-tight">{{ $item[1] }}</p>
                    </div>
                @endforeach
            </div>


            {{-- AI EXECUTIVE SUMMARY PANEL --}}
            <div class="mb-12 animate-card delay-2">
                <div class="agri-card p-8 lg:p-10 relative overflow-hidden bg-gradient-to-br from-white via-indigo-50/30 to-emerald-50/30 border-slate-200">
                    
                    {{-- Decorative subtle background accents --}}
                    <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-100/40 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute bottom-0 left-0 w-80 h-80 bg-emerald-100/40 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                            
                            <div class="flex-1">
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-extrabold tracking-wide mb-3">
                                    <span>🧠 AI Intelligence Report</span>
                                </div>
                                <h2 class="text-3xl font-black text-slate-900 tracking-tight">
                                    Today's Logistics Executive Recommendation
                                </h2>

                                <p class="text-slate-600 mt-4 text-base leading-relaxed max-w-3xl">
                                    AgriFlow AI continuously evaluated <strong>{{ $totalAnalyses }}</strong> shipment operations. 
                                    @if($criticalOperationalCount)
                                        🚨 <span class="font-bold text-rose-600">{{ $criticalOperationalCount }} critical shipment(s)</span> require immediate operational attention.
                                    @endif
                                    @if($shipImmediately)
                                        🚚 <span class="font-bold text-slate-800">{{ $shipImmediately }} shipment(s)</span> are ready for instant dispatch.
                                    @endif
                                    @if($optimizeRoute)
                                        🛣️ <span class="font-bold text-slate-800">{{ $optimizeRoute }} route(s)</span> have optimization recommendations available.
                                    @endif
                                    🌱 Current operational insights are generated from recorded shipment data and deterministic risk analysis.
                                </p>

                                <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    <div class="p-4 rounded-2xl bg-white/80 border border-slate-200/80 shadow-sm">
                                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Total AI Analyses</p>
                                        <p class="text-2xl font-black text-indigo-600 mt-1">{{ $totalAnalyses }}</p>
                                    </div>

                                    <div class="p-4 rounded-2xl bg-white/80 border border-slate-200/80 shadow-sm">
                                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Avg Sustainability</p>
                                        <p class="text-2xl font-black text-emerald-600 mt-1">{{ round($avgScore) }}%</p>
                                    </div>

                                    <div class="p-4 rounded-2xl bg-white/80 border border-slate-200/80 shadow-sm col-span-2 sm:col-span-1">
    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
        Active Critical
    </p>

    <p class="text-2xl font-black text-rose-600 mt-1">
        {{ $criticalOperationalCount }}
        <span class="text-xs text-slate-500 font-bold">
            shipment(s)
        </span>
    </p>
</div>
                                </div>
                            </div>

                            {{-- AI System Health & Sub-engines Card --}}
                            <div class="w-full lg:w-80 bg-white/90 rounded-2xl p-6 border border-slate-200 shadow-md">
                                <p class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-4">AI Engine Performance</p>
                                
                                <div class="space-y-3.5 text-xs font-semibold">
                                    <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                                        <span class="text-slate-600">AI Core Engine</span>
                                        <span class="text-emerald-600 font-bold flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Online</span>
                                    </div>
                                    <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                                        <span class="text-slate-600">Decision Engine</span>
                                        <span class="text-emerald-600 font-bold flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active</span>
                                    </div>
                                    <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                                        <span class="text-slate-600">Route Optimizer</span>
                                        <span class="text-emerald-600 font-bold flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Ready</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-600">Decision Engine</span>
                                        <span class="text-emerald-600 font-bold flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Running</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>


            {{-- LIVE ENVIRONMENTAL INTELLIGENCE --}}
            <div class="mb-12 animate-card delay-3">
                <div class="agri-card p-8 lg:p-10 relative overflow-hidden border-slate-200">
                    
                    {{-- Section Header --}}
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8 pb-6 border-b border-slate-200/80">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-50 text-teal-700 text-xs font-extrabold tracking-wide mb-2">
                                <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                                <span>LIVE ENVIRONMENTAL INTELLIGENCE</span>
                            </div>
                            <h2 class="text-3xl font-black text-slate-900 tracking-tight">Weather & Route Monitoring</h2>
                            <p class="text-slate-500 text-sm mt-1">
    Current weather observations and short-range forecast data from Open-Meteo, used as environmental context for operational decisions.
</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="px-4 py-2 rounded-2xl bg-slate-50 border border-slate-200 text-right">
                                <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Last Sync</p>
                                <p class="text-sm font-black text-slate-800" id="environmentTime">{{ now()->format('H:i:s') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Grid Layout for Earth & Weather Command --}}
                    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
                        
                        {{-- Earth Monitor Column (Diperbaiki secara seamless tanpa potongan kotak) --}}
                        <div class="xl:col-span-4 bg-slate-900 text-white rounded-3xl p-6 flex flex-col justify-between relative overflow-hidden shadow-xl">
                            <div class="flex justify-between items-center z-10">
                                <span class="text-xs font-extrabold uppercase tracking-widest text-teal-400">SATELLITE</span>
                                <span class="text-[10px] font-bold px-2.5 py-1 bg-teal-500/20 text-teal-300 rounded-full border border-teal-500/30">CONNECTED</span>
                            </div>

                            {{-- Globe Container dengan Mask Gradient --}}
                            <div class="my-3 relative flex items-center justify-center h-[260px] w-full overflow-hidden">
                                <div class="absolute w-56 h-56 bg-teal-500/20 rounded-full blur-3xl"></div>
                                <div class="relative z-10 w-full h-full flex items-center justify-center [mask-image:radial-gradient(circle,black_55%,transparent_80%)]">
                                    <div id="weatherOrb" class="w-full h-full flex items-center justify-center bg-transparent"
                                         data-rain="{{ $environment['weather']['rain'] ?? 0 }}"
                                         data-cloud="{{ $environment['weather']['cloud_cover'] ?? 0 }}"
                                         data-wind="{{ $environment['weather']['wind_speed_10m'] ?? 0 }}"
                                         data-temp="{{ $environment['weather']['temperature_2m'] ?? 25 }}">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-xs z-10">
                                <div class="p-3 bg-slate-800/60 rounded-2xl border border-slate-700/80">
                                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Location</p>
                                    <p class="font-extrabold text-white text-sm mt-0.5 truncate">{{ data_get($environment, 'location', 'Unknown') }}</p>
                                </div>
                                <div class="p-3 bg-slate-800/60 rounded-2xl border border-slate-700/80">
                                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Data Source</p>
                                    <p class="font-extrabold text-teal-400 text-sm mt-0.5">Open-Meteo API</p>
                                </div>
                            </div>
                        </div>

                        {{-- Weather Command Center Metrics --}}
                        <div class="xl:col-span-8 flex flex-col justify-between">
                            
                            {{-- Metrics Grid --}}
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80">
                                    <p class="text-xs font-bold text-slate-500">🌡 Temperature</p>
                                    <p class="text-3xl font-black text-slate-900 mt-2">{{ round($environment['weather']['temperature_2m']) }}°C</p>
                                </div>
                                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80">
                                    <p class="text-xs font-bold text-slate-500">💧 Humidity</p>
                                    <p class="text-3xl font-black text-teal-600 mt-2">{{ $environment['weather']['relative_humidity_2m'] }}%</p>
                                </div>
                                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80">
                                    <p class="text-xs font-bold text-slate-500">🌧 Rain Volume</p>
                                    <p class="text-3xl font-black text-blue-600 mt-2">{{ $environment['weather']['rain'] }} <span class="text-xs font-bold text-slate-400">mm</span></p>
                                </div>
                                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80">
                                    <p class="text-xs font-bold text-slate-500">🌬 Wind Speed</p>
                                    <p class="text-3xl font-black text-slate-900 mt-2">{{ round($environment['weather']['wind_speed_10m']) }} <span class="text-xs font-bold text-slate-400">km/h</span></p>
                                </div>
                                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80">
                                    <p class="text-xs font-bold text-slate-500">☁ Cloud Cover</p>
                                    <p class="text-3xl font-black text-slate-900 mt-2">{{ $environment['weather']['cloud_cover'] }}%</p>
                                </div>
                                <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-200">
                                    <p class="text-xs font-bold text-emerald-800">🌍 Weather Suitability</p>
                                    <p class="text-3xl font-black text-emerald-700 mt-2">{{ $environment['weather_suitability_score'] ?? '—' }}</p>
                                </div>
                            </div>

                            {{-- AI Weather Summary & Progress Indicators --}}
                            <div class="mt-6 p-6 rounded-2xl bg-slate-50 border border-slate-200/80 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                                <div>
                                    <p class="text-xs font-extrabold uppercase tracking-wider text-teal-700 mb-2">Weather Operations Summary</p>
                                    <p class="text-sm text-slate-600 leading-relaxed">{{ $environment['recommendation'] }}</p>
                                </div>
                                <div class="space-y-3">
                                    <div>
                                        <div class="flex justify-between text-xs font-bold mb-1">
                                            <span class="text-slate-500">Route Health</span>
                                            <span class="text-slate-900">{{ $environment['weather_suitability_score'] ?? 0 }}%</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-slate-200 overflow-hidden">
                                            <div class="h-full bg-emerald-500 rounded-full" style="width:{{ $environment['weather_suitability_score'] ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs font-bold mb-1">
                                            <span class="text-slate-500">Environmental Data Coverage</span>
<span class="text-slate-900">{{ $environment['data_coverage'] ?? 0 }}%</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-slate-200 overflow-hidden">
                                            <div class="h-full bg-indigo-600 rounded-full" style="width:{{ $environment['data_coverage'] ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs font-bold mb-1">
                                            <span class="text-slate-500">Environmental Condition Index</span>
                                            <span class="text-slate-900">
    {{ $environment['environmental_condition_index'] ?? '—' }}/100
</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-slate-200 overflow-hidden">
                                            <div
    class="h-full bg-rose-500 rounded-full"
    style="width: {{ $environment['environmental_condition_index'] ?? 0 }}%">
</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    {{-- Forecast Analytics Tabs & Chart --}}
                    <div class="mt-10 pt-8 border-t border-slate-200/80">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                            <div>
                                <p class="text-xs font-extrabold uppercase tracking-wider text-teal-700">Forecast Analytics</p>
                                <h3 class="text-xl font-black text-slate-900">Weather Forecast • Next 6 Hours</h3>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button class="weather-tab active" data-type="temp">🌡 Temperature</button>
                                <button class="weather-tab" data-type="humidity">💧 Humidity</button>
                                <button class="weather-tab" data-type="wind">🌬 Wind</button>
                                <button class="weather-tab" data-type="rain">🌧 Rain</button>
                                <button class="weather-tab" data-type="cloud">☁ Cloud</button>
                            </div>
                        </div>

                        <div class="h-[320px] w-full pt-4">
                            <canvas id="temperatureChart"></canvas>
                        </div>
                    </div>

                </div>
            </div>


{{-- CURRENT OPERATIONAL SNAPSHOT --}}
<div class="mb-12">
    <div class="mb-6">
        <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-700">
            OPERATIONAL SNAPSHOT
        </p>

        <h2 class="text-3xl font-black text-slate-900 tracking-tight">
            Current Logistics Intelligence
        </h2>

        <p class="text-slate-500 text-sm mt-1">
            Current metrics derived from recorded shipment data and AgriFlow's deterministic operational risk engine.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

        {{-- 1. Average Operational Risk --}}
        <div class="agri-card p-6 agri-card-hover flex flex-col justify-between">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">
                    Average Operational Risk
                </span>

                <div class="mt-6">
                    <p class="text-4xl font-black text-slate-900">
                        {{ $averageOperationalRisk }}
                        <span class="text-sm text-slate-400">/100</span>
                    </p>

                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Average deterministic risk index across active shipments.
                    </p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
                <span class="text-slate-500">
                    High-risk analysis share
                </span>

                <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-full">
                    {{ $highRiskShare }}%
                </span>
            </div>
        </div>

        {{-- 2. Critical Operational Shipments --}}
        <div class="agri-card p-6 agri-card-hover flex flex-col justify-between">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">
                    Critical Shipments
                </span>

                <div class="mt-6">
                    <p class="text-4xl font-black text-rose-600">
                        {{ $criticalOperationalCount }}
                    </p>

                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Active shipments currently classified as Critical by the operational risk engine.
                    </p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <span class="text-xs font-bold text-slate-500">
                    Requires operational attention when present
                </span>
            </div>
        </div>

        {{-- 3. Recorded Carbon --}}
        <div class="agri-card p-6 agri-card-hover flex flex-col justify-between">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">
                    Recorded Carbon
                </span>

                <div class="mt-6">
                    <p class="text-4xl font-black text-emerald-600">
                        {{ number_format($currentCarbon, 1) }}
                        <span class="text-sm text-slate-400">kg CO₂</span>
                    </p>

                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Aggregate carbon value currently recorded across shipment data.
                    </p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
                <span class="text-xs font-bold text-slate-500">
                    Recorded metric · not projected savings
                </span>
            </div>
        </div>

        {{-- 4. Delivery Completion --}}
        <div class="agri-card p-6 agri-card-hover flex flex-col justify-between">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-400">
                    Delivery Completion
                </span>

                <div class="mt-6">
                    <p class="text-4xl font-black text-indigo-600">
                        {{ $currentEfficiency }}%
                    </p>

                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Share of recorded shipments currently marked as Delivered.
                    </p>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
                <span class="text-slate-500">
                    Delivered
                </span>

                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-full">
                    {{ $deliveredShipments }} / {{ $totalShipments }}
                </span>
            </div>
        </div>

    </div>
</div>


            {{-- MAIN CONTENT GRID: Priority Actions, Sustainability & Analytics Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                {{-- Left Column (8 cols): Priority Actions & Sustainability --}}
                <div class="lg:col-span-8 space-y-8">
                    
                    {{-- Priority Actions Panel --}}
                    <div class="agri-card p-6">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-base font-black text-slate-900 uppercase tracking-wider">Priority Actions</h3>
                                <p class="text-xs text-slate-500">High-risk shipment alerts requiring immediate operational dispatch</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-bold bg-rose-100 text-rose-700 px-3 py-1 rounded-full uppercase">{{ $criticalOperationalCount }} Critical</span>
                                <a href="{{ route('ai-analysis.history') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors flex items-center gap-1">
                                    <span>View All</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>

                        <div class="max-h-80 overflow-y-auto pr-2 custom-scrollbar space-y-3">
                            @foreach(\App\Models\AiAnalysis::where('risk_level', 'High')->with('shipment.harvest')->get() as $alert)
                                <a href="{{ route('shipments.show', $alert->shipment_id) }}" class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-200/60 hover:bg-slate-100/80 transition-all group">
                                    <div>
                                        <p class="font-bold text-sm text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $alert->shipment->harvest->commodity ?? 'Commodity' }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ Str::limit($alert->recommendations, 70) }}</p>
                                    </div>
                                    <span class="text-indigo-600 font-bold text-xs flex items-center gap-1 group-hover:translate-x-1 transition-transform">View ➔</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Sustainability Impact & Green Score --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-6 rounded-3xl shadow-lg shadow-emerald-600/15 flex flex-col justify-between">
                            <div>
                                <p class="text-xs font-extrabold opacity-80 uppercase tracking-wider">
    Average Sustainability Score
</p>
                                <p class="text-5xl font-black mt-3">
    {{ $greenImpactScore }}
    <span class="text-lg">/100</span>
</p>
                            </div>
                            <p class="text-xs text-emerald-100 mt-4 leading-relaxed">
    Average sustainability score from persisted AgriFlow analyses. This is a decision-support indicator, not measured environmental impact.
</p>
                        </div>
<div class="agri-card p-6 flex flex-col justify-between">
    <div>
        <p class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">
            Recorded Carbon
        </p>

        <p class="text-3xl font-black text-slate-900 mt-2">
            {{ number_format($currentCarbon, 1) }}
            <span class="text-sm font-bold text-slate-500">
                KG CO₂
            </span>
        </p>
    </div>

    <div class="mt-4">
        <div class="flex justify-between text-xs font-bold text-slate-500 mb-1">
            <span>Average Sustainability Score</span>
            <span>{{ $greenImpactScore }}/100</span>
        </div>

        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
            <div
                class="bg-emerald-500 h-full rounded-full"
                style="width: {{ min(100, max(0, $greenImpactScore)) }}%">
            </div>
        </div>

        <p class="text-[10px] text-slate-400 mt-2">
            Recorded carbon aggregate; no projected savings implied.
        </p>
    </div>
</div>

{{-- Close Sustainability 2-column grid --}}
</div>

{{-- System Wawasan Insight --}}
                    <div class="agri-card p-6 border-l-4 border-l-teal-500">
                        <h3 class="font-extrabold text-xs text-teal-700 uppercase tracking-wider mb-2">System Insight</h3>
                        <p class="text-slate-700 text-sm italic font-medium">"{{ $aiInsightText }}"</p>
                    </div>

                    {{-- Logistics Overview & Verdict --}}
                    <div class="agri-card p-6 relative overflow-hidden">
                        <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                            <div>
                                <p class="text-[10px] uppercase tracking-widest text-teal-600 font-extrabold">AI Executive Summary</p>
                                <h3 class="text-lg font-black text-slate-900">Logistics Overview</h3>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center text-lg">🤖</div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60">
                                <p class="text-xs text-slate-500">Critical Shipments</p>
                                <p class="text-2xl font-black text-rose-600 mt-1">{{ $criticalOperationalCount }}</p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60">
                                <p class="text-xs text-slate-500">Route Optimization</p>
                                <p class="text-2xl font-black text-teal-600 mt-1">{{ $optimizeRoute }} <span class="text-xs text-slate-400">Routes</span></p>
                            </div>
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60">
                                <p class="text-xs text-slate-500">Immediate Dispatch</p>
                                <p class="text-2xl font-black text-amber-600 mt-1">{{ $shipImmediately }} <span class="text-xs text-slate-400">Shipments</span></p>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200/60">
                            <p class="text-xs text-emerald-800 font-extrabold uppercase tracking-wider mb-1">AI Verdict</p>
                            <p class="text-xs text-slate-700 leading-relaxed">
                                @if($criticalOperationalCount >= 5)
                                    Several shipments require immediate operational attention. Prioritize dispatch scheduling to minimize spoilage and improve logistics efficiency.
                                @elseif($criticalOperationalCount >= 2)
                                    The logistics network remains stable, but several shipments should be monitored to maintain sustainability performance.
                                @else
                                    Current logistics performance is healthy. Continue monitoring shipment quality and optimize routes where possible.
                                @endif
                            </p>
                        </div>
                    </div>

                </div>

                {{-- Right Column (4 cols): Risk Chart, Shipment Status & Prediction Trends --}}
                <div class="lg:col-span-4 space-y-8">
                    
                    {{-- Risk Distribution Chart --}}
                    <div class="agri-card p-6">
                        <h3 class="font-extrabold text-xs text-slate-400 uppercase tracking-wider mb-4">Risk Distribution</h3>
                        <div class="h-56"><canvas id="riskChart"></canvas></div>
                    </div>

                    {{-- Shipment Status Chart --}}
                    <div class="agri-card p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-extrabold text-xs text-slate-400 uppercase tracking-wider">Shipment Status</h3>
                            <span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full">LIVE</span>
                        </div>
                        <div class="h-56"><canvas id="shipmentStatusChart"></canvas></div>
                    </div>

                    {{-- Critical Alert Box --}}
                    @if($latestHighRisk)
                        @php
                            $text = $latestHighRisk->recommendations;
                            $parts = explode('Explanation:', $text);
                            $recommendation = $parts[0] ?? '';
                            $explanation = isset($parts[1]) ? 'Explanation:' . $parts[1] : '';
                        @endphp

                        <div class="p-6 rounded-3xl bg-rose-50 border border-rose-200">
                            <h3 class="font-black text-rose-700 text-sm mb-3 flex items-center gap-2">🚨 Critical Alert</h3>
                            <div class="space-y-3 text-xs text-slate-700 leading-relaxed">
                                <div>
                                    <p class="font-extrabold text-rose-800 uppercase tracking-wider mb-1">Recommendations:</p>
                                    <p>{{ str_replace('Recommendations:', '', $recommendation) }}</p>
                                </div>
                                @if($explanation)
                                    <div class="pt-3 border-t border-rose-200">
                                        <p class="italic text-slate-600">{{ $explanation }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>

            </div>

        </div>
    </div>

    {{-- MODAL DIALOG --}}
    <div id="myModal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white border border-slate-200 rounded-3xl p-6 max-w-md w-full shadow-2xl">
            <h3 id="modalTitle" class="text-xl font-black text-slate-900 mb-4"></h3>
            <div id="modalContent" class="text-slate-600 text-sm leading-relaxed"></div>
            <button onclick="closeModal()" class="mt-6 w-full bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-slate-800 transition-colors">Close</button>
        </div>
    </div>

    {{-- SCRIPTS & CHART INTEGRATION --}}
    <script>
        function openModal(element) {
            const title = element.getAttribute('data-title');
            const content = element.getAttribute('data-content');
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalContent').innerHTML = content;
            document.getElementById('myModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('myModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('myModal');
            if (event.target == modal) { closeModal(); }
        }
    </script>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <script>
        // Risk Doughnut Chart
        const ctx = document.getElementById('riskChart').getContext('2d');
        Chart.register(ChartDataLabels);
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Low', 'Medium', 'High'],
                datasets: [{
                    data: [{{ $lowRisk }}, {{ $mediumRisk }}, {{ $highRisk }}],
                    backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#475569', font: { weight: 'bold', size: 11 } } },
                    datalabels: {
                        color: '#ffffff',
                        font: { weight: 'bold', size: 12 },
                        formatter: (value, ctx) => {
                            if (value === 0) return "";
                            let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            return ((value * 100) / sum).toFixed(0) + "%";
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = context.raw;
                                let sum = context.dataset.data.reduce((a, b) => a + b, 0);
                                let percentage = ((value / sum) * 100).toFixed(1);
                                return ` ${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Shipment Status Bar Chart
        const shipmentCtx = document.getElementById('shipmentStatusChart');
        new Chart(shipmentCtx, {
            type: 'bar',
            data: {
                labels: ['Harvested', 'Packed', 'In Transit', 'Delivered'],
                datasets: [{
                    data: [{{ $statusHarvested }}, {{ $statusPacked }}, {{ $statusTransit }}, {{ $statusDelivered }}],
                    borderRadius: 8,
                    backgroundColor: ['#6366f1', '#f59e0b', '#0d9488', '#10b981']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        color: '#ffffff',
                        anchor: 'center',
                        align: 'center',
                        font: { size: 12, weight: 'bold' },
                        formatter: function(value){ return value; }
                    }
                },
                scales: {
                    x: { ticks: { color: '#64748b', font: { weight: 'bold' } }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { color: '#64748b' }, grid: { color: 'rgba(226, 232, 240, 0.8)' } }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Weather Forecast Trend Chart
        document.addEventListener("DOMContentLoaded", () => {
            const ctx = document.getElementById("temperatureChart");
            if (!ctx) return;

            const weatherSeries = {
                temp: @json(collect($weatherTrend)->pluck('temp')),
                humidity: @json(collect($weatherTrend)->pluck('humidity')),
                wind: @json(collect($weatherTrend)->pluck('wind')),
                rain: @json(collect($weatherTrend)->pluck('rain')),
                cloud: @json(collect($weatherTrend)->pluck('cloud'))
            };

            const weatherColor = { temp: "#0d9488", humidity: "#2563eb", wind: "#059669", rain: "#4f46e5", cloud: "#64748b" };

            const weatherChart = new Chart(ctx, {
                type: "line",
                data: {
                    labels: @json(collect($weatherTrend)->pluck('time')),
                    datasets: [{
                        label: "Temperature",
                        data: weatherSeries.temp,
                        borderColor: "#0d9488",
                        borderWidth: 3,
                        fill: true,
                        tension: .45,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        backgroundColor: (ctx) => {
                            const chart = ctx.chart;
                            const { ctx: canvas, chartArea } = chart;
                            if (!chartArea) return null;
                            const gradient = canvas.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, "rgba(13, 148, 136, 0.25)");
                            gradient.addColorStop(1, "rgba(13, 148, 136, 0.0)");
                            return gradient;
                        }
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: "index", intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.dataset.label;
                                    const value = context.parsed.y;
                                    if (label === "Rain Probability" || label === "Humidity" || label === "Cloud Cover") return label + ": " + value + "%";
                                    if (label === "Temperature") return label + ": " + value + "°C";
                                    if (label === "Wind Speed") return label + ": " + value + " km/h";
                                    return label + ": " + value;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: "#64748b", font: { weight: 'bold' } } },
                        y: { grid: { color: "rgba(226, 232, 240, 0.8)" }, ticks: { color: "#64748b" } }
                    }
                }
            });

            document.querySelectorAll(".weather-tab").forEach(tab => {
                tab.addEventListener("click", () => {
                    document.querySelectorAll(".weather-tab").forEach(btn => btn.classList.remove("active"));
                    tab.classList.add("active");
                    const type = tab.dataset.type;
                    const labels = { temp: "Temperature", humidity: "Humidity", wind: "Wind Speed", rain: "Rain Probability", cloud: "Cloud Cover" };
                    weatherChart.data.datasets[0].label = labels[type];
                    weatherChart.data.datasets[0].data = weatherSeries[type];
                    weatherChart.data.datasets[0].borderColor = weatherColor[type];
                    weatherChart.update('active');
                });
            });
        });
    </script>
</x-app-layout>