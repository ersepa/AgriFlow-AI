<x-app-layout>
    {{-- Lucide Icons & Leaflet GIS Assets --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-enter {
            animation: fadeSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .agri-surface {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 8px 24px -8px rgba(15, 23, 42, 0.05);
            border-radius: 1.25rem;
        }

        .agri-modal-surface {
            background: #0f172a;
            border: 1px solid rgba(51, 65, 85, 0.85);
            box-shadow: 0 25px 50px -12px rgba(2, 6, 23, 0.65);
            border-radius: 1.5rem;
        }

        .kpi-card {
            transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -8px rgba(15, 23, 42, 0.09);
        }

        .shipment-card {
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .shipment-card.is-selected {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12), 0 12px 28px -8px rgba(15, 23, 42, 0.08);
        }

        /* Clean enterprise GIS styling for OSM tiles */
        .leaflet-container {
            z-index: 1 !important;
            font-family: inherit;
            background: #f1f5f9;
        }
        .leaflet-tile-pane {
            filter: saturate(0.82) contrast(1.03) brightness(1.01);
        }
        .leaflet-pane, .leaflet-control {
            z-index: 1 !important;
        }
        .leaflet-control-zoom {
            border: none !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12) !important;
            border-radius: 0.75rem !important;
            overflow: hidden;
        }
        .leaflet-control-zoom a {
            width: 34px !important;
            height: 34px !important;
            line-height: 34px !important;
            color: #1e293b !important;
            background: #ffffff !important;
            border-bottom: 1px solid #e2e8f0 !important;
            font-weight: 700;
        }
        .leaflet-control-zoom a:hover {
            background: #f8fafc !important;
            color: #4f46e5 !important;
        }
        .leaflet-control-attribution {
            font-size: 10px !important;
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(4px);
            border-top-left-radius: 0.5rem;
            padding: 2px 8px !important;
            color: #64748b !important;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 0.875rem;
            padding: 2px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.18);
            border: 1px solid #e2e8f0;
        }

        .route-marker {
            width: 38px;
            height: 38px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            border: 3px solid #ffffff;
            box-shadow: 0 8px 18px -4px rgba(15, 23, 42, 0.35);
            transition: transform 0.2s ease;
        }
        .route-marker:hover {
            transform: scale(1.08);
        }

        /* Custom subtle dark scrollbar for modals */
        .modal-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .modal-scroll::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.4);
        }
        .modal-scroll::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.45);
            border-radius: 9999px;
        }
        .modal-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.65);
        }
    </style>

    <div class="min-h-screen bg-slate-50/80 py-7 px-4 sm:px-6 lg:px-8 animate-enter w-full"
         x-data="{ loaded: false }"
         x-init="setTimeout(() => { loaded = true; if (window.map) window.map.invalidateSize(); }, 250)">

        <div class="max-w-7xl mx-auto space-y-6">

            {{-- 1. HERO HEADER --}}
            <section class="agri-surface relative overflow-hidden p-6 sm:p-8 bg-gradient-to-br from-white via-white to-indigo-50/40">
                {{-- Subtle background accents --}}
                <div class="pointer-events-none absolute -top-20 -right-20 w-72 h-72 rounded-full bg-teal-400/10 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 right-40 w-64 h-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 via-teal-500 to-indigo-600"></div>

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                    <div class="space-y-2.5 max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-700 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Mengevaluasi rute pengiriman yang tersedia</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-600 to-teal-500 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 shrink-0">
                                <i data-lucide="route" class="w-5 h-5"></i>
                            </div>
                            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                                Optimasi Rute AI
                            </h1>
                        </div>

                        <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                            Temukan rute pengiriman terbaik untuk menjaga kesegaran produk, mengurangi risiko operasional, dan mengelola emisi transportasi.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                        <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/90 border border-slate-200/90 text-xs font-medium text-slate-700 shadow-sm">
                            <i data-lucide="leaf" class="w-4 h-4 text-emerald-600"></i>
                            <span>Prioritas Kesegaran</span>
                        </div>
                        <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/90 border border-slate-200/90 text-xs font-medium text-slate-700 shadow-sm">
                            <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i>
                            <span>Geometri Rute Terverifikasi</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 2. SUMMARY KPI CARDS --}}
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Shipments --}}
                <div class="agri-surface kpi-card p-5 flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Pengiriman</p>
                            <p class="text-3xl font-bold text-slate-900 mt-1.5 tracking-tight">{{ $totalShipments ?? 0 }}</p>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                            <i data-lucide="package" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-3 pt-3 border-t border-slate-100">
                        Pengiriman aktif yang sedang dipantau
                    </p>
                </div>

                {{-- Freshness Safe --}}
                <div class="agri-surface kpi-card p-5 flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Kesegaran Aman</p>
                            <p class="text-3xl font-bold text-slate-900 mt-1.5 tracking-tight">{{ $freshnessSafeCount ?? 0 }}</p>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <p class="text-xs text-emerald-700/80 mt-3 pt-3 border-t border-slate-100">
                        Pengiriman dalam batas waktu aman
                    </p>
                </div>

                {{-- High Risk --}}
                <div class="agri-surface kpi-card p-5 flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-rose-700">Risiko Tinggi</p>
                            <p class="text-3xl font-bold text-slate-900 mt-1.5 tracking-tight">{{ $highCount ?? 0 }}</p>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <p class="text-xs text-rose-700/80 mt-3 pt-3 border-t border-slate-100">
                        Pengiriman yang memerlukan perhatian segera
                    </p>
                </div>

                {{-- Average Route Score --}}
                <div class="agri-surface kpi-card p-5 flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-blue-700">Rata-rata Skor Rute</p>
                            <p class="text-3xl font-bold text-slate-900 mt-1.5 tracking-tight">
                                {{ $averageRouteScore ?? 0 }}<span class="text-base font-semibold text-slate-400 ml-0.5">/100</span>
                            </p>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <i data-lucide="gauge" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-3 pt-3 border-t border-slate-100">
                        Penilaian keseluruhan kesegaran & risiko rute
                    </p>
                </div>
            </section>

            {{-- 3. SHIPMENT ROUTE MAP SECTION --}}
            <section id="routeMapSection" class="agri-surface p-5 sm:p-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center">
                                <i data-lucide="map" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">
                                    Peta Rute Pengiriman
                                </h2>
                                <p class="text-xs sm:text-sm text-slate-500">
                                    Lihat lokasi pengiriman dan rute perjalanan yang tersedia.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200/80 text-slate-700 text-xs font-semibold">
                            <i data-lucide="truck" class="w-3.5 h-3.5 text-indigo-600"></i>
                            <span>{{ $totalShipments ?? 0 }} Pengiriman</span>
                        </span>

                        {{-- Truthful Dynamic Map Connection Status --}}
                        <span id="mapStatusBadge"
                              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 text-xs font-semibold">
                            <span id="mapStatusDot" class="w-2 h-2 rounded-full bg-slate-400"></span>
                            <span id="mapStatusText">Memuat Peta...</span>
                        </span>
                    </div>
                </div>

                {{-- Compact Legend & Selected Route Controls --}}
                <div class="flex flex-wrap items-center justify-between gap-3 mb-3 px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200/80 text-xs">
                    <div class="flex flex-wrap items-center gap-4 text-slate-600 font-medium">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-emerald-200"></span>
                            Asal (Kebun)
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-rose-500 ring-2 ring-rose-200"></span>
                            Tujuan (Gudang)
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-indigo-600 ring-2 ring-indigo-200"></span>
                            Truk Aktif
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-5 h-1 rounded-full bg-indigo-500"></span>
                            Rute Jalan
                        </span>
                    </div>

                    <button type="button"
                            onclick="recenterActiveRoute()"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-semibold transition-colors">
                        <i data-lucide="maximize-2" class="w-3.5 h-3.5 text-indigo-600"></i>
                        <span>Sesuaikan Rute</span>
                    </button>
                </div>

                {{-- Map Canvas Wrapper --}}
                <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-inner bg-slate-100">
                    <div id="map" class="h-[460px] sm:h-[500px] w-full"></div>

                    {{-- Selected Shipment Info Overlay (Top-Left) --}}
                    <div id="mapSelectedInfo"
                         class="hidden absolute top-3 left-3 z-[400] max-w-xs sm:max-w-sm bg-white/95 backdrop-blur-md border border-slate-200/90 rounded-xl shadow-lg px-3.5 py-3 pointer-events-auto">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600">Pengiriman Terpilih</span>
                            <span id="mapSelectedFeasibility" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">—</span>
                        </div>
                        <p id="mapSelectedCommodity" class="text-sm font-bold text-slate-900 truncate">—</p>
                        <div class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-600">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                            <span id="mapSelectedOrigin" class="truncate">—</span>
                            <i data-lucide="arrow-right" class="w-3 h-3 text-slate-400 shrink-0"></i>
                            <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                            <span id="mapSelectedDest" class="truncate">—</span>
                        </div>
                    </div>

                    {{-- Map Loading Overlay --}}
                    <div id="mapLoadingOverlay"
                         class="hidden absolute inset-0 z-[500] bg-slate-900/20 backdrop-blur-[2px] flex items-center justify-center">
                        <div class="bg-white rounded-xl shadow-xl border border-slate-200 px-5 py-3.5 flex items-center gap-3">
                            <div class="w-5 h-5 border-2 border-indigo-200 border-t-indigo-600 rounded-full animate-spin"></div>
                            <span class="text-sm font-semibold text-slate-800">Memuat rute jalan...</span>
                        </div>
                    </div>

                    {{-- Map Error Banner (Non-destructive, truthful) --}}
                    <div id="mapErrorBanner"
                         class="hidden absolute bottom-3 left-3 right-14 z-[500] bg-rose-50/95 backdrop-blur-md border border-rose-200 rounded-xl shadow-lg p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-start gap-2.5">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
                            <div>
                                <p class="text-xs font-bold text-rose-900">Geometri Rute Tidak Tersedia</p>
                                <p id="mapErrorText" class="text-xs text-rose-700 mt-0.5">
                                    Layanan rute tidak dapat mengembalikan jalur jalan untuk pengiriman ini.
                                </p>
                            </div>
                        </div>
                        <button type="button"
                                onclick="retryActiveRoute()"
                                class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shrink-0 transition-colors">
                            Coba Lagi
                        </button>
                    </div>

                    {{-- Map Empty State --}}
                    @if($results->isEmpty())
                    <div id="mapEmptyState"
                         class="absolute inset-0 z-[450] bg-slate-50/90 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-slate-200/80 text-slate-500 flex items-center justify-center mb-3">
                            <i data-lucide="map-pin-off" class="w-6 h-6"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-800">Tidak Ada Pengiriman Aktif untuk Ditampilkan</p>
                        <p class="text-xs text-slate-500 mt-1 max-w-sm">
                            Tambahkan pengiriman dengan koordinat asal dan tujuan yang valid untuk melihat rute perjalanannya di sini.
                        </p>
                    </div>
                    @endif
                </div>
            </section>

            {{-- 4. SHIPMENT CARDS LIST --}}
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Pengiriman yang Dipantau</h2>
                        <p class="text-xs text-slate-500">Pilih pengiriman untuk melihat rute, analisis kesegaran, atau penjelasan risiko AI.</p>
                    </div>
                </div>

                @forelse($results as $index => $result)
                    @php
                        $priorityLower = strtolower($result['priority_level'] ?? '');
                        $priorityBadge = str_contains($priorityLower, 'high') || str_contains($priorityLower, 'critical')
                            ? 'bg-rose-50 text-rose-700 border-rose-200'
                            : (str_contains($priorityLower, 'moderate') || str_contains($priorityLower, 'medium')
                                ? 'bg-amber-50 text-amber-700 border-amber-200'
                                : 'bg-emerald-50 text-emerald-700 border-emerald-200');

                        $priorityLabel = str_contains($priorityLower, 'critical')
                            ? 'Kritis'
                            : (str_contains($priorityLower, 'high')
                                ? 'Prioritas Tinggi'
                                : (str_contains($priorityLower, 'moderate') || str_contains($priorityLower, 'medium')
                                    ? 'Prioritas Sedang'
                                    : (str_contains($priorityLower, 'low') ? 'Prioritas Rendah' : ($result['priority_level'] ?? 'Standar'))));

                        $feasibilityRaw = $result['freshness_feasibility'] ?? 'Unknown';
                        $feasibilityLabel = match ($feasibilityRaw) {
                            'Safe' => 'Aman Dikirim',
                            'Tight' => 'Waktu Pengiriman Terbatas',
                            'Breach' => 'Melewati Batas Aman',
                            'Unavailable' => 'Belum Tersedia',
                            default => $feasibilityRaw,
                        };
                        $feasibilityBadge = match ($feasibilityRaw) {
                            'Safe' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'Tight' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'Breach' => 'bg-rose-50 text-rose-700 border-rose-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                        };

                        $riskScore = $result['risk_score'] ?? 0;
                        $riskColor = $riskScore >= 70 ? 'text-rose-600' : ($riskScore >= 40 ? 'text-amber-600' : 'text-emerald-600');
                        $riskSeverityIndo = match (strtolower((string) ($result['risk_severity'] ?? ''))) {
                            'critical' => 'tingkat kritis',
                            'high' => 'tingkat tinggi',
                            'moderate', 'medium' => 'tingkat sedang',
                            'low' => 'tingkat rendah',
                            default => strtolower((string) ($result['risk_severity'] ?? '')),
                        };

                        $qualityVal = $result['quality_at_arrival'] ?? null;
                        $qualityColor = $qualityVal === null ? 'text-slate-500' : ($qualityVal >= 75 ? 'text-emerald-600' : ($qualityVal >= 50 ? 'text-amber-600' : 'text-rose-600'));
                    @endphp

                    <div class="shipment-card agri-surface p-5 sm:p-6"
                         data-shipment-id="{{ $result['shipment']->id }}"
                         data-origin-lat="{{ $result['origin_lat'] }}"
                         data-origin-lng="{{ $result['origin_lng'] }}"
                         data-dest-lat="{{ $result['destination_lat'] }}"
                         data-dest-lng="{{ $result['destination_lng'] }}"
                         data-commodity="{{ ucfirst($result['commodity'] ?? 'Pengiriman') }}"
                         data-origin="{{ $result['origin'] ?? 'Tidak diketahui' }}"
                         data-destination="{{ $result['destination'] ?? 'Tidak diketahui' }}"
                         data-priority="{{ $result['priority_level'] ?? 'Normal' }}"
                         data-route-score="{{ $result['freshness_route_score'] ?? '' }}"
                         data-quality="{{ $result['quality_at_arrival'] ?? '' }}"
                         data-quality-status="{{ $result['quality_status'] ?? '' }}"
                         data-risk-score="{{ $result['risk_score'] ?? '' }}"
                         data-risk-severity="{{ $result['risk_severity'] ?? '' }}"
                         data-feasibility="{{ $feasibilityRaw }}"
                         data-transit-margin="{{ $result['transit_margin_hours'] ?? '' }}"
                         data-delay-tolerance="{{ $result['delay_tolerance_hours'] ?? '' }}">

                        {{-- Top Row: Identity & Actions --}}
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                            <div class="space-y-1.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-bold">
                                        #{{ $index + 1 }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-[11px] font-semibold {{ $priorityBadge }}">
                                        {{ $priorityLabel }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-[11px] font-semibold {{ $feasibilityBadge }}">
                                        {{ $feasibilityLabel }}
                                    </span>
                                </div>

                                <h3 class="text-xl font-bold text-slate-900 tracking-tight">
                                    {{ ucfirst($result['commodity'] ?? 'Komoditas') }}
                                </h3>

                                <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-slate-600 font-medium">
                                    <span class="inline-flex items-center gap-1.5">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        {{ $result['origin'] ?? 'Asal' }}
                                    </span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span class="inline-flex items-center gap-1.5">
                                        <i data-lucide="warehouse" class="w-3.5 h-3.5 text-rose-500"></i>
                                        {{ $result['destination'] ?? 'Tujuan' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                                <button type="button"
                                        onclick="updateMap(this.closest('.shipment-card'), this)"
                                        class="route-btn inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-sm transition-colors">
                                    <span class="route-text inline-flex items-center gap-1.5">
                                        <i data-lucide="map-pinned" class="w-3.5 h-3.5"></i>
                                        <span>Lihat Rute</span>
                                    </span>
                                </button>

                                <button type="button"
                                        onclick="showFreshnessRoutes({{ $result['shipment']->id }})"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition-colors">
                                    <i data-lucide="leaf" class="w-3.5 h-3.5"></i>
                                    <span>Analisis Kesegaran</span>
                                </button>

                                <button type="button"
                                        onclick="showAIExplain({{ $result['shipment']->id }})"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition-colors">
                                    <i data-lucide="brain-circuit" class="w-3.5 h-3.5 text-indigo-300"></i>
                                    <span>Penjelasan AI</span>
                                </button>
                            </div>
                        </div>

                        {{-- Core Metrics Row --}}
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 py-4">
                            <div class="rounded-xl bg-slate-50 border border-slate-200/70 p-3.5">
                                <div class="flex items-center justify-between text-slate-500 mb-1">
                                    <span class="text-[11px] font-semibold">Skor Kualitas Rute</span>
                                    <i data-lucide="gauge" class="w-3.5 h-3.5 text-indigo-500"></i>
                                </div>
                                <p class="text-xl font-bold text-slate-900">
                                    {{ $result['freshness_route_score'] ?? '—' }}<span class="text-xs font-medium text-slate-400">/100</span>
                                </p>
                            </div>

                            <div class="rounded-xl bg-slate-50 border border-slate-200/70 p-3.5">
                                <div class="flex items-center justify-between text-slate-500 mb-1">
                                    <span class="text-[11px] font-semibold">Perkiraan Kualitas Produk</span>
                                    <i data-lucide="leaf" class="w-3.5 h-3.5 text-emerald-500"></i>
                                </div>
                                <p class="text-xl font-bold {{ $qualityColor }}">
                                    {{ $qualityVal ?? '—' }}<span class="text-xs font-medium text-slate-400">/100</span>
                                </p>
                                @if(!empty($result['quality_status']))
                                    <p class="text-[11px] text-slate-500 mt-0.5 truncate">{{ $result['quality_status'] }}</p>
                                @endif
                            </div>

                            <div class="rounded-xl bg-slate-50 border border-slate-200/70 p-3.5">
                                <div class="flex items-center justify-between text-slate-500 mb-1">
                                    <span class="text-[11px] font-semibold">Risiko Pengiriman</span>
                                    <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-rose-500"></i>
                                </div>
                                <p class="text-xl font-bold {{ $riskColor }}">
                                    {{ $result['risk_score'] ?? '—' }}<span class="text-xs font-medium text-slate-400">/100</span>
                                </p>
                                @if(!empty($result['risk_severity']))
                                    <p class="text-[11px] text-slate-500 mt-0.5 capitalize">{{ $riskSeverityIndo }}</p>
                                @endif
                            </div>

                            <div class="rounded-xl bg-slate-50 border border-slate-200/70 p-3.5">
                                <div class="flex items-center justify-between text-slate-500 mb-1">
                                    <span class="text-[11px] font-semibold">Sisa Waktu Aman</span>
                                    <i data-lucide="timer" class="w-3.5 h-3.5 text-teal-500"></i>
                                </div>
                                <p class="text-xl font-bold text-slate-900">
                                    {{ $result['transit_margin_hours'] !== null ? number_format($result['transit_margin_hours'], 1) . ' jam' : 'Belum tersedia' }}
                                </p>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    Toleransi tunda: {{ $result['delay_tolerance_hours'] !== null ? number_format($result['delay_tolerance_hours'], 1) . ' jam' : 'Tidak ada' }}
                                </p>
                            </div>
                        </div>

                        {{-- Concise Route Note --}}
                        @if(!empty($result['route_assessment']['recommendation_reason']))
                            <div class="flex items-start gap-2.5 px-3.5 py-2.5 rounded-xl bg-indigo-50/50 border border-indigo-100 text-xs text-slate-700 leading-relaxed">
                                <i data-lucide="info" class="w-4 h-4 text-indigo-600 shrink-0 mt-0.5"></i>
                                <p>{{ $result['route_assessment']['recommendation_reason'] }}</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="agri-surface p-10 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="package-x" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Tidak Ada Pengiriman Ditemukan</h3>
                        <p class="text-sm text-slate-500 mt-1">Saat ini belum ada pengiriman aktif yang tersedia untuk optimasi rute.</p>
                    </div>
                @endforelse

                {{-- PAGINATION --}}
                @if($results->total() > 0)
                    <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-200/80">
                        <p class="text-xs text-slate-500 font-medium">
                            Halaman <span class="text-slate-900 font-bold">{{ $results->currentPage() }}</span> dari <span class="text-slate-900 font-bold">{{ $results->lastPage() }}</span>
                            • Menampilkan <span class="text-indigo-600 font-bold">{{ $results->firstItem() }}–{{ $results->lastItem() }}</span> dari <span class="text-slate-900 font-bold">{{ $results->total() }}</span> pengiriman
                        </p>
                        <div class="custom-pagination">
                            {{ $results->links() }}
                        </div>
                    </div>
                @endif
            </section>

        </div>
    </div>

    {{-- ===================================================================== --}}
    {{-- MODAL 1: PENJELASAN PENGIRIMAN AI                                     --}}
    {{-- ===================================================================== --}}
    <div id="aiModal" class="hidden fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-[9999] flex items-center justify-center p-4">
        <div class="agri-modal-surface max-w-2xl w-full max-h-[90vh] flex flex-col text-white relative overflow-hidden">
            <div class="h-1 w-full bg-gradient-to-r from-indigo-500 via-teal-400 to-emerald-400 shrink-0"></div>

            {{-- Header --}}
            <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-800 shrink-0">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/15 border border-indigo-400/30 text-indigo-300 flex items-center justify-center shrink-0">
                        <i data-lucide="brain-circuit" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight">Penjelasan Pengiriman AI</h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Pahami mengapa AgriFlow memberikan penilaian risiko saat ini pada pengiriman tersebut.
                        </p>
                    </div>
                </div>
                <button type="button"
                        onclick="closeAIExplain()"
                        class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors shrink-0">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Scrollable Body --}}
            <div class="p-6 overflow-y-auto modal-scroll flex-1">
                {{-- Loading State --}}
                <div id="aiLoading" class="py-12 text-center">
                    <div class="w-10 h-10 border-4 border-indigo-900 border-t-indigo-400 rounded-full animate-spin mx-auto"></div>
                    <p class="text-sm font-semibold text-white mt-4">Menyiapkan penjelasan pengiriman...</p>
                    <p class="text-xs text-slate-400 mt-1">Meninjau kualitas produk, risiko pengiriman, dan sisa waktu aman.</p>
                </div>

                {{-- Error State --}}
                <div id="aiError" class="hidden py-10 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-400 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="alert-circle" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm font-bold text-white">Gagal Memuat Penjelasan</p>
                    <p id="aiErrorMessage" class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                        Tidak dapat mengambil penjelasan AI untuk pengiriman ini. Silakan coba lagi.
                    </p>
                </div>

                {{-- Result View --}}
                <div id="aiResult" class="hidden space-y-5">
                    {{-- 1. Clear Decision Status Banner --}}
                    <div id="aiStatusBanner" class="rounded-2xl border p-4 sm:p-5 bg-slate-800/60 border-slate-700">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                            <div class="inline-flex items-center gap-2">
                                <i data-lucide="shield-alert" class="w-4 h-4" id="aiStatusIcon"></i>
                                <span id="aiStatusBadge" class="text-xs font-bold uppercase tracking-wider">Penilaian Risiko</span>
                            </div>
                            <span id="aiShipmentMeta" class="text-xs text-slate-400 font-medium"></span>
                        </div>
                        <p id="aiStatusDescription" class="text-sm text-slate-200 leading-relaxed"></p>
                    </div>

                    {{-- 2. Actionable Recommendation ("Apa yang Harus Dilakukan?") --}}
                    <div class="rounded-2xl bg-indigo-500/10 border border-indigo-400/25 p-4 sm:p-5">
                        <div class="flex items-center gap-2 text-indigo-300 mb-2">
                            <i data-lucide="lightbulb" class="w-4 h-4"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider">Apa yang Harus Anda Lakukan?</h3>
                        </div>
                        <p id="aiRecommendation" class="text-base font-bold text-white leading-snug">-</p>
                        <p id="aiRecommendationSub" class="text-xs text-slate-300 mt-1.5 leading-relaxed hidden"></p>
                    </div>

                    {{-- 3. Visual Factors ("Mengapa Pengiriman Ini Ditandai?") --}}
                    <div id="aiFactorsSection" class="hidden space-y-2.5">
                        <div class="flex items-center gap-2 text-slate-300">
                            <i data-lucide="clipboard-list" class="w-4 h-4 text-teal-400"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider">Mengapa Pengiriman Ini Ditandai?</h3>
                        </div>
                        <div id="aiFactorsGrid" class="grid grid-cols-1 sm:grid-cols-3 gap-3"></div>
                    </div>

                    {{-- 4. Compact Decision Flow --}}
                    <div class="rounded-2xl bg-slate-900/70 border border-slate-800 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">Bagaimana AgriFlow Mengambil Keputusan Ini</p>
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
                            <div class="rounded-xl bg-slate-800/70 border border-slate-700/70 p-3">
                                <div class="flex items-center gap-1.5 text-teal-400 mb-1">
                                    <i data-lucide="package" class="w-3.5 h-3.5"></i>
                                    <span class="text-[10px] font-bold uppercase">1. Kondisi</span>
                                </div>
                                <p id="aiFlowStep1" class="text-xs text-slate-300 leading-snug">Metrik pengiriman diperiksa</p>
                            </div>
                            <div class="rounded-xl bg-slate-800/70 border border-slate-700/70 p-3">
                                <div class="flex items-center gap-1.5 text-amber-400 mb-1">
                                    <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                                    <span class="text-[10px] font-bold uppercase">2. Evaluasi</span>
                                </div>
                                <p id="aiFlowStep2" class="text-xs text-slate-300 leading-snug">Kualitas & risiko dinilai</p>
                            </div>
                            <div class="rounded-xl bg-slate-800/70 border border-slate-700/70 p-3">
                                <div class="flex items-center gap-1.5 text-rose-400 mb-1">
                                    <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                                    <span class="text-[10px] font-bold uppercase">3. Penilaian</span>
                                </div>
                                <p id="aiFlowStep3" class="text-xs text-slate-300 leading-snug">Tingkat risiko ditetapkan</p>
                            </div>
                            <div class="rounded-xl bg-slate-800/70 border border-slate-700/70 p-3">
                                <div class="flex items-center gap-1.5 text-indigo-400 mb-1">
                                    <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i>
                                    <span class="text-[10px] font-bold uppercase">4. Tindakan</span>
                                </div>
                                <p id="aiFlowStep4" class="text-xs text-slate-300 leading-snug">Langkah berikutnya disarankan</p>
                            </div>
                        </div>
                    </div>

                    {{-- 5. Concise Explanation Summary --}}
                    <div class="rounded-2xl bg-slate-800/50 border border-slate-700/80 p-4">
                        <div class="flex items-center gap-2 text-slate-300 mb-1.5">
                            <i data-lucide="info" class="w-4 h-4 text-sky-400"></i>
                            <h3 class="text-xs font-bold uppercase tracking-wider">Ringkasan Penjelasan</h3>
                        </div>
                        <p id="aiConclusion" class="text-xs sm:text-sm text-slate-300 leading-relaxed">-</p>
                        {{-- Preserve hidden element for backward compatibility if inspected --}}
                        <p id="aiReason" class="hidden">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================================================================== --}}
    {{-- MODAL 2: ANALISIS RUTE KESEGARAN                                      --}}
    {{-- ===================================================================== --}}
    <div id="freshnessRouteModal" class="hidden fixed inset-0 bg-slate-950/75 backdrop-blur-sm z-[10000] flex items-center justify-center p-4">
        <div class="agri-modal-surface max-w-5xl w-full max-h-[90vh] flex flex-col text-white relative overflow-hidden">
            <div class="h-1 w-full bg-gradient-to-r from-emerald-400 via-teal-400 to-indigo-500 shrink-0"></div>

            {{-- Header --}}
            <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-800 shrink-0">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-500/15 border border-teal-400/30 text-teal-300 flex items-center justify-center shrink-0">
                        <i data-lucide="route" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 id="freshnessDecisionHeading" class="text-lg sm:text-xl font-bold text-white tracking-tight">
                            Analisis Rute Kesegaran
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Lihat rute pengiriman mana yang memberikan kondisi terbaik untuk menjaga kesegaran produk.
                        </p>
                    </div>
                </div>

                <button type="button"
                        onclick="closeFreshnessRoutes()"
                        class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors shrink-0">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Scrollable Body --}}
            <div class="p-6 overflow-y-auto modal-scroll flex-1">
                {{-- Loading State --}}
                <div id="freshnessRouteLoading" class="py-14 text-center">
                    <div class="w-10 h-10 border-4 border-teal-900 border-t-teal-400 rounded-full animate-spin mx-auto"></div>
                    <p class="text-sm font-semibold text-white mt-4">Mengevaluasi rute pengiriman yang tersedia...</p>
                    <p class="text-xs text-slate-400 mt-1">
                        Membandingkan kesegaran produk, risiko pengiriman, waktu tempuh, dan emisi karbon.
                    </p>
                </div>

                {{-- Error State --}}
                <div id="freshnessRouteError" class="hidden py-12 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-400 flex items-center justify-center mx-auto mb-3">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm font-bold text-white">Gagal Menganalisis Rute</p>
                    <p class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                        Layanan rute tidak dapat mengevaluasi kandidat rute untuk pengiriman ini. Periksa konfigurasi penyedia rute dan coba lagi.
                    </p>
                </div>

                {{-- Result State --}}
                <div id="freshnessRouteResult" class="hidden space-y-6">
                    {{-- A. Main Decision Summary --}}
                    <div id="freshnessSummaryCard" class="rounded-2xl border p-5 sm:p-6 bg-slate-800/60 border-slate-700">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <div class="inline-flex items-center gap-2">
                                <span id="freshnessDecisionPill" class="px-3 py-1 rounded-full text-xs font-bold border">
                                    Mengevaluasi
                                </span>
                            </div>
                            <span id="freshnessRecommendedLabelCaption" class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                Rute Direkomendasikan
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="rounded-xl bg-slate-900/60 border border-slate-700/70 p-4">
                                <p class="text-[11px] font-semibold text-slate-400">Opsi Terbaik</p>
                                <p id="freshnessRecommendedLabel" class="text-lg font-bold text-white mt-1">-</p>
                            </div>
                            <div class="rounded-xl bg-slate-900/60 border border-slate-700/70 p-4">
                                <p class="text-[11px] font-semibold text-slate-400">Skor Kualitas Rute</p>
                                <p id="freshnessRecommendedScore" class="text-lg font-bold text-sky-400 mt-1">-</p>
                            </div>
                            <div class="rounded-xl bg-slate-900/60 border border-slate-700/70 p-4">
                                <p class="text-[11px] font-semibold text-slate-400">Kondisi Pengiriman</p>
                                <p id="freshnessRecommendedFeasibility" class="text-lg font-bold mt-1">-</p>
                            </div>
                        </div>

                        <p id="freshnessRouteReason" class="text-xs sm:text-sm text-slate-300 leading-relaxed mt-4 pt-4 border-t border-slate-700/70"></p>
                    </div>

                    {{-- B. Operational Recommendation Panel --}}
                    <div id="freshnessRequiredActionWrap" class="hidden rounded-2xl border border-rose-500/35 bg-rose-500/10 p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-rose-500/20 text-rose-300 flex items-center justify-center shrink-0">
                                <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-rose-300">Rekomendasi Operasional</p>
                                <p id="freshnessRequiredAction" class="text-sm font-semibold text-rose-100 mt-1 leading-relaxed"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Provider Notice (e.g., ORS 100km alternative limit) --}}
                    <div id="freshnessRouteNotice" class="hidden rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-xs text-amber-200 leading-relaxed flex items-start gap-2.5">
                        <i data-lucide="info" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                        <span id="freshnessRouteNoticeText"></span>
                    </div>

                    {{-- C. Route Alternatives --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-bold text-white">Pilihan Rute Tersedia</h3>
                            <span id="freshnessCandidateCount" class="text-xs text-slate-400"></span>
                        </div>
                        <div id="freshnessRouteCandidates" class="grid grid-cols-1 lg:grid-cols-2 gap-4"></div>
                    </div>

                    {{-- D. Ranking Weights (Visual Progress Bars) --}}
                    <div class="rounded-2xl bg-slate-900/70 border border-slate-800 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300">Cara Penilaian Rute</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Setiap rute dinilai menggunakan lima kriteria tetap dengan total bobot 100%.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-200">Kesegaran</span>
                                    <span class="font-bold text-emerald-400">40%</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-slate-800 overflow-hidden">
                                    <div class="h-full bg-emerald-400 rounded-full" style="width: 40%"></div>
                                </div>
                                <p class="text-[11px] text-slate-400">Kualitas produk saat tiba</p>
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-200">Perlindungan Risiko</span>
                                    <span class="font-bold text-rose-400">25%</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-slate-800 overflow-hidden">
                                    <div class="h-full bg-rose-400 rounded-full" style="width: 25%"></div>
                                </div>
                                <p class="text-[11px] text-slate-400">Paparan kerusakan lebih rendah</p>
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-200">Waktu Cadangan</span>
                                    <span class="font-bold text-teal-400">15%</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-slate-800 overflow-hidden">
                                    <div class="h-full bg-teal-400 rounded-full" style="width: 15%"></div>
                                </div>
                                <p class="text-[11px] text-slate-400">Sisa batas waktu aman</p>
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-200">Durasi</span>
                                    <span class="font-bold text-indigo-400">10%</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-slate-800 overflow-hidden">
                                    <div class="h-full bg-indigo-400 rounded-full" style="width: 10%"></div>
                                </div>
                                <p class="text-[11px] text-slate-400">Total waktu perjalanan</p>
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-slate-200">Karbon</span>
                                    <span class="font-bold text-sky-400">10%</span>
                                </div>
                                <div class="h-1.5 w-full rounded-full bg-slate-800 overflow-hidden">
                                    <div class="h-full bg-sky-400 rounded-full" style="width: 10%"></div>
                                </div>
                                <p class="text-[11px] text-slate-400">Emisi CO₂ lebih rendah</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let map = null;
let markerOrigin = null;
let markerDestination = null;
let truck = null;
let routeLine = null;
let routeFlowLine = null;
let animationId = null;
let dashIntervalId = null;
let activeShipmentCard = null;
let activeFreshnessShipmentId = null;
let latestFreshnessRouteCandidates = [];

const SVG_ICONS = {
    origin: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 20h10"/><path d="M10 20c5.5-2.5.8-6.4 3-10"/><path d="M9.5 9.4c1.1.8 1.8 2.2 2.3 3.7-2 .4-3.5.4-4.8-.3-1.2-.6-2.3-1.9-3-4.2 2.8-.5 4.4 0 5.5.8z"/><path d="M14.1 6a7 7 0 0 0-1.1 4c1.9-.1 3.3-.6 4.3-1.4 1-1 1.6-2.3 1.7-4.6-2.7.1-4 1-4.9 2z"/></svg>`,
    destination: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z"/><path d="M6 18h12"/><path d="M6 14h12"/><rect width="12" height="12" x="6" y="10"/></svg>`,
    truck: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>`
};

function renderLucideIcons() {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

function setMapStatus(state, text) {
    const badge = document.getElementById('mapStatusBadge');
    const dot = document.getElementById('mapStatusDot');
    const label = document.getElementById('mapStatusText');
    if (!badge || !dot || !label) return;

    badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-semibold';
    dot.className = 'w-2 h-2 rounded-full';

    if (state === 'ready') {
        badge.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-700');
        dot.classList.add('bg-emerald-500');
    } else if (state === 'loading') {
        badge.classList.add('bg-indigo-50', 'border-indigo-200', 'text-indigo-700');
        dot.classList.add('bg-indigo-500', 'animate-pulse');
    } else if (state === 'error') {
        badge.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-700');
        dot.classList.add('bg-rose-500');
    } else {
        badge.classList.add('bg-slate-100', 'border-slate-200', 'text-slate-600');
        dot.classList.add('bg-slate-400');
    }

    label.innerText = text;
}

function createMarker(icon, color) {
    return L.divIcon({
        className: '',
        html: `<div class="route-marker" style="background:${color}">${icon}</div>`,
        iconSize: [38, 38],
        iconAnchor: [19, 19],
        popupAnchor: [0, -20]
    });
}

function initLeafletMap() {
    const mapContainer = document.getElementById('map');
    if (!mapContainer) return;

    if (map) {
        map.remove();
        map = null;
    }

    map = L.map('map', {
        zoomControl: false,
        attributionControl: true
    }).setView([-6.2, 106.816666], 10);

    window.map = map;

    const osmLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>'
    });

    let switchedToFallback = false;
    let tileErrorCount = 0;

    osmLayer.on('tileerror', () => {
        tileErrorCount++;
        if (!switchedToFallback && tileErrorCount >= 4) {
            switchedToFallback = true;
            map.removeLayer(osmLayer);
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: 'Tiles &copy; Esri'
            }).addTo(map);
        }
    });

    osmLayer.addTo(map);

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    markerOrigin = L.marker([0, 0], {
        icon: createMarker(SVG_ICONS.origin, '#10b981')
    });

    markerDestination = L.marker([0, 0], {
        icon: createMarker(SVG_ICONS.destination, '#ef4444')
    });

    truck = L.marker([0, 0], {
        icon: createMarker(SVG_ICONS.truck, '#4f46e5'),
        zIndexOffset: 500
    });

    routeLine = L.polyline([], {
        color: '#4f46e5',
        weight: 5,
        opacity: 0.35,
        lineCap: 'round',
        lineJoin: 'round'
    }).addTo(map);

    routeFlowLine = L.polyline([], {
        color: '#4f46e5',
        weight: 3,
        opacity: 0.95,
        lineCap: 'round',
        lineJoin: 'round',
        dashArray: '10 10'
    }).addTo(map);

    if (dashIntervalId) clearInterval(dashIntervalId);
    let dashOffset = 0;
    dashIntervalId = setInterval(() => {
        if (routeFlowLine && routeFlowLine.getLatLngs().length > 1) {
            dashOffset = (dashOffset - 1) % 20;
            routeFlowLine.setStyle({ dashOffset: String(dashOffset) });
        }
    }, 55);

    if (window.ResizeObserver) {
        const ro = new ResizeObserver(() => {
            if (map) map.invalidateSize();
        });
        ro.observe(mapContainer);
    }

    setTimeout(() => {
        if (map) map.invalidateSize();
    }, 300);
}

function isValidLatLng(lat, lng) {
    return Number.isFinite(lat) && Number.isFinite(lng) && Math.abs(lat) <= 90 && Math.abs(lng) <= 180;
}

function updateSelectedInfoPanel(element) {
    const panel = document.getElementById('mapSelectedInfo');
    if (!panel || !element) return;

    const commodity = element.getAttribute('data-commodity') || 'Pengiriman';
    const origin = element.getAttribute('data-origin') || 'Asal';
    const destination = element.getAttribute('data-destination') || 'Tujuan';
    const feasibility = element.getAttribute('data-feasibility') || '';

    document.getElementById('mapSelectedCommodity').innerText = commodity;
    document.getElementById('mapSelectedOrigin').innerText = origin;
    document.getElementById('mapSelectedDest').innerText = destination;

    const pill = document.getElementById('mapSelectedFeasibility');
    const mapped = mapFeasibilityDisplay(feasibility);
    pill.innerText = mapped.shortLabel;
    pill.className = `text-[10px] font-bold px-2 py-0.5 rounded-full ${mapped.lightBadge}`;

    panel.classList.remove('hidden');
}

function highlightShipmentCard(element) {
    document.querySelectorAll('.shipment-card').forEach(card => card.classList.remove('is-selected'));
    if (element) element.classList.add('is-selected');
}

function showMapError(message) {
    const banner = document.getElementById('mapErrorBanner');
    const text = document.getElementById('mapErrorText');
    if (text) text.innerText = message;
    if (banner) banner.classList.remove('hidden');
    setMapStatus('error', 'Rute Tidak Tersedia');
}

function hideMapError() {
    const banner = document.getElementById('mapErrorBanner');
    if (banner) banner.classList.add('hidden');
}

function clearRouteVisuals() {
    if (animationId) {
        cancelAnimationFrame(animationId);
        animationId = null;
    }
    if (routeLine) routeLine.setLatLngs([]);
    if (routeFlowLine) routeFlowLine.setLatLngs([]);
    if (truck && map && map.hasLayer(truck)) {
        map.removeLayer(truck);
    }
}

async function getRoadRoute(shipmentId) {
    const response = await fetch(`/ai-optimizer/route/${shipmentId}`, {
        headers: { 'Accept': 'application/json' }
    });

    if (!response.ok) {
        let serverMsg = 'Layanan penyedia rute sedang tidak tersedia.';
        try {
            const errJson = await response.json();
            if (errJson?.message) serverMsg = errJson.message;
        } catch (_) {}
        throw new Error(serverMsg);
    }

    const data = await response.json();
    const rawCoords = Array.isArray(data?.geometry) ? data.geometry : [];

    const latlngs = rawCoords
        .filter(c => Array.isArray(c) && c.length >= 2)
        .map(c => [Number(c[1]), Number(c[0])])
        .filter(([lat, lng]) => isValidLatLng(lat, lng));

    if (latlngs.length < 2) {
        throw new Error('Tidak ada geometri rute jalan yang valid dari layanan rute.');
    }

    return latlngs;
}

async function updateMap(element, button = null) {
    if (!element || !map) return;

    activeShipmentCard = element;
    highlightShipmentCard(element);
    updateSelectedInfoPanel(element);
    hideMapError();

    const loadingOverlay = document.getElementById('mapLoadingOverlay');
    if (loadingOverlay) loadingOverlay.classList.remove('hidden');
    if (button) setRouteLoading(button, true);
    setMapStatus('loading', 'Memuat Rute...');

    const oLat = parseFloat(element.getAttribute('data-origin-lat'));
    const oLng = parseFloat(element.getAttribute('data-origin-lng'));
    const dLat = parseFloat(element.getAttribute('data-dest-lat'));
    const dLng = parseFloat(element.getAttribute('data-dest-lng'));
    const originName = element.getAttribute('data-origin') || 'Asal';
    const destName = element.getAttribute('data-destination') || 'Tujuan';
    const shipmentId = element.getAttribute('data-shipment-id');

    const hasValidEndpoints = isValidLatLng(oLat, oLng) && isValidLatLng(dLat, dLng);

    if (hasValidEndpoints) {
        markerOrigin.setLatLng([oLat, oLng]).bindPopup(`<div class="text-xs font-semibold text-slate-800"><span class="text-emerald-600 font-bold">Asal:</span> ${escapeHtml(originName)}</div>`);
        markerDestination.setLatLng([dLat, dLng]).bindPopup(`<div class="text-xs font-semibold text-slate-800"><span class="text-rose-600 font-bold">Tujuan:</span> ${escapeHtml(destName)}</div>`);
        if (!map.hasLayer(markerOrigin)) markerOrigin.addTo(map);
        if (!map.hasLayer(markerDestination)) markerDestination.addTo(map);
    }

    try {
        const newRoute = await getRoadRoute(shipmentId);

        routeLine.setLatLngs(newRoute);
        routeFlowLine.setLatLngs(newRoute);

        const bounds = routeLine.getBounds();
        if (bounds.isValid()) {
            map.fitBounds(bounds, { padding: [55, 55], maxZoom: 15 });
        }

        resetTruck(newRoute);
        setMapStatus('ready', 'Rute Terhubung');

        if (button) {
            document.getElementById('routeMapSection')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    } catch (error) {
        console.error('Map route error:', error);
        clearRouteVisuals();
        if (hasValidEndpoints) {
            map.fitBounds(L.latLngBounds([[oLat, oLng], [dLat, dLng]]), { padding: [70, 70], maxZoom: 13 });
        }
        showMapError(error.message || 'Gagal memuat rute jalan dari layanan penyedia rute.');
    } finally {
        if (loadingOverlay) loadingOverlay.classList.add('hidden');
        if (button) setRouteLoading(button, false);
    }
}

function retryActiveRoute() {
    if (activeShipmentCard) {
        updateMap(activeShipmentCard);
    }
}

function recenterActiveRoute() {
    if (!map) return;
    if (routeLine && routeLine.getLatLngs().length >= 2 && routeLine.getBounds().isValid()) {
        map.fitBounds(routeLine.getBounds(), { padding: [55, 55], maxZoom: 15 });
    } else if (markerOrigin && markerDestination && map.hasLayer(markerOrigin) && map.hasLayer(markerDestination)) {
        map.fitBounds(L.latLngBounds([markerOrigin.getLatLng(), markerDestination.getLatLng()]), { padding: [70, 70], maxZoom: 13 });
    }
}

function resetTruck(route) {
    if (animationId) {
        cancelAnimationFrame(animationId);
        animationId = null;
    }

    if (!route || route.length < 2 || !map) return;

    if (!map.hasLayer(truck)) {
        truck.addTo(map);
    }

    let segment = 0;
    let progress = 0;
    const speed = 0.008;

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

function setRouteLoading(button, loading) {
    const text = button.querySelector('.route-text');
    if (!text) return;

    if (loading) {
        text.innerHTML = `
            <span class="inline-flex items-center gap-1.5">
                <span class="w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                <span>Memuat...</span>
            </span>
        `;
        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-wait');
    } else {
        text.innerHTML = `<i data-lucide="map-pinned" class="w-3.5 h-3.5"></i><span>Lihat Rute</span>`;
        button.disabled = false;
        button.classList.remove('opacity-75', 'cursor-wait');
        renderLucideIcons();
    }
}

/* ========================================================================= */
/* HELPERS FOR PLAIN-LANGUAGE TRANSLATION & SAFE RENDERING                   */
/* ========================================================================= */

function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function mapFeasibilityDisplay(raw) {
    const val = String(raw ?? '').toLowerCase();
    if (val === 'safe') {
        return {
            label: 'Aman Dikirim',
            shortLabel: 'Aman',
            darkText: 'text-emerald-400',
            darkPill: 'bg-emerald-500/15 border-emerald-400/35 text-emerald-300',
            lightBadge: 'bg-emerald-50 text-emerald-700'
        };
    }
    if (val === 'tight') {
        return {
            label: 'Waktu Pengiriman Terbatas',
            shortLabel: 'Waktu Terbatas',
            darkText: 'text-amber-400',
            darkPill: 'bg-amber-500/15 border-amber-400/35 text-amber-300',
            lightBadge: 'bg-amber-50 text-amber-700'
        };
    }
    if (val === 'breach') {
        return {
            label: 'Waktu Pengiriman Melewati Batas Aman',
            shortLabel: 'Melewati Batas',
            darkText: 'text-rose-400',
            darkPill: 'bg-rose-500/15 border-rose-400/35 text-rose-300',
            lightBadge: 'bg-rose-50 text-rose-700'
        };
    }
    return {
        label: raw || 'Belum tersedia',
        shortLabel: raw || 'Tidak diketahui',
        darkText: 'text-slate-300',
        darkPill: 'bg-slate-700/50 border-slate-600 text-slate-200',
        lightBadge: 'bg-slate-100 text-slate-700'
    };
}

function simplifyTechnicalText(text) {
    if (!text) return '';
    return String(text)
        .replace(/immediate operational intervention required; routing alone is insufficient\.?/gi,
            'Pengiriman ini memerlukan perhatian segera. Mengubah rute saja tidak cukup untuk menjaga kesegaran produk.')
        .replace(/escalate and review shipment immediately/gi,
            'Tinjau pengiriman ini segera dan pertimbangkan percepatan pengiriman atau penyesuaian penanganan.')
        .replace(/operational freshness window/gi, 'batas waktu pengiriman aman')
        .replace(/projected arrival quality/gi, 'perkiraan kualitas produk saat tiba')
        .replace(/arrival quality pressure/gi, 'perkiraan kualitas produk saat tiba yang rendah')
        .replace(/remaining operational life/gi, 'perkiraan sisa masa simpan')
        .replace(/operational risk/gi, 'risiko pengiriman')
        .replace(/deterministic risk evaluation/gi, 'penilaian risiko otomatis');
}

function getShipmentCardById(id) {
    return document.querySelector(`.shipment-card[data-shipment-id="${id}"]`);
}

/* ========================================================================= */
/* MODAL 1: PENJELASAN PENGIRIMAN AI                                         */
/* ========================================================================= */

async function showAIExplain(id) {
    const modal = document.getElementById('aiModal');
    const loading = document.getElementById('aiLoading');
    const errorBox = document.getElementById('aiError');
    const result = document.getElementById('aiResult');

    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    errorBox.classList.add('hidden');
    result.classList.add('hidden');

    const card = getShipmentCardById(id);

    try {
        const response = await fetch(`/ai-optimizer/explain/${id}`, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error('Tidak dapat mengambil penjelasan AI dari server.');
        }

        const data = await response.json();

        const commodity = card?.getAttribute('data-commodity') || '';
        const origin = card?.getAttribute('data-origin') || '';
        const destination = card?.getAttribute('data-destination') || '';
        const riskScoreRaw = card?.getAttribute('data-risk-score');
        const riskSeverityRaw = (card?.getAttribute('data-risk-severity') || '').toLowerCase();
        const qualityRaw = card?.getAttribute('data-quality');
        const marginRaw = card?.getAttribute('data-transit-margin');

        const riskScore = riskScoreRaw !== null && riskScoreRaw !== '' ? Number(riskScoreRaw) : null;
        const qualityVal = qualityRaw !== null && qualityRaw !== '' ? Number(qualityRaw) : null;
        const marginVal = marginRaw !== null && marginRaw !== '' ? Number(marginRaw) : null;

        let riskLabel = 'Penilaian Risiko';
        let statusStyle = 'bg-slate-800/60 border-slate-700 text-slate-200';
        let badgeColor = 'text-slate-300';
        let statusSummary = 'Tinjau detail penilaian di bawah ini untuk memahami kondisi pengiriman saat ini.';

        if (riskSeverityRaw.includes('critical') || (riskScore !== null && riskScore >= 85)) {
            riskLabel = 'Risiko Kritis';
            statusStyle = 'bg-rose-500/10 border-rose-500/35';
            badgeColor = 'text-rose-400';
            statusSummary = 'Pengiriman ini memerlukan perhatian segera karena kualitas produk berisiko menurun parah sebelum tiba.';
        } else if (riskSeverityRaw.includes('high') || (riskScore !== null && riskScore >= 65)) {
            riskLabel = 'Risiko Tinggi';
            statusStyle = 'bg-rose-500/10 border-rose-500/30';
            badgeColor = 'text-rose-400';
            statusSummary = 'Pengiriman ini memiliki risiko tinggi kehilangan kesegaran selama perjalanan dan perlu segera ditinjau.';
        } else if (riskSeverityRaw.includes('moderate') || riskSeverityRaw.includes('medium') || (riskScore !== null && riskScore >= 35)) {
            riskLabel = 'Risiko Sedang';
            statusStyle = 'bg-amber-500/10 border-amber-500/30';
            badgeColor = 'text-amber-400';
            statusSummary = 'Kondisi pengiriman masih terkendali, namun penundaan atau kendala penanganan dapat memengaruhi kesegaran produk.';
        } else if (riskSeverityRaw.includes('low') || (riskScore !== null && riskScore < 35)) {
            riskLabel = 'Risiko Rendah';
            statusStyle = 'bg-emerald-500/10 border-emerald-500/30';
            badgeColor = 'text-emerald-400';
            statusSummary = 'Kondisi pengiriman saat ini mendukung untuk menjaga kesegaran produk hingga tiba di tujuan.';
        }

        const banner = document.getElementById('aiStatusBanner');
        banner.className = `rounded-2xl border p-4 sm:p-5 ${statusStyle}`;
        const badgeEl = document.getElementById('aiStatusBadge');
        badgeEl.innerText = riskLabel;
        badgeEl.className = `text-xs font-bold uppercase tracking-wider ${badgeColor}`;
        document.getElementById('aiStatusIcon').className = `w-4 h-4 ${badgeColor}`;
        document.getElementById('aiStatusDescription').innerText = statusSummary;
        document.getElementById('aiShipmentMeta').innerText = commodity ? `${commodity} • ${origin} → ${destination}` : '';

        const rawRec = data.recommendation ?? '';
        const cleanRec = rawRec ? simplifyTechnicalText(rawRec) : 'Tidak ada tindakan khusus yang dikembalikan oleh sistem.';
        document.getElementById('aiRecommendation').innerText = cleanRec;

        const recSub = document.getElementById('aiRecommendationSub');
        if (rawRec && cleanRec.toLowerCase() !== rawRec.toLowerCase()) {
            recSub.innerText = `Panduan sistem: ${rawRec}`;
            recSub.classList.remove('hidden');
        } else {
            recSub.classList.add('hidden');
        }

        const factorsGrid = document.getElementById('aiFactorsGrid');
        const factorsSection = document.getElementById('aiFactorsSection');
        factorsGrid.innerHTML = '';

        const factors = [];

        if (qualityVal !== null && Number.isFinite(qualityVal)) {
            const qDesc = qualityVal < 40
                ? 'Perkiraan kualitas produk saat tiba sangat rendah.'
                : (qualityVal < 75 ? 'Kesegaran produk berpotensi menurun sebelum tiba.' : 'Produk diperkirakan tiba dalam kondisi baik.');
            const qAccent = qualityVal < 40 ? 'text-rose-400' : (qualityVal < 75 ? 'text-amber-400' : 'text-emerald-400');
            factors.push({
                icon: 'leaf',
                label: 'Perkiraan Kualitas Produk',
                value: `${qualityVal}/100`,
                accent: qAccent,
                desc: qDesc
            });
        }

        if (riskScore !== null && Number.isFinite(riskScore)) {
            const rDesc = riskScore >= 70
                ? 'Pengiriman ini memiliki risiko perjalanan yang sangat tinggi.'
                : (riskScore >= 40 ? 'Terdeteksi faktor risiko sedang di sepanjang rute ini.' : 'Risiko pengiriman masih berada dalam batas aman.');
            const rAccent = riskScore >= 70 ? 'text-rose-400' : (riskScore >= 40 ? 'text-amber-400' : 'text-emerald-400');
            factors.push({
                icon: 'alert-triangle',
                label: 'Risiko Pengiriman',
                value: `${riskScore}/100`,
                accent: rAccent,
                desc: rDesc
            });
        }

        if (marginVal !== null && Number.isFinite(marginVal)) {
            const mDesc = marginVal <= 0
                ? 'Batas waktu aman pengiriman telah habis sebelum tiba.'
                : (marginVal < 6 ? 'Hanya tersisa sedikit waktu cadangan jika terjadi keterlambatan.' : 'Masih tersedia waktu cadangan yang cukup untuk perjalanan ini.');
            const mAccent = marginVal <= 0 ? 'text-rose-400' : (marginVal < 6 ? 'text-amber-400' : 'text-emerald-400');
            factors.push({
                icon: 'calendar-clock',
                label: 'Sisa Waktu Aman',
                value: `${marginVal.toFixed(1)} jam`,
                accent: mAccent,
                desc: mDesc
            });
        }

        if (factors.length > 0) {
            factors.forEach(f => {
                const el = document.createElement('div');
                el.className = 'rounded-xl bg-slate-800/70 border border-slate-700/80 p-3.5 flex flex-col justify-between';
                el.innerHTML = `
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <span class="text-[11px] font-semibold text-slate-400">${escapeHtml(f.label)}</span>
                            <i data-lucide="${f.icon}" class="w-4 h-4 ${f.accent} shrink-0"></i>
                        </div>
                        <p class="text-xl font-bold ${f.accent}">${escapeHtml(f.value)}</p>
                    </div>
                    <p class="text-[11px] text-slate-300 mt-2 leading-relaxed">${escapeHtml(f.desc)}</p>
                `;
                factorsGrid.appendChild(el);
            });
            factorsSection.classList.remove('hidden');
        } else {
            factorsSection.classList.add('hidden');
        }

        document.getElementById('aiFlowStep1').innerText = qualityVal !== null
            ? `Kualitas ${qualityVal}/100`
            : 'Data pengiriman dianalisis';
        document.getElementById('aiFlowStep2').innerText = riskScore !== null
            ? `Skor risiko ${riskScore}/100`
            : 'Model risiko AI dievaluasi';
        document.getElementById('aiFlowStep3').innerText = riskLabel;
        document.getElementById('aiFlowStep4').innerText = riskScore !== null && riskScore >= 65
            ? 'Perlu tinjauan segera'
            : 'Ikuti panduan di atas';

        const rawReason = (data.decision_reason ?? '').trim();
        const rawConclusion = (data.conclusion ?? '').trim();
        document.getElementById('aiReason').innerText = rawReason || '-';

        let summaryText = '';
        if (rawConclusion && rawReason && rawConclusion.toLowerCase() !== rawReason.toLowerCase()) {
            summaryText = `${simplifyTechnicalText(rawReason)} ${simplifyTechnicalText(rawConclusion)}`;
        } else {
            summaryText = simplifyTechnicalText(rawConclusion || rawReason || 'Tidak ada detail penjelasan tambahan yang tersedia.');
        }

        document.getElementById('aiConclusion').innerText = summaryText;

        loading.classList.add('hidden');
        result.classList.remove('hidden');
        renderLucideIcons();
    } catch (e) {
        console.error('AI Explain error:', e);
        loading.classList.add('hidden');
        document.getElementById('aiErrorMessage').innerText = e.message || 'Gagal memuat penjelasan AI.';
        errorBox.classList.remove('hidden');
        renderLucideIcons();
    }
}

function closeAIExplain() {
    document.getElementById('aiModal')?.classList.add('hidden');
}

/* ========================================================================= */
/* MODAL 2: ANALISIS RUTE KESEGARAN                                          */
/* ========================================================================= */

async function showFreshnessRoutes(id) {
    activeFreshnessShipmentId = id;

    const modal = document.getElementById('freshnessRouteModal');
    const loading = document.getElementById('freshnessRouteLoading');
    const errorBox = document.getElementById('freshnessRouteError');
    const result = document.getElementById('freshnessRouteResult');
    const notice = document.getElementById('freshnessRouteNotice');
    const noticeText = document.getElementById('freshnessRouteNoticeText');
    const container = document.getElementById('freshnessRouteCandidates');

    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    errorBox.classList.add('hidden');
    result.classList.add('hidden');
    notice.classList.add('hidden');
    container.innerHTML = '';

    try {
        const response = await fetch(`/ai-optimizer/freshness-routes/${id}`, {
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error('Optimasi rute berbasis kesegaran gagal');
        }

        const data = await response.json();
        latestFreshnessRouteCandidates = Array.isArray(data.candidates) ? data.candidates : [];

        const best = data.recommended_route ?? data.best_available_route ?? null;
        const isBestAvailableOnly = data.decision_type === 'best_available';
        const feasibilityInfo = mapFeasibilityDisplay(best?.freshness_feasibility);
        const noSafeRoute = isBestAvailableOnly || String(best?.freshness_feasibility ?? '').toLowerCase() === 'breach';

        document.getElementById('freshnessDecisionHeading').innerText = 'Analisis Rute Kesegaran';

        const summaryCard = document.getElementById('freshnessSummaryCard');
        const decisionPill = document.getElementById('freshnessDecisionPill');

        if (noSafeRoute) {
            summaryCard.className = 'rounded-2xl border p-5 sm:p-6 bg-rose-500/10 border-rose-500/30';
            decisionPill.className = 'px-3 py-1 rounded-full text-xs font-bold border bg-rose-500/20 border-rose-400/40 text-rose-200';
            decisionPill.innerText = 'Tidak Ada Rute Aman';
        } else {
            summaryCard.className = 'rounded-2xl border p-5 sm:p-6 bg-emerald-500/10 border-emerald-500/30';
            decisionPill.className = `px-3 py-1 rounded-full text-xs font-bold border ${feasibilityInfo.darkPill}`;
            decisionPill.innerText = feasibilityInfo.label;
        }

        document.getElementById('freshnessRecommendedLabelCaption').innerText = noSafeRoute
            ? 'Opsi Terbaik Tersedia (Belum Aman)'
            : 'Rute Direkomendasikan';

        document.getElementById('freshnessRecommendedLabel').innerText = best?.label ?? '-';
        document.getElementById('freshnessRecommendedScore').innerText = best && best.route_score !== undefined
            ? `${best.route_score}/100`
            : '-';

        const feasEl = document.getElementById('freshnessRecommendedFeasibility');
        feasEl.innerText = feasibilityInfo.label;
        feasEl.className = `text-base sm:text-lg font-bold mt-1 ${feasibilityInfo.darkText}`;

        const rawReason = data.recommendation_reason ?? '';
        document.getElementById('freshnessRouteReason').innerText = rawReason
            ? simplifyTechnicalText(rawReason)
            : (noSafeRoute
                ? 'Rute yang tersedia masih terlalu berisiko untuk menjaga kesegaran produk. Pengiriman berpotensi tiba melewati batas waktu aman.'
                : 'Rute ini memberikan keseimbangan terbaik antara kesegaran produk, risiko pengiriman rendah, dan waktu tempuh.');

        const actionWrap = document.getElementById('freshnessRequiredActionWrap');
        const actionText = document.getElementById('freshnessRequiredAction');
        if (data.required_action) {
            actionText.innerText = simplifyTechnicalText(data.required_action);
            actionWrap.classList.remove('hidden');
        } else {
            actionWrap.classList.add('hidden');
        }

        if (data.alternative_note) {
            noticeText.innerText = data.alternative_note;
            notice.classList.remove('hidden');
        }

        document.getElementById('freshnessCandidateCount').innerText =
            `${latestFreshnessRouteCandidates.length} pilihan dievaluasi`;

        latestFreshnessRouteCandidates.forEach((candidate, index) => {
            const recommended = candidate.recommended === true;
            const bestAvailable = candidate.best_available === true;
            const cFeas = mapFeasibilityDisplay(candidate.freshness_feasibility);
            const routeScore = Number(candidate.route_score ?? 0);
            const arrivalQuality = Number(candidate.projected_arrival_quality ?? 0);
            const riskScore = Number(candidate.projected_risk_score ?? 0);

            const cardBorder = recommended
                ? 'bg-emerald-500/10 border-emerald-400/35'
                : (bestAvailable
                    ? 'bg-rose-500/10 border-rose-400/35'
                    : 'bg-slate-800/60 border-slate-700');

            const badgeHtml = recommended
                ? `<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">Direkomendasikan</span>`
                : (bestAvailable
                    ? `<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-500/20 text-rose-300 border border-rose-400/30">Terbaik Tersedia (Masih Berisiko)</span>`
                    : `<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-700 text-slate-300">Peringkat #${escapeHtml(candidate.rank ?? (index + 1))}</span>`);

            const hasGeometry = Array.isArray(candidate.geometry) && candidate.geometry.length > 1;

            const card = document.createElement('div');
            card.className = `rounded-2xl border p-5 flex flex-col justify-between ${cardBorder}`;
            card.innerHTML = `
                <div>
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <div class="mb-1.5">${badgeHtml}</div>
                            <h4 class="text-base font-bold text-white">${escapeHtml(candidate.label ?? 'Rute')}</h4>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-[10px] font-semibold uppercase text-slate-400">Skor Rute</p>
                            <p class="text-2xl font-bold text-white leading-none mt-1">
                                ${escapeHtml(candidate.route_score ?? '-')}<span class="text-xs text-slate-400 font-medium">/100</span>
                            </p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="h-1.5 w-full rounded-full bg-slate-900 overflow-hidden">
                            <div class="h-full rounded-full ${routeScore >= 70 ? 'bg-emerald-400' : (routeScore >= 40 ? 'bg-amber-400' : 'bg-rose-400')}"
                                 style="width: ${Math.max(0, Math.min(100, routeScore))}%"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 text-xs">
                        <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                            <div class="flex items-center gap-1.5 text-slate-400 mb-1">
                                <i data-lucide="clock" class="w-3.5 h-3.5 text-indigo-400"></i>
                                <span class="text-[11px]">Est. Waktu Tempuh</span>
                            </div>
                            <p class="font-bold text-white">${escapeHtml(candidate.duration_hours ?? '-')} jam</p>
                        </div>

                        <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                            <div class="flex items-center gap-1.5 text-slate-400 mb-1">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-sky-400"></i>
                                <span class="text-[11px]">Jarak Tempuh</span>
                            </div>
                            <p class="font-bold text-white">${escapeHtml(candidate.distance_km ?? '-')} km</p>
                        </div>

                        <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                            <div class="flex items-center gap-1.5 text-slate-400 mb-1">
                                <i data-lucide="leaf" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span class="text-[11px]">Perkiraan Kualitas</span>
                            </div>
                            <p class="font-bold ${arrivalQuality >= 70 ? 'text-emerald-400' : (arrivalQuality >= 40 ? 'text-amber-400' : 'text-rose-400')}">
                                ${escapeHtml(candidate.projected_arrival_quality ?? '-')}/100
                            </p>
                        </div>

                        <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                            <div class="flex items-center gap-1.5 text-slate-400 mb-1">
                                <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-rose-400"></i>
                                <span class="text-[11px]">Risiko Pengiriman</span>
                            </div>
                            <p class="font-bold ${riskScore >= 70 ? 'text-rose-400' : (riskScore >= 40 ? 'text-amber-400' : 'text-emerald-400')}">
                                ${escapeHtml(candidate.projected_risk_score ?? '-')}/100
                            </p>
                        </div>

                        <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                            <div class="flex items-center gap-1.5 text-slate-400 mb-1">
                                <i data-lucide="timer" class="w-3.5 h-3.5 text-teal-400"></i>
                                <span class="text-[11px]">Sisa Waktu Aman</span>
                            </div>
                            <p class="font-bold text-white">
                                ${candidate.transit_margin_hours === null || candidate.transit_margin_hours === undefined
                                    ? 'Belum tersedia'
                                    : Number(candidate.transit_margin_hours).toFixed(1) + ' jam'}
                            </p>
                        </div>

                        <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3">
                            <div class="flex items-center gap-1.5 text-slate-400 mb-1">
                                <i data-lucide="cloud" class="w-3.5 h-3.5 text-slate-400"></i>
                                <span class="text-[11px]">Est. Emisi Karbon</span>
                            </div>
                            <p class="font-bold text-white">${escapeHtml(candidate.carbon_kg ?? '-')} kg CO₂e</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3.5 border-t border-slate-700/70 flex items-center justify-between gap-3">
                    <span class="text-xs font-semibold ${cFeas.darkText}">
                        ${escapeHtml(cFeas.label)}
                    </span>

                    ${hasGeometry
                        ? `<button type="button"
                                   onclick="previewFreshnessRoute(${index})"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-500/20 hover:bg-indigo-500/35 border border-indigo-400/30 text-indigo-200 text-xs font-semibold transition-colors">
                               <i data-lucide="map" class="w-3.5 h-3.5"></i>
                               <span>Pratinjau di Peta</span>
                           </button>`
                        : `<span class="text-[11px] text-slate-500">Tanpa geometri</span>`
                    }
                </div>
            `;

            container.appendChild(card);
        });

        loading.classList.add('hidden');
        result.classList.remove('hidden');
        renderLucideIcons();
    } catch (error) {
        console.error('Freshness routes error:', error);
        loading.classList.add('hidden');
        errorBox.classList.remove('hidden');
        renderLucideIcons();
    }
}

