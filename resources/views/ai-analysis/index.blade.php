<x-app-layout>
    {{-- DeveloperDay 2026 / APICTA Grade Design Tokens & Animations --}}
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
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.3);
            border-radius: 1.5rem;
        }

        .btn-animate {
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-animate:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -5px rgba(79, 70, 229, 0.25);
        }
        .btn-animate:active {
            transform: translateY(0) scale(0.98);
        }

        .agri-table-row {
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .agri-table-row:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -5px rgba(15, 23, 42, 0.08);
        }
    </style>

    <div class="py-8 px-4 sm:px-6 lg:px-8 animate-card w-full">
        
        {{-- Header Area & Competition Badge --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-100/80 border border-indigo-200 text-indigo-800 text-xs font-bold tracking-wide mb-2 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
                    <span>DEVELOPERDAY 2026 • ROAD TO APICTA</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">AI Intelligence Hub</h1>
                <p class="text-slate-500 mt-1 font-medium text-sm">Predictive Sustainability Engine & Autonomous Logistics Risk Analytics.</p>
            </div>
            
            <a href="{{ route('ai-analysis.history') }}" 
               class="btn-animate group inline-flex items-center justify-center gap-3 px-6 py-3.5 rounded-2xl bg-indigo-50 border border-indigo-200/80 text-indigo-700 font-extrabold text-sm shadow-sm hover:bg-indigo-600 hover:text-white transition-all duration-300">
                <svg class="w-4 h-4 transition-transform group-hover:rotate-180 duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>View Analysis History</span>
            </a>
        </div>
                
        @php
            $predictionData = session('prediction_data', []);
        @endphp

        {{-- AI Prediction Result Engine View --}}
        @if(session('ai_result'))
            @php 
                $ai = session('ai_result');
                $extract = function($label) use ($ai) {
                    preg_match("/$label:\s*(.*)/i", $ai, $matches);
                    return $matches[1] ?? 'N/A';
                };
                
                $riskLevel = session('risk_level');
                $wasteProb = session('waste_probability');
                $sustainScore = session('sustainability_score');
                $shipmentData = session('shipment_data');
            @endphp

            <div class="agri-dark-card p-6 sm:p-8 mb-10 text-white relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-500 via-teal-400 to-emerald-500"></div>

                {{-- Header Result Bar --}}
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black flex items-center gap-2.5">
                        <span class="w-3 h-3 bg-indigo-400 rounded-full animate-pulse"></span>
                        <span>Predictive Analysis Output</span>
                    </h2>
                    <span class="px-3.5 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        OPENROUTER AI ENGINE
                    </span>
                </div>

                {{-- Selected Shipment Data Details Grid --}}
                @if($shipmentData)
                <div class="mb-8 bg-slate-800/60 border border-slate-700/80 rounded-2xl p-6">
                    <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4 flex items-center gap-2">
                        <span>📦</span>
                        <span>Shipment Telemetry Summary</span>
                    </h3>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Commodity</p>
                            <p class="font-extrabold text-white text-base mt-0.5">{{ $shipmentData['commodity'] }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Origin</p>
                            <p class="font-extrabold text-white text-base mt-0.5">{{ $shipmentData['origin'] }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Destination</p>
                            <p class="font-extrabold text-white text-base mt-0.5">{{ $shipmentData['destination'] }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Status</p>
                            <span class="inline-block mt-1 px-2.5 py-0.5 rounded-lg text-xs font-black uppercase bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                {{ $shipmentData['status'] }}
                            </span>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Distance</p>
                            <p class="font-extrabold text-white text-base mt-0.5">{{ number_format($shipmentData['distance'], 0, ',', '.') }} km</p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Remaining Days</p>
                            <p class="font-extrabold text-white text-base mt-0.5">{{ $shipmentData['remaining_days'] }} Days</p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Duration</p>
                            <p class="font-extrabold text-white text-base mt-0.5">{{ $shipmentData['duration'] ?? 'N/A' }} Hours</p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Carbon Emission</p>
                            <p class="font-extrabold text-rose-400 text-base mt-0.5">{{ $shipmentData['carbon_emission'] ?? 'N/A' }} kg CO₂</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Primary KPI Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Risk Level Assessment</p>
                        <span class="inline-block mt-3 px-4 py-1.5 rounded-xl text-xs font-black uppercase border
                            {{ str_contains($riskLevel, 'High') ? 'bg-rose-500/20 text-rose-400 border-rose-500/30' : 
                               (str_contains($riskLevel, 'Medium') ? 'bg-amber-500/20 text-amber-400 border-amber-500/30' : 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30') }}">
                            {{ $riskLevel }}
                        </span>
                    </div>
                    
                    <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Waste Probability</p>
                        <p class="mt-2 text-2xl font-black text-white">{{ $wasteProb }}</p>
                    </div>

                    <div class="bg-slate-800/60 p-5 rounded-2xl border border-slate-700/80">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sustainability Index</p>
                        <p class="mt-2 text-2xl font-black text-emerald-400">{{ $sustainScore }}</p>
                    </div>
                </div>

                {{-- Interactive Map & Spoilage Chart Grid --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

                    {{-- Route Visualization --}}
                    <div class="bg-slate-800/60 border border-slate-700/80 rounded-2xl p-6">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-4 flex items-center gap-2">
                            <span>🗺️</span>
                            <span>Live Route GIS Telemetry</span>
                        </h3>
                        <div id="map" class="h-[320px] rounded-xl overflow-hidden border border-slate-700"></div>
                    </div>

                    {{-- Predicted Spoilage Risk --}}
                    <div class="bg-slate-800/60 border border-slate-700/80 rounded-2xl p-6">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-1 flex items-center gap-2">
                            <span>📈</span>
                            <span>Predicted Spoilage Curve</span>
                        </h3>
                        <p class="text-slate-400 text-xs mb-4">Estimated degradation rate over remaining shelf-life days.</p>
                        <div class="h-[320px]">
                            <canvas id="riskChart"></canvas>
                        </div>
                    </div>

                </div>

                {{-- AI Explainability Drivers Section --}}
                @if(session('explainability'))
                @php
                    $drivers = session('explainability');
                    $totalImpact = collect($drivers)->sum('impact');
                @endphp

                <div class="mb-8 bg-slate-800/60 border border-slate-700/80 rounded-3xl p-6 sm:p-8">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-700">
                        <div>
                            <p class="text-xs uppercase tracking-[0.25em] text-indigo-400 font-black">AI Explainability</p>
                            <h2 class="text-2xl font-black text-white mt-1">Why did AI produce this prediction?</h2>
                        </div>
                        <div class="px-4 py-2 rounded-xl bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 text-xs font-black uppercase tracking-widest">
                            Decision Engine Core
                        </div>
                    </div>

                    <div class="space-y-5">
                        @foreach($drivers as $driver)
                        @php
                            if($driver['impact'] >= 30){
                                $barColor = 'bg-rose-500';
                                $textColor = 'text-rose-400';
                                $label = 'Critical';
                            }elseif($driver['impact'] >= 20){
                                $barColor = 'bg-amber-400';
                                $textColor = 'text-amber-400';
                                $label = 'High';
                            }else{
                                $barColor = 'bg-emerald-500';
                                $textColor = 'text-emerald-400';
                                $label = 'Medium';
                            }
                        @endphp

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">{{ $driver['icon'] }}</span>
                                    <div>
                                        <p class="text-white font-bold text-sm">{{ $driver['title'] }}</p>
                                        <p class="text-slate-400 text-xs">{{ $driver['reason'] }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-white font-black text-base">{{ $driver['impact'] }}%</p>
                                    <p class="{{ $textColor }} text-[10px] uppercase font-black tracking-wider">{{ $label }}</p>
                                </div>
                            </div>
                            <div class="h-2.5 rounded-full bg-slate-700 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000 {{ $barColor }}" style="width: {{ $driver['impact'] }}%;"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-8 border-t border-slate-700 pt-6">
                        <div class="flex justify-between mb-2">
                            <span class="text-slate-400 font-bold text-xs uppercase tracking-wider">Overall AI Confidence Index</span>
                            <span class="text-white font-black text-sm">{{ min(100,$totalImpact) }}%</span>
                        </div>
                        <div class="h-3 rounded-full bg-slate-700 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 via-cyan-400 to-emerald-400 transition-all duration-1000" style="width: {{ min(100,$totalImpact) }}%"></div>
                        </div>
                        <p class="mt-4 text-slate-300 text-xs leading-relaxed">
                            <span class="font-bold text-white">Primary Cause:</span> {{ $drivers[0]['reason'] }} This factor contributed the most to the overall AI prediction model.
                        </p>
                    </div>
                </div>

                {{-- Decision Score & Breakdown --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    
                    {{-- Priority Gauge --}}
                    <div class="bg-slate-800/60 rounded-3xl p-6 sm:p-8 border border-slate-700/80 flex flex-col items-center justify-center">
                        <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-6">AI Decision Score Gauge</h2>
                        <div class="relative w-52 h-52">
                            <svg class="w-full h-full -rotate-90">
                                <circle cx="104" cy="104" r="84" stroke="#334155" stroke-width="14" fill="none"/>
                                @php
                                    $circumference = 2 * pi() * 84;
                                    $offset = $circumference - ($circumference * session('priority_score', 0) / 100);
                                @endphp
                                <circle cx="104" cy="104" r="84" stroke="#6366f1" stroke-width="14" fill="none"
                                        stroke-linecap="round"
                                        stroke-dasharray="{{ $circumference }}"
                                        stroke-dashoffset="{{ $offset }}"
                                        class="transition-all duration-1000"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <p class="text-5xl font-black text-white">{{ session('priority_score') }}</p>
                                <p class="text-indigo-400 text-xs font-black uppercase tracking-widest mt-1">HIGH PRIORITY</p>
                            </div>
                        </div>
                    </div>

                    {{-- Impact Summary Grid --}}
                    <div class="bg-slate-800/60 rounded-3xl p-6 sm:p-8 border border-slate-700/80 flex flex-col justify-between">
                        <div>
                            <h3 class="text-xs uppercase tracking-widest text-slate-400 font-black mb-4">Decision Breakdown</h3>
                            <div class="space-y-3">
                                @foreach($drivers as $driver)
                                <div class="flex justify-between items-center py-2.5 border-b border-slate-700/60">
                                    <div class="flex items-center gap-3">
                                        <span class="text-lg">{{ $driver['icon'] }}</span>
                                        <span class="text-white font-bold text-xs">{{ $driver['title'] }}</span>
                                    </div>
                                    <span class="text-indigo-400 font-black text-xs">+{{ $driver['impact'] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-6">
                            <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-2xl p-4">
                                <p class="text-[10px] uppercase font-bold text-slate-400">Overall Impact</p>
                                <p class="text-2xl font-black text-white mt-0.5">{{ session('total_impact') }}</p>
                            </div>
                            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-4">
                                <p class="text-[10px] uppercase font-bold text-slate-400">Decision</p>
                                <p class="text-lg font-black text-emerald-400 mt-1">Dispatch Now</p>
                            </div>
                        </div>
                    </div>

                </div>
                @endif

                {{-- Recommendations Footer --}}
                <div class="pt-6 border-t border-slate-700/80">
                    <p class="text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest flex items-center gap-2">
                        <span>💡</span>
                        <span>AI Actionable Recommendations</span>
                    </p>
                    <div class="text-xs text-indigo-100/90 font-medium leading-relaxed bg-slate-800/80 p-5 rounded-2xl border border-slate-700">
                        <span>Recomendations</span>{!! nl2br(e(explode('Recommendations:', $ai)[1] ?? 'No recommendations provided for this route.')) !!}
                    </div>
                </div>

            </div>
        @endif

        {{-- Shipments Selector Table Container --}}
        <div class="agri-card p-6 sm:p-8">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900">Active Shipments for AI Evaluation</h2>
                    <p class="text-xs text-slate-500 font-medium">Select a shipment logistics record below to execute predictive risk analytics.</p>
                </div>
            </div>

            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-slate-500 font-extrabold text-[11px] uppercase tracking-wider">
                            <th class="px-6 py-3">Commodity</th>
                            <th class="px-6 py-3">Logistics Route</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Control</th>
                        </tr>
                    </thead>
                    
                    <tbody class="text-sm">
                        @foreach($shipments as $index => $shipment)
                        @php
                            $themes = [
                                0 => 'bg-emerald-50/60 border-emerald-200/80',
                                1 => 'bg-teal-50/60 border-teal-200/80',
                                2 => 'bg-indigo-50/60 border-indigo-200/80',
                                3 => 'bg-cyan-50/60 border-cyan-200/80',
                            ];
                            $theme = $themes[$index % 4];
                        @endphp
                        <tr class="agri-table-row {{ $theme }} border rounded-2xl overflow-hidden">
                            
                            {{-- Commodity --}}
                            <td class="px-6 py-5 font-black text-slate-900 text-base rounded-l-2xl border-l border-t border-b border-inherit bg-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                                        🌱
                                    </div>
                                    <span class="truncate">{{ $shipment->harvest->commodity ?? 'N/A' }}</span>
                                </div>
                            </td>
                            
                            {{-- Route --}}
                            <td class="px-6 py-5 font-bold text-slate-700 border-t border-b border-inherit bg-white">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-1.5 text-indigo-600">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                        <span class="truncate">{{ $shipment->origin }}</span>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    <div class="flex items-center gap-1.5 text-emerald-600">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                        <span class="truncate">{{ $shipment->destination }}</span>
                                    </div>
                                </div>
                            </td>
                            
                            {{-- Status --}}
                            <td class="px-6 py-5 border-t border-b border-inherit bg-white">
                                <span class="inline-flex items-center px-3.5 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider border shadow-sm
                                    @if($shipment->status == 'Delivered') bg-emerald-100 text-emerald-800 border-emerald-200
                                    @elseif($shipment->status == 'In Transit') bg-teal-100 text-teal-800 border-teal-200
                                    @elseif($shipment->status == 'Packed') bg-indigo-100 text-indigo-800 border-indigo-200
                                    @elseif($shipment->status == 'Harvested') bg-amber-100 text-amber-800 border-amber-200
                                    @else bg-slate-100 text-slate-700 border-slate-200 @endif">
                                    {{ $shipment->status }}
                                </span>
                            </td>

                            {{-- Submit Form Action --}}
                            <td class="px-6 py-5 text-right rounded-r-2xl border-r border-t border-b border-inherit bg-white">
                                <form action="{{ route('ai.analysis.run', $shipment->id) }}" method="POST" onsubmit="return showLoading(this)">
                                    @csrf
                                    <button type="submit" class="btn-animate inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white px-5 py-2.5 rounded-xl font-extrabold text-xs uppercase tracking-wider shadow-md shadow-indigo-600/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                        <span>Analyze Now</span>
                                    </button>
                                </form>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Map Leaflet Initialization --}}
    @php
        $shipmentData = session('shipment_data');
        $predictionData = session('prediction_data');
    @endphp

    @if($shipmentData)
    @php
        $routeGeometry = json_decode($shipmentData['route_geometry'], true);
    @endphp
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const originLat = {{ $shipmentData['origin_lat'] }};
        const originLng = {{ $shipmentData['origin_lng'] }};
        const destinationLat = {{ $shipmentData['destination_lat'] }};
        const destinationLng = {{ $shipmentData['destination_lng'] }};

        const map = L.map('map').setView([originLat, originLng], 6);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        L.marker([originLat, originLng]).addTo(map).bindPopup('Origin');
        L.marker([destinationLat, destinationLng]).addTo(map).bindPopup('Destination');

        const routeCoords = @json($routeGeometry);
        const latlngs = routeCoords.map(coord => [coord[1], coord[0]]);

        const routeLine = L.polyline(latlngs, {
            color: '#6366f1',
            weight: 5
        }).addTo(map);

        map.fitBounds(routeLine.getBounds());
    });
    </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @if(session()->has('prediction_data'))
    <script>
    const predictionData = @json($predictionData);
    const labels = predictionData.map(item => 'Day ' + item.day);
    const risks = predictionData.map(item => item.risk);

    new Chart(document.getElementById('riskChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Spoilage Risk (%)',
                data: risks,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    min: 0,
                    max: 100,
                    ticks: { color: '#94a3b8' },
                    grid: { color: 'rgba(51, 65, 85, 0.4)' }
                },
                x: {
                    ticks: { color: '#94a3b8' },
                    grid: { color: 'rgba(51, 65, 85, 0.4)' }
                }
            },
            plugins: {
                legend: {
                    labels: { color: '#f8fafc', font: { weight: 'bold' } }
                }
            }
        }
    });
    </script>
    @endif

    {{-- Script JavaScript Utama untuk Loading Screen --}}
    <script>
    function showLoading(form) {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        }

        const btn = form.querySelector("button[type='submit']");
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="white" stroke-width="3" opacity=".2"/>
                    <path d="M22 12a10 10 0 00-10-10" stroke="white" stroke-width="3"/>
                </svg>
                <span>Analyzing...</span>
            `;
        }
        return true;
    }
    </script>

    {{-- AI Loading Overlay (Berputar-putar secara Global) --}}
    <div id="loadingOverlay" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md hidden items-center justify-center z-[9999]">
        <div class="bg-slate-900 border border-slate-700 rounded-3xl p-10 w-[420px] text-center shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-500 via-cyan-400 to-emerald-400 animate-pulse"></div>
            
            <div class="flex justify-center mb-6">
                <div class="relative w-16 h-16">
                    <div class="absolute inset-0 rounded-full border-4 border-indigo-900"></div>
                    <div class="absolute inset-0 rounded-full border-4 border-indigo-500 border-t-transparent animate-spin"></div>
                </div>
            </div>

            <h2 class="text-2xl font-black text-white">AI is analyzing...</h2>
            <p class="text-slate-400 mt-3 text-xs leading-relaxed">
                AgriFlow AI is evaluating logistics risk, sustainability score, spoilage prediction, and shipment recommendations.
            </p>

            <div class="mt-8">
                <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-indigo-500 via-cyan-400 to-emerald-400 animate-pulse"></div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>