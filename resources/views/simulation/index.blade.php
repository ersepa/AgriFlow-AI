<x-app-layout>
    {{-- Design System & Animations for DeveloperDay 2026 / APICTA --}}
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

        .shipment-card {
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .shipment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -5px rgba(15, 23, 42, 0.08);
        }
        .shipment-active {
            border-color: #6366f1 !important;
            background: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%) !important;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2), 0 10px 25px -5px rgba(99, 102, 241, 0.15) !important;
        }

        .vehicle-card {
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .vehicle-card:hover {
            border-color: #6366f1;
            transform: translateY(-2px);
        }
        .vehicle-selected {
            border-color: #6366f1 !important;
            background: #ffffff !important;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.15) !important;
        }

        .btn-animate {
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-animate:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -5px rgba(99, 102, 241, 0.25);
        }
        .btn-animate:active {
            transform: translateY(0) scale(0.98);
        }

        #shipmentList::-webkit-scrollbar { width: 6px; }
        #shipmentList::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        #shipmentList::-webkit-scrollbar-thumb:hover { background: #6366f1; }
    </style>

    <div class="py-8 px-4 sm:px-6 lg:px-8 animate-card w-full">

{{-- HERO SECTION --}}
<section class="agri-card p-8 md:p-10 relative overflow-hidden bg-white shadow-sm border border-slate-200/90 mb-8">
    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-teal-400 via-indigo-500 to-cyan-400"></div>
    
    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="px-4 py-1.5 rounded-full bg-indigo-100/90 border border-indigo-200 text-indigo-800 text-xs font-black tracking-widest uppercase shadow-sm">
                    DEVELOPERDAY 2026 • ROAD TO APICTA
                </span>
            </div>
            
            <h1 class="text-3xl md:text-5xl font-black tracking-tight leading-none text-slate-900">
                Logistics Digital Twin <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-indigo-700 to-teal-600">Simulator</span>
            </h1>
            
            <p class="mt-4 text-slate-600 text-sm md:text-base max-w-2xl font-bold leading-relaxed">
                Replikasi kondisi logistik aktual secara presisi. Bandingkan jenis armada, kendalikan suhu Cold-Chain, dan simulasikan risiko penyusutan (*spoilage*) secara *real-time* berbasis kecerdasan buatan.
            </p>
        </div>
        
        <div class="w-16 h-16 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center text-3xl shrink-0 shadow-sm">
            🤖
        </div>
    </div>

    {{-- Metric Highlights --}}
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6 border-t border-slate-200/80 pt-6">
        <div class="flex items-center gap-3">
            <span class="text-2xl">⚡</span>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-slate-500">AI Decision Engine</p>
                <p class="text-sm font-extrabold text-slate-900 mt-0.5">Predictive Risk & Delivery Analytics</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <span class="text-2xl">🌐</span>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-slate-500">Verified Standards</p>
                <p class="text-sm font-extrabold text-teal-700 mt-0.5">Accurate Cold-Chain & CO₂ Tracking</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <span class="text-2xl">🌱</span>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-slate-500">Core Mission</p>
                <p class="text-sm font-extrabold text-emerald-700 mt-0.5">Zero Food Loss & Eco-Friendly Transit</p>
            </div>
        </div>
    </div>
</section>

        {{-- MAIN LAYOUT: LIST & PREVIEW --}}
        <div class="grid lg:grid-cols-12 gap-8 items-start">

            {{-- LEFT COLUMN: SHIPMENT SELECTOR (lg:col-span-5) --}}
            <div class="lg:col-span-5">
                <div class="agri-card p-6">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                        <div>
                            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Database Portal</span>
                            <h2 class="text-xl font-black text-slate-900">Shipment Telemetry</h2>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-indigo-100 text-indigo-800 font-extrabold text-xs">
                            {{ $shipments->total() }} Active
                        </span>
                    </div>

                    {{-- Search Field --}}
                    <div class="relative mb-4">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input id="searchShipment" type="text" placeholder="Filter commodity or route..."
                               class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none">
                    </div>

                    {{-- Shipment Cards Scrollable List --}}
                    <div id="shipmentList" class="max-h-[580px] overflow-y-auto space-y-3 pr-1">
                        @foreach($shipments as $shipment)
                        <button type="button"
                                class="shipment-card group w-full text-left p-4 rounded-2xl bg-white border border-slate-200/90 hover:border-indigo-300 relative overflow-hidden"
                                data-id="{{ $shipment->id }}"
                                data-commodity="{{ $shipment->harvest->commodity ?? 'Commodity' }}"
                                data-origin="{{ $shipment->origin }}"
                                data-destination="{{ $shipment->destination }}"
                                data-status="{{ $shipment->status }}"
                                data-distance="{{ round($shipment->distance_km) }}">
                            
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-700 flex items-center justify-center text-lg font-bold">
                                        📦
                                    </div>
                                    <div>
                                        <h3 class="font-black text-slate-900 text-sm group-hover:text-indigo-600 transition-colors">
                                            {{ $shipment->harvest->commodity ?? 'Commodity' }}
                                        </h3>
                                        <p class="text-[10px] text-slate-400 font-bold">ID #{{ $shipment->id }}</p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-lg border text-[10px] font-black uppercase
                                    @if($shipment->status == 'Delivered') bg-emerald-100 text-emerald-800 border-emerald-200
                                    @elseif($shipment->status == 'In Transit') bg-teal-100 text-teal-800 border-teal-200
                                    @elseif($shipment->status == 'Packed') bg-indigo-100 text-indigo-800 border-indigo-200
                                    @else bg-amber-100 text-amber-800 border-amber-200 @endif">
                                    {{ $shipment->status }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-xs font-bold text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                <div class="truncate max-w-[40%]">📍 {{ $shipment->origin }}</div>
                                <span class="text-slate-300">➔</span>
                                <div class="truncate max-w-[40%] text-right">🏁 {{ $shipment->destination }}</div>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Distance</span>
                                <span class="font-black text-slate-900">{{ round($shipment->distance_km) }} <span class="text-slate-500 font-normal">KM</span></span>
                            </div>
                        </button>
                        @endforeach
                    </div>

                    <input type="hidden" id="selectedShipment" value="">

                    {{-- Pagination Controls --}}
                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-bold text-[10px]">
                            Showing {{ $shipments->firstItem() }}-{{ $shipments->lastItem() }} of {{ $shipments->total() }}
                        </span>
                        <div class="flex gap-2">
                            @if($shipments->onFirstPage())
                                <button disabled class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-400 text-xs font-bold">Prev</button>
                            @else
                                <a href="{{ $shipments->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white text-xs font-bold transition-colors">Prev</a>
                            @endif

                            @if($shipments->hasMorePages())
                                <a href="{{ $shipments->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white text-xs font-bold transition-colors">Next</a>
                            @else
                                <button disabled class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-400 text-xs font-bold">Next</button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- RIGHT COLUMN: DIGITAL TWIN LIVE PREVIEW (lg:col-span-7) --}}
            <div class="lg:col-span-7">
                
                {{-- Placeholder state --}}
                <div id="shipmentPlaceholder" class="agri-card p-12 text-center border-dashed border-2 border-slate-300">
                    <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto text-3xl font-bold mb-4 shadow-sm">
                        📡
                    </div>
                    <h3 class="text-lg font-black text-slate-900">Select Shipment to Initialize Digital Twin</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 leading-relaxed">
                        Pilih salah satu log pengiriman di panel kiri untuk membuka Digital Twin telemetry dan memulai konfigurasi simulasi.
                    </p>
                </div>

                {{-- Digital Twin Active Preview --}}
                <div id="shipmentPreview" class="hidden agri-card p-6 sm:p-8 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-indigo-600 via-teal-500 to-emerald-500"></div>

                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                        <div>
                            <span class="px-3 py-1 rounded-full bg-emerald-100/80 border border-emerald-200 text-emerald-800 text-[10px] font-black tracking-wide uppercase">
                                DIGITAL TWIN SYNCED
                            </span>
                            <h2 id="commodity" class="text-2xl font-black text-slate-900 mt-1">Commodity Name</h2>
                        </div>
                        <div class="text-right">
                            <span id="shipmentStatus" class="inline-block px-3 py-1 rounded-lg text-xs font-black uppercase bg-indigo-50 text-indigo-700 border border-indigo-200">
                                Status
                            </span>
                        </div>
                    </div>

                    {{-- Route Overview --}}
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80 mb-6">
                        <div class="flex items-center justify-between text-xs sm:text-sm font-black text-slate-900">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Origin</p>
                                <p id="origin" class="text-indigo-600 mt-0.5">Origin Location</p>
                            </div>
                            <div class="flex-1 px-4 text-center">
                                <div class="border-t-2 border-dashed border-indigo-300 relative my-2">
                                    <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 bg-white px-1 text-xs">🚚</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-slate-400 uppercase">Destination</p>
                                <p id="destination" class="text-emerald-600 mt-0.5">Destination City</p>
                            </div>
                        </div>
                    </div>

                    {{-- Live Telemetry Cards --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Est. Distance</p>
                            <p id="distance" class="text-lg font-black text-slate-900 mt-1">0 km</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Transit Time</p>
                            <p id="eta" class="text-lg font-black text-indigo-600 mt-1">-- hrs</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Carbon Baseline</p>
                            <p id="carbon" class="text-lg font-black text-rose-500 mt-1">-- kg CO₂</p>
                        </div>
                    </div>

                    {{-- Timeline Progress --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-black uppercase text-slate-400 tracking-wider">Logistics Lifecycle Progress</p>
                            <span id="timelineBadge" class="text-[10px] font-black uppercase px-2.5 py-0.5 bg-slate-100 text-slate-700 rounded-md">
                                READY
                            </span>
                        </div>

                        <div class="relative py-2">
                            <div class="absolute left-4 right-4 top-1/2 -translate-y-1/2 h-1 bg-slate-200 rounded-full"></div>
                            <div id="progressLine" class="absolute left-4 top-1/2 -translate-y-1/2 h-1 bg-gradient-to-r from-indigo-500 to-teal-400 rounded-full transition-all duration-500" style="width: 0%;"></div>

                            <div class="relative z-10 flex justify-between items-center px-2">
                                <div id="stepHarvested" class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-black border-2 border-white">✓</div>
                                <div id="stepPacked" class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-black border-2 border-white">✓</div>
                                <div id="stepTransit" class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-black border-2 border-white">🚚</div>
                                <div id="stepDelivered" class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-black border-2 border-white">📦</div>
                            </div>
                        </div>

                        <div class="flex justify-between text-[10px] font-extrabold text-slate-400 mt-2 px-1">
                            <span>Harvested</span>
                            <span>Packed</span>
                            <span>In Transit</span>
                            <span>Delivered</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        {{-- SIMULATION CONTROL PANEL (Appears after shipment selection) --}}
        <div id="simulationPanel" class="hidden mt-10 agri-card p-6 sm:p-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-teal-500 via-indigo-600 to-purple-600"></div>

            <div class="mb-6 pb-4 border-b border-slate-100">
                <span class="px-3.5 py-1 rounded-full bg-indigo-100/80 border border-indigo-200 text-indigo-800 text-[10px] font-black tracking-wide uppercase">
                    SIMULATION CONTROL CENTER
                </span>
                <h2 class="text-2xl font-black text-slate-900 mt-2">Configure Scenario Variables</h2>
                <p class="text-xs text-slate-500 font-medium mt-1">Ubah variabel operasional sebelum meluncurkan simulasi Digital Twin.</p>
            </div>

            {{-- Vehicle Selection --}}
            <div class="mb-8">
                <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-4">
                    1. Select Transportation Vehicle Fleet
                </label>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="vehicle-card cursor-pointer p-4 rounded-2xl bg-slate-50 border border-slate-200/90 text-center vehicle-selected"
                         data-value="Truck">
                        <div class="text-3xl mb-2">🚚</div>
                        <h4 class="vehicle-title font-black text-slate-900 text-sm">Standard Truck</h4>
                        <p class="vehicle-desc text-[10px] text-slate-500 mt-0.5">Ambient logistics</p>
                    </div>

                    <div class="vehicle-card cursor-pointer p-4 rounded-2xl bg-slate-50 border border-slate-200/90 text-center"
                         data-value="cold">
                        <div class="text-3xl mb-2">❄️</div>
                        <h4 class="vehicle-title font-black text-slate-700 text-sm">Refrigerated Truck</h4>
                        <p class="vehicle-desc text-[10px] text-slate-500 mt-0.5">Cold-chain active</p>
                    </div>

                    <div class="vehicle-card cursor-pointer p-4 rounded-2xl bg-slate-50 border border-slate-200/90 text-center"
                         data-value="plane">
                        <div class="text-3xl mb-2">✈️</div>
                        <h4 class="vehicle-title font-black text-slate-700 text-sm">Air Cargo</h4>
                        <p class="vehicle-desc text-[10px] text-slate-500 mt-0.5">Express delivery</p>
                    </div>

                    <div class="vehicle-card cursor-pointer p-4 rounded-2xl bg-slate-50 border border-slate-200/90 text-center"
                         data-value="ship">
                        <div class="text-3xl mb-2">🚢</div>
                        <h4 class="vehicle-title font-black text-slate-700 text-sm">Sea Cargo</h4>
                        <p class="vehicle-desc text-[10px] text-slate-500 mt-0.5">Bulk eco transit</p>
                    </div>
                </div>

                <input type="hidden" id="vehicle" value="Truck">
            </div>

            {{-- Operational Parameters Grid --}}
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                {{-- Storage Temperature --}}
                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200/90">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <span class="text-[10px] font-black uppercase text-slate-400">Cold Chain Target</span>
                            <h4 class="font-black text-slate-900 text-sm">Storage Temperature</h4>
                        </div>
                        <div id="tempValue" class="px-3 py-1 rounded-xl bg-indigo-100 text-indigo-800 font-black text-sm">
                            5°C
                        </div>
                    </div>
                    <input id="temperature" type="range" min="0" max="25" value="5" class="w-full accent-indigo-600 cursor-pointer">
                    <div class="flex justify-between text-[10px] font-extrabold text-slate-400 mt-2">
                        <span>0°C (Chilled)</span>
                        <span class="text-emerald-600">Optimal Range</span>
                        <span>25°C (Ambient)</span>
                    </div>
                </div>

                {{-- Transit Delay --}}
                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200/90">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <span class="text-[10px] font-black uppercase text-slate-400">Bottleneck Factor</span>
                            <h4 class="font-black text-slate-900 text-sm">Simulated Transit Delay</h4>
                        </div>
                        <div id="delayValue" class="px-3 py-1 rounded-xl bg-amber-100 text-amber-800 font-black text-sm">
                            0 Day
                        </div>
                    </div>
                    <input id="delay" type="range" min="0" max="10" value="0" class="w-full accent-amber-500 cursor-pointer">
                    <div class="flex justify-between text-[10px] font-extrabold text-slate-400 mt-2">
                        <span>0 Days</span>
                        <span>5 Days</span>
                        <span>10 Days</span>
                    </div>
                </div>
            </div>

            {{-- Smart Route Option --}}
            <div class="p-5 bg-indigo-50/60 rounded-2xl border border-indigo-100 flex items-center justify-between mb-8">
                <div>
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-wider">AI Autonomous Agent</span>
                    <h4 class="font-black text-slate-900 text-sm">Auto-Route Optimization</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Izinkan AI memilih rute bypass teroptimum secara otomatis.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input id="route" type="checkbox" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            {{-- Run Button --}}
            <button id="runSimulation" data-url="{{ route('simulation.run') }}"
                    class="btn-animate w-full bg-gradient-to-r from-indigo-600 via-indigo-700 to-teal-600 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-wider shadow-lg shadow-indigo-600/20 flex items-center justify-center gap-3">
                <span class="text-xl">⚡</span>
                <span>Launch Digital Twin Simulation</span>
            </button>
        </div>

        {{-- SIMULATION RESULT OUTPUT CONTAINER --}}
        <div id="result" class="mt-8"></div>

    </div>

    {{-- SIMULATION FULL-SCREEN LOADING OVERLAY --}}
    <div id="simulationLoading" class="fixed inset-0 bg-slate-950/85 backdrop-blur-md hidden items-center justify-center z-[99999]">
        <div class="agri-dark-card p-8 sm:p-10 max-w-lg w-full text-center text-white relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-indigo-500 via-teal-400 to-emerald-400 animate-pulse"></div>

            <div class="w-16 h-16 rounded-2xl bg-indigo-500/20 border border-indigo-500/30 text-indigo-400 flex items-center justify-center text-3xl mx-auto mb-6 animate-pulse">
                🤖
            </div>

            <span class="text-[10px] font-black uppercase text-indigo-400 tracking-[0.3em]">AI Digital Twin Engine</span>
            <h2 class="text-2xl font-black mt-1">Executing Logistics Twin</h2>
            <p id="loadingText" class="text-xs text-slate-400 mt-2 font-medium">Synchronizing shipment telemetry...</p>

            <div class="mt-8">
                <div class="w-full h-3 bg-slate-800 rounded-full overflow-hidden p-0.5 border border-slate-700">
                    <div id="loadingBar" class="h-full bg-gradient-to-r from-indigo-500 via-teal-400 to-emerald-400 rounded-full transition-all duration-300" style="width: 0%;"></div>
                </div>
                <div class="flex justify-between items-center mt-3 text-xs font-mono">
                    <span class="text-slate-500">Progress</span>
                    <span id="loadingPercent" class="font-black text-indigo-400">0%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript Interactivity & Engine Logic --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Vehicle Selection Card Interaction
            const vehicleCards = document.querySelectorAll('.vehicle-card');
            const vehicleInput = document.getElementById('vehicle');

            vehicleCards.forEach(card => {
                card.addEventListener('click', function () {
                    vehicleCards.forEach(c => {
                        c.classList.remove('vehicle-selected');
                        c.querySelector('.vehicle-title').classList.remove('text-slate-900');
                        c.querySelector('.vehicle-title').classList.add('text-slate-700');
                    });

                    this.classList.add('vehicle-selected');
                    this.querySelector('.vehicle-title').classList.remove('text-slate-700');
                    this.querySelector('.vehicle-title').classList.add('text-slate-900');

                    vehicleInput.value = this.dataset.value;
                });
            });

            // Sliders Display
            const tempSlider = document.getElementById('temperature');
            const tempValue = document.getElementById('tempValue');
            if (tempSlider && tempValue) {
                tempSlider.addEventListener('input', () => tempValue.textContent = tempSlider.value + '°C');
            }

            const delaySlider = document.getElementById('delay');
            const delayValue = document.getElementById('delayValue');
            if (delaySlider && delayValue) {
                delaySlider.addEventListener('input', () => delayValue.textContent = delaySlider.value + ' Day');
            }

            // Search Filter List
            const searchInput = document.getElementById('searchShipment');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const keyword = this.value.toLowerCase();
                    document.querySelectorAll('.shipment-card').forEach(card => {
                        const text = card.textContent.toLowerCase();
                        card.style.display = text.includes(keyword) ? 'block' : 'none';
                    });
                });
            }

            // Shipment Selection & Digital Twin Sync
            const shipmentCards = document.querySelectorAll('.shipment-card');
            const placeholder = document.getElementById('shipmentPlaceholder');
            const preview = document.getElementById('shipmentPreview');
            const simulationPanel = document.getElementById('simulationPanel');

            shipmentCards.forEach(card => {
                card.addEventListener('click', function () {
                    shipmentCards.forEach(c => c.classList.remove('shipment-active'));
                    this.classList.add('shipment-active');

                    document.getElementById('selectedShipment').value = this.dataset.id;

                    if (placeholder) placeholder.classList.add('hidden');
                    if (preview) preview.classList.remove('hidden');
                    if (simulationPanel) simulationPanel.classList.remove('hidden');

                    document.getElementById('commodity').textContent = this.dataset.commodity;
                    document.getElementById('origin').textContent = this.dataset.origin;
                    document.getElementById('destination').textContent = this.dataset.destination;
                    document.getElementById('shipmentStatus').textContent = this.dataset.status;

                    const distance = parseInt(this.dataset.distance) || 0;
                    document.getElementById('distance').textContent = distance + ' km';
                    document.getElementById('eta').textContent = Math.max(1, Math.round(distance / 60)) + ' hrs';
                    document.getElementById('carbon').textContent = (distance * 0.08).toFixed(1) + ' kg CO₂';

                    updateTimeline(this.dataset.status);
                });
            });

            function updateTimeline(status) {
                status = (status || '').trim().toLowerCase();
                let progress = 0;

                switch (status) {
                    case 'harvested': progress = 0; break;
                    case 'packed': progress = 1; break;
                    case 'in transit': progress = 2; break;
                    case 'delivered': progress = 3; break;
                    default: progress = 0;
                }

                const steps = [
                    document.getElementById('stepHarvested'),
                    document.getElementById('stepPacked'),
                    document.getElementById('stepTransit'),
                    document.getElementById('stepDelivered')
                ];

                steps.forEach((step, idx) => {
                    if (step) {
                        if (idx <= progress) {
                            step.className = "w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-black border-2 border-white shadow-sm";
                        } else {
                            step.className = "w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-black border-2 border-white";
                        }
                    }
                });

                const progressLine = document.getElementById('progressLine');
                if (progressLine) {
                    progressLine.style.width = ["0%", "33%", "66%", "100%"][progress];
                }

                const badge = document.getElementById('timelineBadge');
                if (badge) badge.textContent = status.toUpperCase();
            }

            // Run Simulation Trigger
            const runBtn = document.getElementById('runSimulation');
            if (runBtn) {
                runBtn.addEventListener('click', async function () {
                    const selectedShipment = document.getElementById('selectedShipment').value;
                    if (!selectedShipment) {
                        alert("Pilih salah satu log pengiriman terlebih dahulu.");
                        return;
                    }

                    const overlay = document.getElementById('simulationLoading');
                    const bar = document.getElementById('loadingBar');
                    const percent = document.getElementById('loadingPercent');
                    const text = document.getElementById('loadingText');

                    overlay.classList.remove('hidden');
                    overlay.classList.add('flex');

                    const tasks = [
                        "Loading Telemetry Data", "Building Digital Twin Mesh", "Calculating Cold Chain Dynamics",
                        "Executing Carbon Model", "Predicting Shelf-Life", "Scenario Risk Engine", "Finalizing Strategy"
                    ];

                    let progress = 0;
                    const interval = setInterval(() => {
                        if (progress >= 95) {
                            clearInterval(interval);
                        } else {
                            progress += 5;
                            bar.style.width = progress + '%';
                            percent.textContent = progress + '%';
                            const taskIdx = Math.floor((progress / 100) * tasks.length);
                            if (tasks[taskIdx]) text.textContent = tasks[taskIdx];
                        }
                    }, 80);

                    try {
                        const response = await fetch(runBtn.dataset.url, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                shipment: selectedShipment,
                                vehicle: document.getElementById('vehicle').value,
                                temperature: document.getElementById('temperature').value,
                                delay: document.getElementById('delay').value,
                                route: document.getElementById('route').checked
                            })
                        });

                        const result = await response.json();

                        bar.style.width = '100%';
                        percent.textContent = '100%';
                        text.textContent = "Simulation Completed!";

                        setTimeout(() => {
                            overlay.classList.remove('flex');
                            overlay.classList.add('hidden');
                            showSimulationResult(result);
                            document.getElementById('result').scrollIntoView({ behavior: 'smooth' });
                        }, 400);

                    } catch (error) {
                        console.error(error);
                        alert("Gagal menjalankan simulasi.");
                        overlay.classList.remove('flex');
                        overlay.classList.add('hidden');
                    }
                });
            }

            // Render Output Result Cards (Dark Cyber Enterprise Result)
            function showSimulationResult(data) {
                const improvement = (data.before.risk_score - data.after.risk_score).toFixed(0);

                const html = `
                <div class="agri-dark-card p-6 sm:p-8 text-white relative overflow-hidden animate-card">
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-emerald-400 via-teal-400 to-indigo-500"></div>

                    {{-- Result Header --}}
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-slate-700/80">
                        <div>
                            <span class="px-3.5 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-[10px] font-black uppercase tracking-widest">
                                SCENARIO SIMULATION COMPLETED
                            </span>
                            <h2 class="text-3xl font-black text-white mt-2">Digital Twin Optimization Report</h2>
                            <p class="text-xs text-slate-400 mt-1">Skenario logistik telah disimulasikan secara utuh menggunakan AGRIFLOW DECISION ENGINE.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="px-4 py-2 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-center">
                                <p class="text-[10px] font-bold text-slate-400 uppercase">ESG Score</p>
                                <p class="text-lg font-black text-emerald-400">A+ Excellent</p>
                            </div>
                        </div>
                    </div>

                    {{-- Top Impact Metrics --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                        <div class="p-4 bg-slate-800/60 rounded-2xl border border-slate-700/80">
                            <p class="text-[10px] font-black uppercase text-slate-400">Risk Reduction</p>
                            <p class="text-2xl font-black text-emerald-400 mt-1">${improvement}%</p>
                        </div>
                        <div class="p-4 bg-slate-800/60 rounded-2xl border border-slate-700/80">
                            <p class="text-[10px] font-black uppercase text-slate-400">Eco Sustainability</p>
                            <p class="text-2xl font-black text-teal-300 mt-1">${data.after.sustainability_score}/100</p>
                        </div>
                        <div class="p-4 bg-slate-800/60 rounded-2xl border border-slate-700/80">
                            <p class="text-[10px] font-black uppercase text-slate-400">Carbon Difference</p>
                            <p class="text-2xl font-black text-emerald-400 mt-1">${(data.before.carbon - data.after.carbon).toFixed(1)} kg</p>
                        </div>
                        <div class="p-4 bg-slate-800/60 rounded-2xl border border-slate-700/80">
                            <p class="text-[10px] font-black uppercase text-slate-400">ETA Difference</p>
                            <p class="text-2xl font-black text-indigo-400 mt-1">${(data.before.duration - data.after.duration).toFixed(1)} hrs</p>
                        </div>
                    </div>

                    {{-- Before vs After Comparison Grid --}}
                    <div class="grid md:grid-cols-2 gap-6 mb-8">
                        {{-- Current / Before Card --}}
                        <div class="p-6 bg-slate-800/40 rounded-2xl border border-rose-500/30">
                            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-700">
                                <div>
                                    <span class="text-[10px] font-black uppercase text-rose-400 tracking-wider">Baseline Model</span>
                                    <h3 class="font-black text-white text-base">Current Operational Plan</h3>
                                </div>
                                <span class="text-xl">⚠️</span>
                            </div>

                            <div class="space-y-3 text-xs">
                                <div class="flex justify-between py-1 border-b border-slate-800">
                                    <span class="text-slate-400">Operational Risk:</span>
                                    <span class="font-black text-rose-400 text-sm">${data.before.risk_score}%</span>
                                </div>
                                <div class="flex justify-between py-1 border-b border-slate-800">
                                    <span class="text-slate-400">Carbon Emission:</span>
                                    <span class="font-bold text-white">${data.before.carbon} kg CO₂</span>
                                </div>
                                <div class="flex justify-between py-1 border-b border-slate-800">
                                    <span class="text-slate-400">Est. Transit Duration:</span>
                                    <span class="font-bold text-white">${data.before.duration} Hours</span>
                                </div>
                                <div class="flex justify-between py-1">
                                    <span class="text-slate-400">Fleet Type:</span>
                                    <span class="font-bold text-slate-300">${data.before.vehicle}</span>
                                </div>
                            </div>
                        </div>

                        {{-- AI Optimized Card --}}
                        <div class="p-6 bg-slate-800/40 rounded-2xl border border-emerald-500/30">
                            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-700">
                                <div>
                                    <span class="text-[10px] font-black uppercase text-emerald-400 tracking-wider">Neural Recommendation</span>
                                    <h3 class="font-black text-white text-base">AI Digital Twin Strategy</h3>
                                </div>
                                <span class="text-xl">🚀</span>
                            </div>

                            <div class="space-y-3 text-xs">
                                <div class="flex justify-between py-1 border-b border-slate-800">
                                    <span class="text-slate-400">Optimized Risk Level:</span>
                                    <span class="font-black text-emerald-400 text-sm">${data.after.risk_score}%</span>
                                </div>
                                <div class="flex justify-between py-1 border-b border-slate-800">
                                    <span class="text-slate-400">Carbon Emission:</span>
                                    <span class="font-bold text-white">${data.after.carbon} kg CO₂</span>
                                </div>
                                <div class="flex justify-between py-1 border-b border-slate-800">
                                    <span class="text-slate-400">Est. Transit Duration:</span>
                                    <span class="font-bold text-white">${data.after.duration} Hours</span>
                                </div>
                                <div class="flex justify-between py-1">
                                    <span class="text-slate-400">Fleet Selection:</span>
                                    <span class="font-bold text-emerald-300">${data.after.vehicle}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- AI Insights Bullet Points --}}
                    <div class="p-5 bg-slate-800/80 rounded-2xl border border-slate-700">
                        <p class="text-[10px] font-black uppercase text-indigo-400 tracking-widest mb-3">AI Actionable Recommendations</p>
                        <ul class="space-y-2 text-xs text-slate-300 font-medium">
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Pemilihan armada <strong class="text-white">${data.after.vehicle}</strong> memberikan rasio efisiensi risiko dan emisi karbon paling stabil.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Pengaturan suhu Cold-Chain disarankan berada pada tingkat <strong class="text-white">${document.getElementById("temperature").value}°C</strong> untuk menekan laju respirasi komoditas.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Estimasi waktu tiba (*ETA*) terjangkau dalam rentang <strong class="text-white">${data.after.duration} Jam</strong> tanpa memicu risiko kerusakan (*spoilage*).</span>
                            </li>
                        </ul>
                    </div>

                </div>`;

                document.getElementById('result').innerHTML = html;
            }
        });
    </script>
</x-app-layout>