function previewFreshnessRoute(index) {
    const candidate = latestFreshnessRouteCandidates[index];
    if (!candidate || !Array.isArray(candidate.geometry) || candidate.geometry.length < 2 || !map) {
        return;
    }

    const latlngs = candidate.geometry
        .filter(c => Array.isArray(c) && c.length >= 2)
        .map(c => [Number(c[1]), Number(c[0])])
        .filter(([lat, lng]) => isValidLatLng(lat, lng));

    if (latlngs.length < 2) return;

    const card = activeFreshnessShipmentId ? getShipmentCardById(activeFreshnessShipmentId) : null;
    if (card) {
        activeShipmentCard = card;
        highlightShipmentCard(card);
        updateSelectedInfoPanel(card);
    }

    hideMapError();
    markerOrigin.setLatLng(latlngs[0]);
    markerDestination.setLatLng(latlngs[latlngs.length - 1]);
    if (!map.hasLayer(markerOrigin)) markerOrigin.addTo(map);
    if (!map.hasLayer(markerDestination)) markerDestination.addTo(map);

    routeLine.setLatLngs(latlngs);
    routeFlowLine.setLatLngs(latlngs);

    if (routeLine.getBounds().isValid()) {
        map.fitBounds(routeLine.getBounds(), { padding: [55, 55], maxZoom: 15 });
    }

    resetTruck(latlngs);
    setMapStatus('ready', `Pratinjau: ${candidate.label ?? 'Rute'}`);
    closeFreshnessRoutes();
    document.getElementById('routeMapSection')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeFreshnessRoutes() {
    document.getElementById('freshnessRouteModal')?.classList.add('hidden');
}

/* ========================================================================= */
/* INITIALIZATION & EVENT LISTENERS                                          */
/* ========================================================================= */

document.addEventListener('DOMContentLoaded', () => {
    renderLucideIcons();
    initLeafletMap();

    const firstCard = document.querySelector('.shipment-card');
    if (firstCard) {
        updateMap(firstCard);
    } else {
        setMapStatus('idle', 'Tidak Ada Pengiriman Aktif');
    }

    document.getElementById('aiModal')?.addEventListener('click', function (e) {
        if (e.target === this) closeAIExplain();
    });

    document.getElementById('freshnessRouteModal')?.addEventListener('click', function (e) {
        if (e.target === this) closeFreshnessRoutes();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeAIExplain();
            closeFreshnessRoutes();
        }
    });
});
</script>